<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Question | {{ $questionnaire->title }}</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #eef7f1;
            color: #183b28;
            min-height: 100vh;
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 35px 20px 50px;
        }

        .header {
            margin-bottom: 25px;
        }

        .back-link {
            display: inline-block;
            text-decoration: none;
            color: #287a45;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #66806f;
            font-size: 15px;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(40, 90, 55, 0.10);
            border: 1px solid #dcece1;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
            color: #234a30;
        }

        .required {
            color: #c0392b;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            border: 1px solid #cfe1d4;
            border-radius: 12px;
            padding: 13px 14px;
            font-size: 15px;
            background: #fbfefc;
            color: #183b28;
            outline: none;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #5ca874;
            box-shadow: 0 0 0 3px rgba(92, 168, 116, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .hint {
            margin-top: 7px;
            font-size: 13px;
            color: #71877a;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: #f4faf6;
            border-radius: 12px;
            border: 1px solid #dcece1;
        }

        .checkbox-row input {
            width: 17px;
            height: 17px;
            accent-color: #398653;
        }

        .checkbox-row label {
            margin: 0;
            font-weight: 600;
        }

        .options-box {
            display: none;
            margin-top: 18px;
            padding: 20px;
            background: #f5faf7;
            border: 1px solid #d9eade;
            border-radius: 15px;
        }

        .options-box.active {
            display: block;
        }

        .options-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .options-description {
            color: #71877a;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .option-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .option-row input {
            flex: 1;
        }

        .remove-option {
            border: none;
            background: #f4dede;
            color: #a33a3a;
            width: 42px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
        }

        .remove-option:hover {
            background: #edcccc;
        }

        .add-option {
            border: 1px solid #8abd98;
            background: #edf8f0;
            color: #287a45;
            padding: 9px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .add-option:hover {
            background: #e2f3e6;
        }

        .error-box {
            background: #fff0f0;
            border: 1px solid #edcaca;
            color: #9f3030;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
        }

        .error-box ul {
            margin-left: 20px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-cancel {
            background: #edf2ee;
            color: #506457;
        }

        .btn-primary {
            background: #398653;
            color: white;
        }

        .btn-primary:hover {
            background: #2f7446;
        }

        @media (max-width: 600px) {
            .page {
                padding: 25px 15px 40px;
            }

            .card {
                padding: 20px;
            }

            h1 {
                font-size: 25px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
        .suggestion-box {
    display: none;
    margin-top: 12px;
    padding: 13px 15px;
    background: #edf8f0;
    border: 1px solid #cfe5d5;
    border-radius: 12px;
    color: #356342;
    font-size: 13px;
}

.suggestion-box.active {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.suggestion-text strong {
    color: #287a45;
}

.use-suggestion {
    border: none;
    background: #398653;
    color: white;
    padding: 7px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
}

.use-suggestion:hover {
    background: #2f7446;
}
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="page">

    <div class="header">

        <a
            href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}"
            class="back-link"
        >
            ← Back to Questionnaire
        </a>

        <h1>Add Question</h1>

        <p class="subtitle">
            {{ $questionnaire->title }}
            · {{ $event->title }}
        </p>

    </div>

    <div class="card">

        @if ($errors->any())
            <div class="error-box">
                <strong>Please correct the following:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route(
                'events.questionnaires.questions.store',
                [$event, $questionnaire]
            ) }}"
        >

            @csrf

            <div class="form-group">

                <label for="question_text">
                    Question <span class="required">*</span>
                </label>

                <textarea
                    id="question_text"
                    name="question_text"
                    placeholder="Enter your question..."
                    required
                >{{ old('question_text') }}</textarea>

            </div>

            <div class="form-group">

                <label for="question_type">
                    Question Type <span class="required">*</span>
                </label>

                <select
                    id="question_type"
                    name="question_type"
                    required
                >
                    <option value="">Select question type</option>

                    <option value="text">Text Box</option>
                    <option value="textarea">Long Text</option>
                    <option value="email">Email</option>
                    <option value="phone">Phone Number</option>
                    <option value="radio">Radio Button</option>
                    <option value="select">Dropdown</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="poll">Poll</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="time">Time</option>
                    <option value="yes_no">Yes / No</option>
                    <option value="rating">Rating</option>
                </select>

                <div
    id="suggestionBox"
    class="suggestion-box"
>
    <div class="suggestion-text">
        💡 Suggested type:
        <strong id="suggestedType"></strong>
    </div>

    <button
        type="button"
        class="use-suggestion"
        onclick="useSuggestion()"
    >
        Use Suggestion
    </button>
</div>

            </div>

            <div
                id="optionsBox"
                class="options-box"
            >

                <div class="options-title">
                    Answer Options
                </div>

                <div class="options-description">
                    Add the choices that respondents can select.
                </div>

                <div id="optionsContainer">

                    <div class="option-row">
                        <input
                            type="text"
                            name="options[]"
                            placeholder="Option 1"
                        >

                        <button
                            type="button"
                            class="remove-option"
                            onclick="removeOption(this)"
                        >
                            ×
                        </button>
                    </div>

                    <div class="option-row">
                        <input
                            type="text"
                            name="options[]"
                            placeholder="Option 2"
                        >

                        <button
                            type="button"
                            class="remove-option"
                            onclick="removeOption(this)"
                        >
                            ×
                        </button>
                    </div>

                </div>

                <button
                    type="button"
                    class="add-option"
                    onclick="addOption()"
                >
                    + Add Option
                </button>

            </div>

            <div class="form-group" style="margin-top: 22px;">

                <div class="checkbox-row">

                    <input
                        type="checkbox"
                        id="is_required"
                        name="is_required"
                        value="1"
                    >

                    <label for="is_required">
                        Required question
                    </label>

                </div>

                <div class="hint">
                    Respondents must answer this question before submitting the questionnaire.
                </div>

            </div>

            {{-- ADVANCED QUESTION SETTINGS --}}
            <details style="margin-top: 15px; padding: 15px; background: #fbfdfc; border: 1px solid #dcece1; border-radius: 12px;">
                <summary style="font-weight: 700; color: #2e7d32; cursor: pointer;">
                    ⚙️ Question Settings & Validation Rules
                </summary>

                <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="placeholder" style="font-size: 13px;">Placeholder Text</label>
                        <input type="text" id="placeholder" name="placeholder" value="{{ old('placeholder') }}" placeholder="e.g. Enter full name...">
                    </div>

                    <div>
                        <label for="help_text" style="font-size: 13px;">Help Text / Instructions</label>
                        <input type="text" id="help_text" name="help_text" value="{{ old('help_text') }}" placeholder="Guidance for respondent or enumerator...">
                    </div>

                    <div>
                        <label for="min_length" style="font-size: 13px;">Min Character Length</label>
                        <input type="number" id="min_length" name="min_length" value="{{ old('min_length') }}" placeholder="e.g. 2">
                    </div>

                    <div>
                        <label for="max_length" style="font-size: 13px;">Max Character Length</label>
                        <input type="number" id="max_length" name="max_length" value="{{ old('max_length') }}" placeholder="e.g. 100">
                    </div>

                    <div>
                        <label for="min_value" style="font-size: 13px;">Min Numerical Value</label>
                        <input type="number" id="min_value" name="min_value" value="{{ old('min_value') }}" placeholder="e.g. 0">
                    </div>

                    <div>
                        <label for="max_value" style="font-size: 13px;">Max Numerical Value</label>
                        <input type="number" id="max_value" name="max_value" value="{{ old('max_value') }}" placeholder="e.g. 120">
                    </div>
                </div>

                <div style="margin-top: 15px; padding: 12px; background: #f0f7f2; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1e4b26;">
                        <input type="checkbox" name="is_primary_identifier" value="1" {{ old('is_primary_identifier') ? 'checked' : '' }}>
                        🔑 Designate as Primary Identifier (Prevent Duplicate Submissions)
                    </label>
                    <div style="font-size: 12px; color: #627467; margin-top: 4px; margin-left: 24px;">
                        Submissions with the same answer for this question will be rejected as duplicates.
                    </div>
                </div>
            </details>

            <div class="actions">

                <a
                    href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}"
                    class="btn btn-cancel"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Question
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    const questionText = document.getElementById('question_text');
    const questionType = document.getElementById('question_type');
    const optionsBox = document.getElementById('optionsBox');
    const optionsContainer = document.getElementById('optionsContainer');

    const suggestionBox = document.getElementById('suggestionBox');
    const suggestedType = document.getElementById('suggestedType');

    const optionTypes = [
        'radio',
        'select',
        'checkbox',
        'poll'
    ];

    const typeNames = {
        text: 'Text Box',
        textarea: 'Long Text',
        radio: 'Radio Button',
        select: 'Dropdown',
        checkbox: 'Checkbox',
        poll: 'Poll',
        number: 'Number',
        date: 'Date',
        time: 'Time',
        yes_no: 'Yes / No',
        rating: 'Rating',
        email: 'Email',
        phone: 'Phone Number'
    };

    function detectQuestionType(text) {

        const value = text.toLowerCase().trim();

        if (!value) {
            return null;
        }

        /* Date */
        if (
            value.includes('date of birth') ||
            value.includes('dob') ||
            value.includes('birth date') ||
            value.includes('birthday')
        ) {
            return 'date';
        }

        /* Time */
        if (
            value.includes('what time') ||
            value.includes('time of') ||
            value.includes('start time') ||
            value.includes('end time')
        ) {
            return 'time';
        }

        /* Number */
        if (
            value.includes('how old') ||
            value.includes('age') ||
            value.includes('how many') ||
            value.includes('how much')
        ) {
            return 'number';
        }

        /* Yes / No */
        if (
            value.includes('yes or no') ||
            value.includes('yes/no') ||
            value.includes('do you') ||
            value.includes('are you') ||
            value.includes('is the') ||
            value.includes('have you') ||
            value.includes('did you')
        ) {
            return 'yes_no';
        }

        /* Rating */
        if (
            value.includes('rate') ||
            value.includes('rating') ||
            value.includes('satisfied') ||
            value.includes('satisfaction') ||
            value.includes('how would you rate')
        ) {
            return 'rating';
        }

        /* Multiple selection */
        if (
            value.includes('select all') ||
            value.includes('choose all') ||
            value.includes('which activities') ||
            value.includes('which of these') ||
            value.includes('what activities')
        ) {
            return 'checkbox';
        }

        /* Single-choice fields */
        if (
            value.includes('gender') ||
            value.includes('sex') ||
            value.includes('relationship') ||
            value.includes('category')
        ) {
            return 'radio';
        }

        /* Dropdown */
        if (
            value.includes('select') ||
            value.includes('choose one') ||
            value.includes('which one') ||
            value.includes('district')
        ) {
            return 'select';
        }

        /* Long text */
        if (
            value.includes('describe') ||
            value.includes('explain') ||
            value.includes('comments') ||
            value.includes('address') ||
            value.includes('why') ||
            value.includes('feedback') ||
            value.includes('experience')
        ) {
            return 'textarea';
        }

        /* Email */
        if (value.includes('email') || value.includes('e-mail')) {
            return 'email';
        }

        /* Phone */
        if (
            value.includes('phone') ||
            value.includes('mobile') ||
            value.includes('contact number') ||
            value.includes('telephone') ||
            value.includes('cell')
        ) {
            return 'phone';
        }

        /* Normal text */
        if (
            value.includes('name') ||
            value.includes('school') ||
            value.includes('occupation')
        ) {
            return 'text';
        }

        return null;
    }

    function updateSuggestion() {

        const suggestion = detectQuestionType(
            questionText.value
        );

        if (suggestion) {

            suggestedType.textContent =
                typeNames[suggestion];

            suggestionBox.classList.add('active');

            suggestionBox.dataset.type =
                suggestion;

        } else {

            suggestionBox.classList.remove('active');

            suggestionBox.dataset.type = '';
        }
    }

    function useSuggestion() {

        const suggestion =
            suggestionBox.dataset.type;

        if (!suggestion) {
            return;
        }

        questionType.value = suggestion;

        updateOptionsVisibility();
    }

    questionText.addEventListener(
        'input',
        updateSuggestion
    );

    questionType.addEventListener(
        'change',
        updateOptionsVisibility
    );

    function updateOptionsVisibility() {

        if (optionTypes.includes(questionType.value)) {
            optionsBox.classList.add('active');
        } else {
            optionsBox.classList.remove('active');
        }
    }

    function addOption() {

        const optionNumber =
            optionsContainer.children.length + 1;

        const row =
            document.createElement('div');

        row.className = 'option-row';

        row.innerHTML = `
            <input
                type="text"
                name="options[]"
                placeholder="Option ${optionNumber}"
            >

            <button
                type="button"
                class="remove-option"
                onclick="removeOption(this)"
            >
                ×
            </button>
        `;

        optionsContainer.appendChild(row);
    }

    function removeOption(button) {

        const rows =
            optionsContainer.querySelectorAll(
                '.option-row'
            );

        if (rows.length <= 1) {
            return;
        }

        button.parentElement.remove();
    }

    updateSuggestion();
    updateOptionsVisibility();
</script>

</body>
</html>