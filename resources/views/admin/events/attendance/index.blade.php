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
            Use the same scanner online or offline. Scans are saved on this phone and sync when internet returns.
        </p>
        <a id="offlineAttendanceLink" class="scan-btn"
           href="{{ asset('offline-attendance.html') }}?event_id={{ $event->id }}&amp;event_title={{ urlencode($event->title) }}&amp;timezone={{ urlencode(config('attendance.timezone', 'Asia/Colombo')) }}">
            Open attendance scanner
        </a>

    </div>


    <div class="attendance-section">

        <h2>
            Attendance List
            <span style="font-size:14px;color:#718579;">
                ({{ $attendances->count() }})
            </span>
        </h2>

        <a href="{{ route('events.attendance.export', $event) }}"
           style="display:inline-block; margin:0 0 14px; padding:10px 16px; border-radius:8px; background:#287344; color:#fff; text-decoration:none; font-weight:700;">
            Export Attendance to Excel
        </a>

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
                            <th>Marked by</th>
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
                                    {{ trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
                                        $attendance->child->first_name,
                                        $attendance->child->middle_name,
                                        $attendance->child->last_name,
                                    ])))) }}
                                </td>

                                <td>
                                    {{ $attendance->attendance_time?->format('h:i A') }}
                                </td>

                                <td>
                                    <span class="status">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $attendance->markedByEnumerator?->name ?? $attendance->markedBy?->name ?? '—' }}
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


<script>
    if ('serviceWorker' in navigator) {
        const workerUrl = @json(rtrim(request()->getBaseUrl(), '/') . '/service-worker.js');
        const workerScope = @json(rtrim(request()->getBaseUrl(), '/') . '/');
        navigator.serviceWorker.register(workerUrl, { scope: workerScope }).catch(() => {});
    }

</script>

</body>
</html>
