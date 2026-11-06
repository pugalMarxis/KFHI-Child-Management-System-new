<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#245b3b">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    <title>Attendance - {{ $event->title }}</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef7f1;
            color: #183b2a;
            min-height: 100vh;
        }

        .container {
            width: min(1100px, 94%);
            margin: 35px auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .back-link {
            text-decoration: none;
            color: #2e7d32;
            font-weight: 600;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            margin-bottom: 25px;
        }

        .header h1 {
            color: #205c35;
            margin-bottom: 7px;
        }

        .header p {
            color: #6b8174;
        }

        .scanner-card {
            background: white;
            border-radius: 18px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            margin-bottom: 25px;
        }

        .scanner-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .scanner-card h2 {
            color: #205c35;
            margin-bottom: 8px;
        }

        .scanner-card p {
            color: #6b8174;
            margin-bottom: 25px;
        }

        .token-form {
            max-width: 600px;
            margin: auto;
        }

        .token-form input {
            width: 100%;
            padding: 14px;
            border: 1px solid #d4e5d9;
            border-radius: 10px;
            font-size: 15px;
            margin-bottom: 12px;
            outline: none;
        }

        .token-form input:focus {
            border-color: #4caf50;
        }

        .scan-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #2e7d32;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .scan-btn:hover {
            background: #256b29;
        }

        .result {
            max-width: 600px;
            margin: 20px auto 0;
            padding: 18px;
            border-radius: 12px;
            display: none;
        }

        .result.success {
            display: block;
            background: #e7f6ea;
            border: 1px solid #b8dfbf;
        }

        .result.error {
            display: block;
            background: #fff0f0;
            border: 1px solid #efc1c1;
        }

        .child-name {
            font-size: 20px;
            font-weight: 700;
            color: #205c35;
            margin-bottom: 5px;
        }

        .child-code {
            color: #607568;
        }

        .attendance-section {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .attendance-section h2 {
            color: #205c35;
            margin-bottom: 18px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #e5eee8;
        }

        th {
            background: #f1f8f3;
            color: #42634d;
            font-size: 13px;
        }

        td {
            color: #4f6658;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #e4f4e7;
            color: #287332;
            font-size: 12px;
            font-weight: 600;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #74877a;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px auto;
            }

            .scanner-card,
            .header,
            .attendance-section {
                padding: 20px;
            }
        }
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="container">

    <div class="top-bar">
        <a href="{{ route('events.show', $event) }}" class="back-link">
            ← Back to Event
        </a>
    </div>

    <div class="header">
        <h1>📁 Attendance</h1>

        <p>
            {{ $event->title }}
            • {{ $event->event_code }}
        </p>
    </div>


    <div class="scanner-card">

        <div class="scanner-icon">
            📷
        </div>

        <h2>QR Attendance Scanner</h2>

        <p>
            Scan a child's secure QR code to mark attendance.
        </p>
        <a id="offlineAttendanceLink" class="scan-btn"
           href="{{ asset('offline-attendance.html') }}?event_id={{ $event->id }}&amp;event_title={{ urlencode($event->title) }}&amp;timezone={{ urlencode(config('attendance.timezone', 'Asia/Colombo')) }}">
            Open offline attendance page
        </a>
        <div id="reader" style="max-width: 500px; margin: 20px auto;"></div>

        <form id="scanForm" class="token-form">

            @csrf

            <input
                type="text"
                id="token"
                name="token"
                placeholder="Enter a child code or paste a full QR link"
                autocomplete="off"
                required
            >

            <button type="submit" class="scan-btn">
                Mark Attendance
            </button>

        </form>

        <div id="result" class="result"></div>

        <button type="button" id="syncPendingButton" class="scan-btn">
            Sync saved attendance
        </button>

    </div>


    <div class="attendance-section">

        <h2>
            Attendance List
            <span style="font-size:14px;color:#718579;">
                ({{ $attendances->count() }})
            </span>
        </h2>

        @if($attendances->count())

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Child Code</th>
                            <th>Child Name</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($attendances as $attendance)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    {{ $attendance->child->child_code }}
                                </td>

                                <td>
                                    {{ trim(
                                        $attendance->child->first_name . ' ' .
                                        $attendance->child->middle_name . ' ' .
                                        $attendance->child->last_name
                                    ) }}
                                </td>

                                <td>
                                    {{ $attendance->attendance_time?->format('h:i A') }}
                                </td>

                                <td>
                                    <span class="status">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty">
                No attendance has been recorded for this event yet.
            </div>

        @endif

    </div>

</div>


<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
<script src="{{ asset('js/offline-child-cache.js') }}"></script>
<script src="{{ asset('js/offline-attendance.js') }}"></script>


<script>
    const form = document.getElementById('scanForm');
    const result = document.getElementById('result');
    const tokenInput = document.getElementById('token');
    const eventId = @json($event->id);
    const syncUrl = @json(route('attendance.sync'));
    const csrfToken = document.querySelector('input[name="_token"]').value;
    const attendanceTimezone = @json(config('attendance.timezone', 'Asia/Colombo'));
    let scanBusy = false;

    function showMessage(type, message) {
        result.className = `result ${type}`;
        result.textContent = message;
    }

    function attendanceDate() {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: attendanceTimezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).formatToParts(new Date());
        const values = Object.fromEntries(parts.map(part => [part.type, part.value]));
        return `${values.year}-${values.month}-${values.day}`;
    }

    function deviceId() {
        let id = localStorage.getItem('kfhi_device_id');
        if (!id) {
            id = crypto.randomUUID();
            localStorage.setItem('kfhi_device_id', id);
        }
        return id;
    }

    async function syncPendingAttendance() {
        if (!navigator.onLine) {
            showMessage('success', 'Saved on this phone. Waiting for internet to sync.');
            return;
        }

        try {
            const pending = await window.KFHIOfflineAttendance.getPending();
            if (!pending.length) {
                showMessage('success', 'There is no attendance waiting to sync.');
                return;
            }

            const response = await fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ events: pending }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Sync failed.');

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

            showMessage('success', 'Sync finished. Any rejected items remain saved for review.');
        } catch (error) {
            showMessage('error', 'Sync failed. Attendance is still saved on this phone.');
        }
    }

    async function processLegacyToken(token) {
        if (!navigator.onLine) {
            showMessage('error', 'This older QR code needs internet.');
            return;
        }

        try {
            const response = await fetch(
                @json(route('events.attendance.scan', $event)),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ token }),
                }
            );
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Could not record attendance.');
            }
            showMessage('success', `${data.child.name}: ${data.message}`);
            tokenInput.value = '';
        } catch (error) {
            showMessage('error', error.message || 'Could not record attendance.');
        }
    }

    async function processScannedValue(value) {
        const scanned = String(value || '').trim();
        if (!scanned) return;

        try {
            const url = new URL(scanned, window.location.origin);
            const legacy = url.pathname.match(/\/qr\/scan\/([^/]+)$/);
            if (legacy) {
                await processLegacyToken(decodeURIComponent(legacy[1]));
                return;
            }
            if (/^https?:\/\//i.test(scanned)) {
                showMessage('error', 'This QR link is not recognized.');
                return;
            }
        } catch (error) {
            // A child code is plain text, not a URL.
        }

        let childCode = scanned;
        if (scanned.startsWith('{')) {
            try {
                childCode = JSON.parse(scanned).code || '';
            } catch (error) {
                childCode = '';
            }
        }
        childCode = String(childCode).trim();
        if (!childCode) {
            showMessage('error', 'This QR code has no child code.');
            return;
        }

        let child = null;
        try {
            child = await window.KFHIChildOfflineCache.findByCode(childCode);
        } catch (error) {
            if (!navigator.onLine) {
                showMessage('error', 'Saved child records are unavailable on this phone.');
                return;
            }
        }
        if (!navigator.onLine && !child) {
            showMessage('error', 'This child was not downloaded to this phone.');
            return;
        }

        try {
            const saved = await window.KFHIOfflineAttendance.save(
                eventId,
                childCode,
                attendanceDate(),
                deviceId()
            );
            if (!saved.saved && saved.record.sync_status !== 'pending') {
                showMessage('error', 'Attendance for this child and event is already saved here.');
                return;
            }

            tokenInput.value = '';
            showMessage('success', 'Attendance saved on this phone.');
            if (navigator.onLine) await syncPendingAttendance();
        } catch (error) {
            showMessage('error', 'Could not save attendance. Check phone storage.');
        }
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        processScannedValue(tokenInput.value);
    });

    document.getElementById('syncPendingButton')
        .addEventListener('click', syncPendingAttendance);
    window.addEventListener('online', syncPendingAttendance);

    function onScanSuccess(decodedText) {
        if (scanBusy) return;
        scanBusy = true;
        processScannedValue(decodedText).finally(() => {
            setTimeout(() => { scanBusy = false; }, 1200);
        });
    }

    function onScanFailure() {
        // Ignore normal camera frames that do not contain a QR code.
    }

    const html5QrCode = new Html5Qrcode('reader');
    Html5Qrcode.getCameras()
        .then(cameras => {
            if (!cameras.length) {
                showMessage('error', 'No camera was found on this device.');
                return;
            }
            html5QrCode.start(
                cameras[0].id,
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess,
                onScanFailure
            );
        })
        .catch(() => showMessage('error', 'Camera access failed. Check browser permission.'));

    if (navigator.onLine) syncPendingAttendance();

    if ('serviceWorker' in navigator) {
        const workerUrl = @json(rtrim(request()->getBaseUrl(), '/') . '/service-worker.js');
        const workerScope = @json(rtrim(request()->getBaseUrl(), '/') . '/');
        navigator.serviceWorker.register(workerUrl, { scope: workerScope }).catch(() => {});
    }

    document.getElementById('offlineAttendanceLink').addEventListener('click', () => {
        try {
            localStorage.setItem('kfhi_offline_event_context', JSON.stringify({
                event_id: eventId,
                event_title: @json($event->title),
                timezone: attendanceTimezone,
            }));
        } catch (error) {
            // The link still includes event details if local storage is unavailable.
        }
    });

</script>

</body>
</html>
