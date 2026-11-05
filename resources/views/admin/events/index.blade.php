<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Event Management | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f8f4;
            color: #24352b;
            min-height: 100vh;
        }

        .page {
            max-width: 1250px;
            margin: auto;
            padding: 35px 25px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .title-section h1 {
            color: #246b45;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .title-section p {
            color: #718078;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            border: none;
            text-decoration: none;
            cursor: pointer;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-primary {
            background: #2e7d4f;
            color: white;
            box-shadow: 0 5px 15px rgba(46, 125, 79, 0.20);
        }

        .btn-primary:hover {
            background: #256b42;
        }

        .alert {
            background: #e3f4e8;
            border: 1px solid #b8dfc5;
            color: #27633e;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 22px;
        }

        .event-card {
            background: white;
            border: 1px solid #dce9df;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(35, 70, 50, 0.07);
            transition: 0.2s ease;
        }

        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(35, 70, 50, 0.11);
        }

        .folder-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .event-code {
            display: inline-block;
            background: #eaf6ee;
            color: #2e7d4f;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .event-card h2 {
            font-size: 19px;
            color: #244c35;
            margin-bottom: 10px;
        }

        .event-info {
            color: #718078;
            font-size: 13px;
            line-height: 1.8;
            margin-bottom: 18px;
        }

        .event-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 9px 13px;
            border-radius: 9px;
            font-size: 12px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-view {
            background: #e8f4ec;
            color: #287047;
        }

        .btn-edit {
            background: #eef3f0;
            color: #4d6256;
        }

        .empty-state {
            background: white;
            border: 1px dashed #c8d9cd;
            border-radius: 18px;
            padding: 60px 25px;
            text-align: center;
        }

        .empty-state .icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty-state h2 {
            color: #365c45;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #78877e;
            margin-bottom: 20px;
        }

        .delete-form {
            display: inline;
        }

        .btn-delete {
            border: none;
            cursor: pointer;
            background: #fcecec;
            color: #b44848;
        }

        @media (max-width: 650px) {
            .page {
                padding: 25px 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-primary {
                width: 100%;
                text-align: center;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }
        }
        .btn-attendance {
    background: #e8f1fb;
    color: #286090;
}

.btn-questionnaire {
    background: #f1eaf8;
    color: #7048a0;
}
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="page">

    <div class="header">

        <div class="title-section">
            <h1>Event Management</h1>
            <p>Create and manage KFHI events, attendance and questionnaires.</p>
        </div>

        <a href="{{ route('events.create') }}" class="btn btn-primary">
            + Create New Event
        </a>

    </div>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    @if($events->count())

        <div class="events-grid">

            @foreach($events as $event)

                <div class="event-card">

                    <div class="folder-icon">
                        📁
                    </div>

                    <span class="event-code">
                        {{ $event->event_code }}
                    </span>

                    <h2>
                        {{ $event->title }}
                    </h2>

                    <div class="event-info">

                        <div>
                            📅
                            {{ $event->event_date?->format('d M Y') }}
                        </div>

                        @if($event->start_time)
                            <div>
                                🕐
                                {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}

                                @if($event->end_time)
                                    -
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                                @endif
                            </div>
                        @endif

                        @if($event->location)
                            <div>
                                📍 {{ $event->location }}
                            </div>
                        @endif

                        <div>
                            Status:
                            {{ ucfirst($event->status) }}
                        </div>

                    </div>

                    <div class="event-actions">

<a
    href="{{ route('events.show', $event) }}"
    class="btn-small btn-view"
>
    Open Event
</a>

<a
    href="{{ route('events.attendance.index', $event) }}"
    class="btn-small btn-attendance"
>
    Attendance
</a>

<a
    href="{{ route('events.questionnaires.index', $event) }}"
    class="btn-small btn-questionnaire"
>
    Questionnaires
</a>

<a
    href="{{ route('events.edit', $event) }}"
    class="btn-small btn-edit"
>
    Edit
</a>

                        <form
                            method="POST"
                            action="{{ route('events.destroy', $event) }}"
                            class="delete-form"
                            onsubmit="return confirm('Are you sure you want to delete this event?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn-small btn-delete"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="empty-state">

            <div class="icon">
                📁
            </div>

            <h2>No Events Created Yet</h2>

            <p>
                Create your first KFHI event to manage attendance
                and questionnaires.
            </p>

            <a
                href="{{ route('events.create') }}"
                class="btn btn-primary"
            >
                + Create First Event
            </a>

        </div>

    @endif

</div>

</body>
</html>