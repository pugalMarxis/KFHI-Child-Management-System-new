(() => {
    const params = new URLSearchParams(window.location.search);
    let savedContext = {};
    try {
        savedContext = JSON.parse(localStorage.getItem('kfhi_offline_event_context') || '{}');
    } catch (error) {
        savedContext = {};
    }
    const eventId = Number(params.get('event_id') || savedContext.event_id || 0);
    const eventTitle = params.get('event_title') || savedContext.event_title || 'Selected event';
    const timezone = params.get('timezone') || savedContext.timezone || 'Asia/Colombo';
    const title = document.getElementById('eventTitle');
    const message = document.getElementById('message');
    const connectionStatus = document.getElementById('connectionStatus');
    const pendingCount = document.getElementById('pendingCount');
    const markedList = document.getElementById('markedList');
    const input = document.getElementById('childCode');
    const form = document.getElementById('attendanceForm');
    let scanner;
    let scanBusy = false;

    title.textContent = eventId > 0
        ? `${eventTitle} (event ${eventId})`
        : 'No event was selected. Open this page from an event attendance page while online.';

    if (eventId > 0) {
        try {
            localStorage.setItem('kfhi_offline_event_context', JSON.stringify({
                event_id: eventId,
                event_title: eventTitle,
                timezone,
            }));
        } catch (error) {
            show('error', 'Could not save the event on this phone. Check phone storage.');
        }
    }

    function show(type, text) {
        message.className = `message ${type || ''}`;
        message.textContent = text;
    }

    function updateConnection() {
        connectionStatus.textContent = navigator.onLine
            ? 'Internet connection appears available.'
            : 'Offline. New attendance will stay on this phone.';
    }

    function getLocalDate() {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).formatToParts(new Date());
        const values = Object.fromEntries(parts.map(part => [part.type, part.value]));
        return `${values.year}-${values.month}-${values.day}`;
    }

    function getDeviceId() {
        let value = localStorage.getItem('kfhi_device_id');
        if (!value) {
            value = crypto.randomUUID
                ? crypto.randomUUID()
                : `device-${Date.now()}-${Math.random().toString(16).slice(2)}`;
            localStorage.setItem('kfhi_device_id', value);
        }
        return value;
    }

    function legacyTokenFrom(value) {
        try {
            const url = new URL(String(value || '').trim(), window.location.origin);
            const match = url.pathname.match(/\/qr\/scan\/([^/]+)$/);
            return match ? decodeURIComponent(match[1]) : null;
        } catch (error) {
            return null;
        }
    }

    async function recordLegacyQrOnline(token) {
        const tokenResponse = await fetch(new URL('attendance/sync/token', window.location.href), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
        if (!tokenResponse.ok) {
            show('error', 'Sign in again while online to record this QR code.');
            return;
        }
        const tokenData = await tokenResponse.json();
        const response = await fetch(
            new URL(`admin/events/${eventId}/attendance/scan`, window.location.href),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': tokenData.csrf_token,
                },
                body: JSON.stringify({ token }),
            }
        );
        const data = await response.json();
        if (!response.ok || !data.success) {
            show('error', data.message || 'Could not record attendance.');
            return;
        }
        show('success', `${data.child.name}: ${data.message}`);
    }

    async function refreshCount() {
        try {
            const pending = await window.KFHIOfflineAttendance.getPending();
            const records = await window.KFHIOfflineAttendance.getForEvent(eventId);
            pendingCount.textContent = `${pending.filter(item => item.event_id === eventId).length} attendance item(s) waiting to sync.`;
            markedList.replaceChildren();
            if (!records.length) {
                const empty = document.createElement('li');
                empty.textContent = 'No attendance marked for this event on this phone yet.';
                markedList.append(empty);
                return;
            }
            for (const record of records) {
                const row = document.createElement('li');
                const time = new Date(record.attended_at).toLocaleTimeString();
                row.textContent = `${record.child_code} — ${record.attendance_date} ${time} — ${record.sync_status === 'synced' ? 'Synced' : record.sync_status === 'pending' ? 'Waiting to sync' : 'Needs review'}`;
                markedList.append(row);
            }
        } catch (error) {
            pendingCount.textContent = 'Phone storage is not available.';
            markedList.textContent = 'Saved attendance list is unavailable.';
        }
    }

    async function syncPending() {
        updateConnection();
        if (!navigator.onLine) {
            show('success', 'Saved attendance will sync when internet returns.');
            return;
        }

        try {
            const pending = await window.KFHIOfflineAttendance.getPending();
            if (!pending.length) {
                show('success', 'There is no attendance waiting to sync.');
                await refreshCount();
                return;
            }

            const tokenResponse = await fetch(new URL('attendance/sync/token', window.location.href), {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!tokenResponse.ok) {
                throw new Error('Sign in again while online to sync attendance.');
            }
            const tokenData = await tokenResponse.json();

            const response = await fetch(new URL('attendance/sync', window.location.href), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': tokenData.csrf_token,
                },
                body: JSON.stringify({ events: pending }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Attendance sync failed.');

            for (const item of data.results || []) {
                const status = ['accepted', 'already_recorded'].includes(item.status)
                    ? 'synced'
                    : item.status;
                await window.KFHIOfflineAttendance.setStatus(
                    item.event_uuid,
                    status,
                    item.message || ''
                );
            }

            show('success', 'Sync finished. Check any rejected items with your supervisor.');
            await refreshCount();
            const eventRecords = await window.KFHIOfflineAttendance.getForEvent(eventId);
            const allResolved = eventRecords.length > 0 && eventRecords.every(item => item.sync_status === 'synced');
            if (allResolved) {
                show('success', 'Attendance synced. Opening the online event attendance list…');
                const attendanceUrl = new URL(`admin/events/${eventId}/attendance`, new URL('../', window.location.href));
                setTimeout(() => { window.location.href = attendanceUrl.href; }, 1200);
            }
        } catch (error) {
            show('error', error.message || 'Sync failed. Attendance remains saved on this phone.');
            await refreshCount();
        }
    }

    async function saveAttendance(rawValue) {
        if (!eventId) {
            show('error', 'Open this page from a specific event first.');
            return;
        }

        const scanned = String(rawValue || '').trim();
        let child = null;

        try {
            child = await window.KFHIChildOfflineCache.findByScannedValue(scanned);
        } catch (error) {
            if (!navigator.onLine) {
                show('error', 'Saved child records are unavailable on this phone.');
                return;
            }
        }

        if (!child && !navigator.onLine) {
            show('error', 'This child QR was not saved on this phone. Connect and sync child records first.');
            return;
        }

        if (!child) {
            const legacyToken = legacyTokenFrom(scanned);
            if (legacyToken) {
                try {
                    await recordLegacyQrOnline(legacyToken);
                } catch (error) {
                    show('error', 'Could not contact the attendance server.');
                }
                return;
            }
        }

        let childCode = child?.child_code || scanned;
        if (!child && scanned.startsWith('{')) {
            try {
                childCode = JSON.parse(scanned).code || '';
            } catch (error) {
                childCode = '';
            }
        }
        childCode = String(childCode).trim();
        if (!childCode) {
            show('error', 'This QR does not contain a child code.');
            return;
        }

        try {
            const saved = await window.KFHIOfflineAttendance.save(
                eventId,
                childCode,
                getLocalDate(),
                getDeviceId()
            );

            if (!saved.saved && saved.record.sync_status !== 'pending') {
                show('error', 'Attendance for this child and event is already saved on this phone.');
                return;
            }

            show('success', `Attendance saved on this phone for ${childCode}.`);
            input.value = '';
            await refreshCount();
            if (navigator.onLine) await syncPending();
        } catch (error) {
            show('error', 'Could not save attendance. Check phone storage and try again.');
        }
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        saveAttendance(input.value);
    });

    document.getElementById('syncButton').addEventListener('click', syncPending);
    window.addEventListener('online', syncPending);
    window.addEventListener('offline', updateConnection);

    document.getElementById('startCamera').addEventListener('click', async () => {
        try {
            const cameras = await Html5Qrcode.getCameras();
            if (!cameras.length) throw new Error('No camera found.');
            scanner = new Html5Qrcode('reader');
            const camera = cameras.find(item => /back|rear|environment/i.test(item.label || '')) || cameras[0];
            await scanner.start(
                camera.id,
                { fps: 10, qrbox: { width: 250, height: 250 } },
                value => {
                    if (scanBusy) return;
                    scanBusy = true;
                    saveAttendance(value).finally(() => {
                        setTimeout(() => { scanBusy = false; }, 1200);
                    });
                },
                () => {}
            );
            show('success', 'Camera is ready. Scan a child code QR.');
        } catch (error) {
            show('error', `Camera could not start: ${error.message || error}. Allow camera access or enter the child code.`);
        }
    });

    updateConnection();
    refreshCount();
})();
