<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Event - {{ $event->title }} | KFHI-CMS</title>

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
            max-width: 900px;
            margin: auto;
            padding: 35px 20px;
        }

        .header {
            margin-bottom: 25px;
        }

        .back {
            display: inline-block;
            margin-bottom: 15px;
            color: #2e7d4f;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .back:hover {
            text-decoration: underline;
        }

        h1 {
            color: #246b45;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #718078;
            font-size: 14px;
        }

        .card {
            background: white;
            border: 1px solid #dce9df;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(35, 70, 50, 0.07);
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            color: #2e7d4f;
            font-size: 18px;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e3eee6;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .full {
            grid-column: 1 / -1;
        }

        .field {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #40564a;
            margin-bottom: 7px;
        }

        .required {
            color: #c44;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d2e0d6;
            border-radius: 10px;
            background: #fbfdfb;
            color: #24352b;
            font-size: 14px;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #65a77d;
            box-shadow: 0 0 0 3px rgba(101, 167, 125, 0.12);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .feature-box {
            background: #f4faf6;
            border: 1px solid #dcebe0;
            border-radius: 14px;
            padding: 18px;
        }

        .feature {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .feature:hover {
            background: #eaf5ed;
        }

        .feature input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: #2e7d4f;
        }

        .feature-content strong {
            display: block;
            color: #315642;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .feature-content span {
            display: block;
            color: #78877e;
            font-size: 12px;
            line-height: 1.5;
        }

        .info-box {
            background: #edf7f0;
            border-left: 4px solid #2e7d4f;
            padding: 14px 16px;
            border-radius: 8px;
            color: #486152;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 15px;
        }

        .error-box {
            background: #fff0f0;
            border: 1px solid #f0caca;
            color: #a43f3f;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
        }

        .error-box ul {
            padding-left: 18px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 10px;
        }

        .btn {
            border: none;
            text-decoration: none;
            cursor: pointer;
            padding: 12px 20px;
            border-radius: 11px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary {
            background: #edf2ef;
            color: #52655a;
        }

        .btn-secondary:hover {
            background: #dfe6e2;
        }

        .btn-primary {
            background: #2e7d4f;
            color: white;
            box-shadow: 0 5px 15px rgba(46, 125, 79, 0.20);
        }

        .btn-primary:hover {
            background: #256b42;
        }

        @media (max-width: 650px) {
            .page {
                padding: 25px 15px;
            }

            .card {
                padding: 20px;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .actions .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="page">

    <div class="header">

        <a href="{{ route('events.show', $event) }}" class="back">
            ← Back to Event Details
        </a>

        <h1>Edit Event</h1>

        <p class="subtitle">
            Update event information, schedule, status, and feature settings.
        </p>

    </div>

    <div class="card">

        @if($errors->any())

            <div class="error-box">
                <strong>Please correct the following:</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

        @endif

        <form
            method="POST"
            action="{{ route('events.update', $event) }}"
        >

            @csrf
            @method('PUT')

            <!-- Event Information -->

            <div class="section">

                <h2 class="section-title">
                    Event Information
                </h2>

                <div class="grid">

                    <div class="field">

                        <label for="event_code">
                            Event Code <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="event_code"
                            name="event_code"
                            value="{{ old('event_code', $event->event_code) }}"
                            placeholder="Example: EVT-2026-001"
                            required
                        >

                    </div>

                    <div class="field">

                        <label for="title">
                            Event Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $event->title) }}"
                            placeholder="Example: Children's Day 2026"
                            required
                        >

                    </div>

                    <div class="field">

                        <label for="event_date">
                            Event Date <span class="required">*</span>
                        </label>

                        <input
                            type="date"
                            id="event_date"
                            name="event_date"
                            value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}"
                            required
                        >

                    </div>

                    <div class="field">

                        <label for="status">
                            Event Status <span class="required">*</span>
                        </label>

                        <select id="status" name="status" required>
                            <option value="planned" {{ old('status', $event->status) === 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="ongoing" {{ old('status', $event->status) === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>

                    </div>

                    <div class="field">

                        <label for="start_time">
                            Start Time
                        </label>

                        <input
                            type="time"
                            id="start_time"
                            name="start_time"
                            value="{{ old('start_time', $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '') }}"
                        >

                    </div>

                    <div class="field">

                        <label for="end_time">
                            End Time
                        </label>

                        <input
                            type="time"
                            id="end_time"
                            name="end_time"
                            value="{{ old('end_time', $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '') }}"
                        >

                    </div>

                    <div class="field full">

                        <label for="location">
                            Location
                        </label>

                        <input
                            type="text"
                            id="location"
                            name="location"
                            value="{{ old('location', $event->location) }}"
                            placeholder="Example: KFHI Badulla"
                        >

                    </div>

                    <div class="field full">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            placeholder="Describe the purpose or details of this event..."
                        >{{ old('description', $event->description) }}</textarea>

                    </div>

                </div>

            </div>


            <!-- Event Features -->

            <div class="section">

                <h2 class="section-title">
                    Event Features
                </h2>

                <div class="feature-box">

                    <label class="feature">

                        <input
                            type="checkbox"
                            name="enable_attendance"
                            value="1"
                            {{ old('enable_attendance', $event->enable_attendance) ? 'checked' : '' }}
                        >

                        <span class="feature-content">

                            <strong>
                                📷 QR Code Attendance
                            </strong>

                            <span>
                                Enable QR code scanning so the Enumerator
                                can scan a child's QR code and mark attendance
                                for this event.
                            </span>

                        </span>

                    </label>


                    <label class="feature">

                        <input
                            type="checkbox"
                            name="enable_questionnaires"
                            value="1"
                            {{ old('enable_questionnaires', $event->enable_questionnaires) ? 'checked' : '' }}
                        >

                        <span class="feature-content">

                            <strong>
                                📝 Questionnaires
                            </strong>

                            <span>
                                Enable questionnaires for this event.
                                Admin will be able to create dynamic forms
                                with different question types.
                            </span>

                        </span>

                    </label>

                </div>

                <div class="info-box">
                    You can enable either feature or both features.
                    The event will appear as a folder where Attendance
                    and Questionnaires can be managed separately.
                </div>

            </div>


            <!-- Actions -->

            <div class="actions">

                <a
                    href="{{ route('events.show', $event) }}"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Event
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>
