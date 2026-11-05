<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Questionnaire - {{ $event->title }}</title>

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
            width: min(900px, 92%);
            margin: 0 auto;
            padding: 35px 0 50px;
        }

        .header {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .header h1 {
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .header p {
            color: #68776e;
            font-size: 14px;
        }

        .form-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #31583a;
        }

        input,
        textarea {
            width: 100%;
            border: 1px solid #dce8df;
            border-radius: 10px;
            padding: 13px 14px;
            font-size: 15px;
            font-family: inherit;
            outline: none;
            background: #fbfdfb;
        }

        input:focus,
        textarea:focus {
            border-color: #66a66b;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.08);
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        .required {
            color: #c62828;
        }

        .info-box {
            background: #f0f8f1;
            border-left: 4px solid #2e7d32;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            color: #53655a;
            font-size: 14px;
            line-height: 1.6;
        }

        .error-box {
            background: #fff0f0;
            color: #b71c1c;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .error-box ul {
            padding-left: 20px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
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

        @media (max-width: 600px) {
            .container {
                width: 94%;
                padding-top: 20px;
            }

            .form-card {
                padding: 20px;
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

<div class="container">

    <div class="header">
        <h1>Create Questionnaire</h1>

        <p>
            Event:
            <strong>{{ $event->title }}</strong>

            @if($event->event_code)
                • {{ $event->event_code }}
            @endif
        </p>
    </div>

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

    <div class="form-card">

        <div class="info-box">
            Create the questionnaire first. After creating it, you will be
            able to add questions such as text boxes, radio buttons,
            dropdowns, checkboxes, polls, ratings, dates, numbers, and
            yes/no questions.
        </div>

        <form
            method="POST"
            action="{{ route('events.questionnaires.store', $event) }}"
        >

            @csrf

            <div class="form-group">

                <label for="title">
                    Questionnaire Title
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="Example: Children's Day Feedback"
                    maxlength="255"
                    required
                >

            </div>

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Describe the purpose of this questionnaire..."
                >{{ old('description') }}</textarea>

            </div>

            <div class="actions">

                <a
                    href="{{ route('events.questionnaires.index', $event) }}"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Create Questionnaire
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>