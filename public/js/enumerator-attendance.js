(() => {
    const config = window.KFHIEnumeratorAttendanceConfig;
    const status = document.getElementById('enumeratorAttendanceStatus');
    const syncButton = document.getElementById('syncEnumeratorAttendance');
    const databaseName = 'kfhi-enumerator-attendance';
    const storeName = 'records';
    let database;
    const databaseReady = openDatabase();

    function setMessage(text, type = '') {
        if (!status) return;
        status.textContent = text;
        status.style.color = type === 'error' ? '#b42318' : type === 'success' ? '#237341' : '';
    }

    function makeUuid() {
        if (crypto.randomUUID) return crypto.randomUUID();
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;
        const hex = [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join('');
        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    }

    function localDate() {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: config.timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).formatToParts(new Date());
        const values = Object.fromEntries(parts.map(part => [part.type, part.value]));
        return `${values.year}-${values.month}-${values.day}`;
    }

    function deviceId() {
        const key = 'kfhi_device_id';
        let id = localStorage.getItem(key);
        if (!id) {
            id = makeUuid();
            localStorage.setItem(key, id);
        }
        return id;
    }

    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(databaseName, 1);
            request.onupgradeneeded = () => {
                const db = request.result;
                if (!db.objectStoreNames.contains(storeName)) {
                    const store = db.createObjectStore(storeName, { keyPath: 'event_uuid' });
                    store.createIndex('event_child', ['event_id', 'child_code'], { unique: true });
                    store.createIndex('event_status', ['event_id', 'sync_status']);
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error || new Error('Could not open phone storage.'));
        });
    }

    function recordsForEvent() {
        return new Promise((resolve, reject) => {
            const tx = database.transaction(storeName, 'readonly');
            const request = tx.objectStore(storeName).getAll();
            request.onsuccess = () => resolve((request.result || [])
                .filter(item => Number(item.event_id) === Number(config.eventId))
                .sort((a, b) => String(b.attended_at).localeCompare(String(a.attended_at))));
            tx.onerror = () => reject(tx.error);
        });
    }

    async function updateMessageForEvent() {
        const records = await recordsForEvent();
        const pending = records.filter(item => item.sync_status === 'pending').length;
        const latest = records[0];
        if (pending) {
            setMessage(`${pending} attendance record(s) saved on this phone and waiting to sync.`);
        } else if (latest?.sync_status === 'rejected' || latest?.sync_status === 'conflict') {
            setMessage(latest.sync_message || 'Attendance needs review.', 'error');
        } else if (latest) {
            setMessage(`Attendance for ${latest.child_code}: ${latest.sync_message || 'synced to the event.'}`, 'success');
        }
    }

    function saveLocal(child) {
        const childCode = String(child?.child_code || '').trim();
        if (!childCode) return Promise.reject(new Error('The scanned record has no child code.'));

        return new Promise((resolve, reject) => {
            const tx = database.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const find = store.index('event_child').get([Number(config.eventId), childCode]);
            let result;

            find.onsuccess = () => {
                if (find.result) {
                    result = { created: false, record: find.result };
                    return;
                }

                const record = {
                    event_uuid: makeUuid(),
                    questionnaire_id: Number(config.questionnaireId),
                    event_id: Number(config.eventId),
                    child_code: childCode,
                    attendance_date: localDate(),
                    attended_at: new Date().toISOString(),
                    device_id: deviceId(),
                    sync_status: 'pending',
                    sync_message: '',
                };
                store.add(record);
                result = { created: true, record };
            };

            tx.oncomplete = () => resolve(result);
            tx.onerror = () => reject(tx.error || new Error('Could not save attendance on this phone.'));
            tx.onabort = () => reject(tx.error || new Error('Attendance was not saved on this phone.'));
        });
    }

    function saveResult(eventUuid, syncStatus, syncMessage) {
        return new Promise((resolve, reject) => {
            const tx = database.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.get(eventUuid);
            request.onsuccess = () => {
                if (request.result) {
                    store.put({ ...request.result, sync_status: syncStatus, sync_message: syncMessage });
                }
            };
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    }

    async function syncPending() {
        try {
            if (!database) database = await databaseReady;
        } catch (error) {
            setMessage('Phone storage is unavailable. Attendance cannot sync from this device.', 'error');
            return;
        }
        if (!navigator.onLine) {
            setMessage('Offline. Attendance stays saved on this phone until the connection returns.');
            return;
        }

        let pending;
        try {
            pending = (await recordsForEvent()).filter(item => item.sync_status === 'pending');
        } catch (error) {
            setMessage(error.message || 'Saved attendance could not be read.', 'error');
            return;
        }
        if (!pending.length) {
            await updateMessageForEvent();
            return;
        }

        syncButton.disabled = true;
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrf) throw new Error('Could not verify your session. Reopen the survey while online.');

            for (let offset = 0; offset < pending.length; offset += 50) {
                const batch = pending.slice(offset, offset + 50);
                const response = await fetch(config.syncUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ events: batch }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Attendance sync failed.');

                for (const item of data.results || []) {
                    const record = batch[item.index];
                    if (!record) continue;
                    const state = ['accepted', 'already_recorded'].includes(item.status) ? 'synced' : item.status;
                    await saveResult(record.event_uuid, state, item.message || 'Attendance needs review.');
                }
            }

            await updateMessageForEvent();
        } catch (error) {
            setMessage(`${error.message || 'Sync failed.'} Attendance remains saved on this phone.`, 'error');
        } finally {
            syncButton.disabled = false;
        }
    }

    async function mark(child) {
        try {
            if (!database) database = await databaseReady;
            const saved = await saveLocal(child);
            if (!saved.created && saved.record.sync_status !== 'pending' && saved.record.sync_status !== 'synced') {
                setMessage(saved.record.sync_message || 'This attendance record needs review.', 'error');
                return;
            }

            setMessage(saved.record.sync_status === 'synced'
                ? `Attendance for ${saved.record.child_code} is already synced.`
                : `Present: ${saved.record.child_code}. Saved on this phone; syncing…`, 'success');
            if (navigator.onLine) await syncPending();
            else await updateMessageForEvent();
        } catch (error) {
            setMessage(error.message || 'Could not save attendance on this phone.', 'error');
        }
    }

    syncButton.addEventListener('click', syncPending);
    window.addEventListener('online', syncPending);
    window.addEventListener('offline', () => setMessage('Offline. Attendance stays saved on this phone until the connection returns.'));

    databaseReady.then(async db => {
        database = db;
        await updateMessageForEvent();
        if (navigator.onLine) syncPending();
    }).catch(error => setMessage(error.message || 'Phone storage is unavailable. Attendance cannot be saved offline.', 'error'));

    window.KFHIEnumeratorAttendance = { mark, syncPending };
})();
