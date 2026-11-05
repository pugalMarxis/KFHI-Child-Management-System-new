<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Questionnaires - {{ $event->title }}</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef7f1;
            color: #1f3d2b;
            min-height: 100vh;
        }

        .container {
            width: min(1200px, 92%);
            margin: 0 auto;
            padding: 35px 0 50px;
        }

        .header {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .header h1 {
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .header p {
            color: #66756b;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border: none;
            border-radius: 10px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: #2e7d32;
            color: white;
        }

        .btn-secondary {
            background: #e8f3eb;
            color: #2e7d32;
        }

        .btn-danger {
            background: #fdeaea;
            color: #c62828;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            padding: 13px 16px;
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            transition: 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(46, 125, 50, 0.12);
        }

        .icon {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            background: #e8f5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
            margin-bottom: 18px;
        }

        .card h2 {
            font-size: 20px;
            margin-bottom: 8px;
            color: #245c2a;
        }

        .description {
            color: #68776e;
            font-size: 14px;
            line-height: 1.6;
            min-height: 45px;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid #edf2ee;
            font-size: 13px;
        }

        .status {
            font-weight: 700;
            text-transform: capitalize;
        }

        .status.draft {
            color: #f57c00;
        }

        .status.published {
            color: #2e7d32;
        }

        .status.closed {
            color: #757575;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .empty {
            background: #ffffff;
            border-radius: 18px;
            padding: 55px 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .empty-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .empty h2 {
            margin-bottom: 8px;
            color: #2e7d32;
        }

        .empty p {
            color: #68776e;
            margin-bottom: 20px;
        }

        form {
            display: inline;
        }

        @media (max-width: 600px) {
            .container {
                width: 94%;
                padding-top: 20px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .actions {
                width: 100%;
            }

            .actions .btn {
                flex: 1;
                text-align: center;
            }
        }
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="container">

    <div class="header">

        <div>
            <h1>Questionnaires</h1>

            <p>
                {{ $event->title }}
                @if($event->event_code)
                    • {{ $event->event_code }}
                @endif
            </p>
        </div>

        <div class="actions">

            <a
                href="{{ route('events.show', $event) }}"
                class="btn btn-secondary"
            >
                ← Back to Event
            </a>

            <a
                href="{{ route('events.questionnaires.create', $event) }}"
                class="btn btn-primary"
            >
                + Create Questionnaire
            </a>

        </div>

    </div>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if($questionnaires->count())

        <div class="grid">

            @foreach($questionnaires as $questionnaire)

                <div class="card">

                    <div class="icon">
                        📝
                    </div>

                    <h2>
                        {{ $questionnaire->title }}
                    </h2>

                    <p class="description">
                        {{ $questionnaire->description ?: 'No description provided.' }}
                    </p>

                    <div class="meta">

                        <span>
                            {{ $questionnaire->questions_count }}
                            {{ $questionnaire->questions_count == 1 ? 'Question' : 'Questions' }}
                        </span>

                        <span class="status {{ $questionnaire->status }}">
                            {{ $questionnaire->status }}
                        </span>

                    </div>

                    <div class="card-actions">

                        <a
                            href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}"
                            class="btn btn-primary"
                        >
                            Open
                        </a>

                        <a
                            href="{{ route('events.questionnaires.edit', [$event, $questionnaire]) }}"
                            class="btn btn-secondary"
                        >
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="{{ route('events.questionnaires.destroy', [$event, $questionnaire]) }}"
                            onsubmit="return confirm('Are you sure you want to delete this questionnaire?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-danger"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="empty">

            <div class="empty-icon">
                📝
            </div>

            <h2>No Questionnaires Yet</h2>

            <p>
                Create the first questionnaire for this event.
            </p>

            <a
                href="{{ route('events.questionnaires.create', $event) }}"
                class="btn btn-primary"
            >
                + Create Questionnaire
            </a>

        </div>

    @endif

</div>

</body>
</html>