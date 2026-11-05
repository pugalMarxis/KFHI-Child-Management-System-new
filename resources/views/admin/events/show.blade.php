<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $event->title }} - KFHI-CMS</title>

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
            margin: 40px auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .back-btn {
            text-decoration: none;
            color: #2e7d32;
            font-weight: 600;
        }

        .event-header {
            background: #ffffff;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            margin-bottom: 25px;
        }

        .event-title {
            font-size: 30px;
            margin-bottom: 8px;
            color: #205c35;
        }

        .event-code {
            color: #6b8174;
            margin-bottom: 20px;
        }

        .event-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .info-box {
            background: #f1f8f3;
            border-radius: 12px;
            padding: 15px;
        }

        .info-label {
            font-size: 12px;
            color: #6b8174;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 600;
            color: #234b32;
        }

        .description {
            margin-top: 20px;
            line-height: 1.6;
            color: #52685a;
        }

        .section-title {
            font-size: 22px;
            margin-bottom: 18px;
            color: #205c35;
        }

        .folders {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .folder {
            background: #ffffff;
            border-radius: 18px;
            padding: 28px;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            transition: 0.2s ease;
            border: 1px solid #e1eee5;
        }

        .folder:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(46, 125, 50, 0.14);
        }

        .folder-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .folder h3 {
            font-size: 21px;
            color: #205c35;
            margin-bottom: 8px;
        }

        .folder p {
            color: #687c70;
            line-height: 1.5;
        }

        .empty-message {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            color: #6b8174;
        }

        .actions {
            margin-top: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-edit {
            background: #2e7d32;
            color: white;
        }

        .btn-back {
            background: #e2eee5;
            color: #285b38;
        }

        @media (max-width: 700px) {
            .event-info {
                grid-template-columns: 1fr;
            }

            .folders {
                grid-template-columns: 1fr;
            }

            .event-title {
                font-size: 24px;
            }

            .container {
                margin: 25px auto;
            }
        }
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="container">

    <div class="top-bar">
        <a href="{{ route('events.index') }}" class="back-btn">
            ← Back to Events
        </a>
    </div>

    <div class="event-header">

        <div class="event-title">
            📁 {{ $event->title }}
        </div>

        <div class="event-code">
            Event Code: {{ $event->event_code }}
        </div>

        <div class="event-info">

            <div class="info-box">
                <div class="info-label">EVENT DATE</div>
                <div class="info-value">
                    {{ $event->event_date?->format('d M Y') }}
                </div>
            </div>

            <div class="info-box">
                <div class="info-label">TIME</div>
                <div class="info-value">
                    {{ $event->start_time ?? 'Not specified' }}
                    @if($event->end_time)
                        - {{ $event->end_time }}
                    @endif
                </div>
            </div>

            <div class="info-box">
                <div class="info-label">LOCATION</div>
                <div class="info-value">
                    {{ $event->location ?? 'Not specified' }}
                </div>
            </div>

            <div class="info-box">
                <div class="info-label">STATUS</div>
                <div class="info-value">
                    {{ ucfirst($event->status) }}
                </div>
            </div>

        </div>

        @if($event->description)
            <div class="description">
                {{ $event->description }}
            </div>
        @endif

        <div class="actions">
            <a href="{{ route('events.edit', $event) }}"
               class="btn btn-edit">
                Edit Event
            </a>

            <a href="{{ route('events.index') }}"
               class="btn btn-back">
                Back
            </a>
        </div>

    </div>


    <h2 class="section-title">Event Folders</h2>

    @if($event->enable_attendance || $event->enable_questionnaires)

        <div class="folders">

            @if($event->enable_attendance)

            <a href="{{ route('events.attendance.index', $event) }}" class="folder">

<div class="folder-icon">
    📁
</div>

<h3>Attendance</h3>

<p>
    Scan child QR codes and manage attendance
    records for this event.
</p>

<div style="
    margin-top: 15px;
    color: #2e7d32;
    font-weight: 600;
    font-size: 14px;
">
    Open Attendance →
</div>

</a>
            @endif


            @if($event->enable_questionnaires)

            <a href="{{ route('events.questionnaires.index', $event) }}" class="folder">

                    <div class="folder-icon">
                        📁
                    </div>

                    <h3>Questionnaires</h3>

                    <p>
                        Create and manage questionnaires
                        and collect responses.
                    </p>

                </a>

            @endif

        </div>

    @else

        <div class="empty-message">
            No event features have been enabled for this event.
        </div>

    @endif

</div>

</body>
</html>