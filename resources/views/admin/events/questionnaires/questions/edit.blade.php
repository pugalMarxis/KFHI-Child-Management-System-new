<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Question | {{ $questionnaire->title }}</title>

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
            margin-bottom: 25px;
        }

        .card {
            background: rgba(255, 255, 255, 0.92);
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

        .suggestion {
            display: none;
            margin-top: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #edf8f0;
            border: 1px solid #cce4d2;
        }

        .suggestion.show {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .suggestion-text {
            font-size: 14px;
            color: #41644c;
        }

        .suggestion-text strong {
            color: #287a45;
        }

        .use-suggestion {
            border: none;
            background: #398653;
            color: white;
            padding: 8px 13px;
            border-radius: 9px;
            cursor: pointer;
            font-weight: 700;
            white-space: nowrap;
        }

        .use-suggestion:hover {
            background: #2f7446;
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

        .add-option {
            border: 1px solid #8abd98;
            background: #edf8f0;
            color: #287a45;
            padding: 9px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
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

            .suggestion.show {
                align-items: stretch;
                flex-direction: column;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="page">

    <a
        href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}"
        class="back-link"
    >
        ← Back to Questionnaire
    </a>

    <h1>Edit Question</h1>

    <p class="subtitle">
        {{ $questionnaire->title }} · {{ $event->title }}
    </p>

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
                'events.questionnaires.questions.update',
                [$event, $questionnaire, $question]
            ) }}"
        >

            @csrf
            @method('PUT')

            <div class="form-group">

                <label for="question_text">
                    Question <span class="required">*</span>
                </label>

                <textarea
                    id="question_text"
                    name="question_text"
                    required
                >{{ old('question_text', $question->question_text) }}</textarea>

                <div class="suggestion" id="suggestionBox">

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

            <div class="form-group">

                <label for="question_type">
                    Question Type <span class="required">*</span>
                </label>

                <select
                    id="question_type"
                    name="question_type"
                    required
                >
                    <option value="text" @selected(old('question_type', $question->question_type) === 'text')>Text Box</option>
                    <option value="textarea" @selected(old('question_type', $question->question_type) === 'textarea')>Long Text</option>
                    <option value="email" @selected(old('question_type', $question->question_type) === 'email')>Email</option>
                    <option value="phone" @selected(old('question_type', $question->question_type) === 'phone')>Phone Number</option>
                    <option value="radio" @selected(old('question_type', $question->question_type) === 'radio')>Radio Button</option>
                    <option value="select" @selected(old('question_type', $question->question_type) === 'select')>Dropdown</option>
                    <option value="checkbox" @selected(old('question_type', $question->question_type) === 'checkbox')>Checkbox</option>
                    <option value="poll" @selected(old('question_type', $question->question_type) === 'poll')>Poll</option>
                    <option value="number" @selected(old('question_type', $question->question_type) === 'number')>Number</option>
                    <option value="date" @selected(old('question_type', $question->question_type) === 'date')>Date</option>
                    <option value="time" @selected(old('question_type', $question->question_type) === 'time')>Time</option>
                    <option value="yes_no" @selected(old('question_type', $question->question_type) === 'yes_no')>Yes / No</option>
                    <option value="rating" @selected(old('question_type', $question->question_type) === 'rating')>Rating</option>
                </select>

            </div>

            <div
                id="optionsBox"
                class="options-box"
            >

                <div class="options-title">
                    Answer Options
                </div>

                <div class="options-description">
                    Add or edit the choices available to respondents.
                </div>

                <div id="optionsContainer">

                    @forelse ($question->options as $option)

                        <div class="option-row">

                            <input
                                type="text"
                                name="options[]"
                                value="{{ $option->option_label }}"
                                placeholder="Answer option"
                            >

                            <button
                                type="button"
                                class="remove-option"
                                onclick="removeOption(this)"
                            >
                                ×
                            </button>

                        </div>

                    @empty

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

                    @endforelse

                </div>

                <button
                    type="button"
                    class="add-option"
                    onclick="addOption()"
                >
                    + Add Option
                </button>

            </div>

            <div
                class="form-group"
                style="margin-top: 22px;"
            >

                <div class="checkbox-row">

                    <input
                        type="checkbox"
                        id="is_required"
                        name="is_required"
                        value="1"
                        {{ old('is_required', $question->is_required) ? 'checked' : '' }}
                    >

                    <label for="is_required">
                        Required question
                    </label>

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
                        <input type="text" id="placeholder" name="placeholder" value="{{ old('placeholder', $question->setting('placeholder')) }}" placeholder="e.g. Enter full name...">
                    </div>

                    <div>
                        <label for="help_text" style="font-size: 13px;">Help Text / Instructions</label>
                        <input type="text" id="help_text" name="help_text" value="{{ old('help_text', $question->setting('help_text')) }}" placeholder="Guidance for respondent or enumerator...">
                    </div>

                    <div>
                        <label for="min_length" style="font-size: 13px;">Min Character Length</label>
                        <input type="number" id="min_length" name="min_length" value="{{ old('min_length', $question->setting('min_length')) }}" placeholder="e.g. 2">
                    </div>

                    <div>
                        <label for="max_length" style="font-size: 13px;">Max Character Length</label>
                        <input type="number" id="max_length" name="max_length" value="{{ old('max_length', $question->setting('max_length')) }}" placeholder="e.g. 100">
                    </div>

                    <div>
                        <label for="min_value" style="font-size: 13px;">Min Numerical Value</label>
                        <input type="number" id="min_value" name="min_value" value="{{ old('min_value', $question->setting('min_value')) }}" placeholder="e.g. 0">
                    </div>

                    <div>
                        <label for="max_value" style="font-size: 13px;">Max Numerical Value</label>
                        <input type="number" id="max_value" name="max_value" value="{{ old('max_value', $question->setting('max_value')) }}" placeholder="e.g. 120">
                    </div>
                </div>

                <div style="margin-top: 15px; padding: 12px; background: #f0f7f2; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1e4b26;">
                        <input type="checkbox" name="is_primary_identifier" value="1" {{ old('is_primary_identifier', $question->isPrimaryIdentifier()) ? 'checked' : '' }}>
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
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    const questionText =
        document.getElementById('question_text');

    const questionType =
        document.getElementById('question_type');

    const suggestionBox =
        document.getElementById('suggestionBox');

    const suggestedType =
        document.getElementById('suggestedType');

    const optionsBox =
        document.getElementById('optionsBox');

    const optionsContainer =
        document.getElementById('optionsContainer');

    let currentSuggestion = null;

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

        if (
            value.includes('date of birth') ||
            value.includes('dob') ||
            value.includes('birth date') ||
            value.includes('birthday')
        ) {
            return 'date';
        }

        if (
            value.includes('what time') ||
            value.includes('time of') ||
            value.includes('start time') ||
            value.includes('end time')
        ) {
            return 'time';
        }

        if (
            value.includes('how old') ||
            value.includes('age') ||
            value.includes('how many')
        ) {
            return 'number';
        }

        if (
            value.includes('yes or no') ||
            value.includes('yes/no') ||
            value.includes('do you') ||
            value.includes('are you') ||
            value.includes('is the') ||
            value.includes('have you')
        ) {
            return 'yes_no';
        }

        if (
            value.includes('rate') ||
            value.includes('rating') ||
            value.includes('satisfied') ||
            value.includes('satisfaction') ||
            value.includes('how would you rate')
        ) {
            return 'rating';
        }

        if (
            value.includes('select all') ||
            value.includes('choose all') ||
            value.includes('which activities') ||
            value.includes('which of these')
        ) {
            return 'checkbox';
        }

        if (
            value.includes('gender') ||
            value.includes('sex') ||
            value.includes('relationship') ||
            value.includes('category')
        ) {
            return 'radio';
        }

        if (
            value.includes('select') ||
            value.includes('choose one') ||
            value.includes('which one') ||
            value.includes('district')
        ) {
            return 'select';
        }

        if (
            value.includes('describe') ||
            value.includes('explain') ||
            value.includes('comments') ||
            value.includes('address') ||
            value.includes('why')
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

        if (
            value.includes('name') ||
            value.includes('school')
        ) {
            return 'text';
        }

        return null;
    }

    function checkSuggestion() {

        const detected =
            detectQuestionType(questionText.value);

        currentSuggestion = detected;

        if (detected) {

            suggestedType.textContent =
                typeNames[detected];

            suggestionBox.classList.add('show');

        } else {

            suggestionBox.classList.remove('show');

        }
    }

    function useSuggestion() {

        if (!currentSuggestion) {
            return;
        }

        questionType.value = currentSuggestion;

        updateOptionsVisibility();

        suggestionBox.classList.remove('show');
    }

    questionText.addEventListener(
        'input',
        checkSuggestion
    );

    const optionTypes = [
        'radio',
        'select',
        'checkbox',
        'poll'
    ];

    function updateOptionsVisibility() {

        if (optionTypes.includes(questionType.value)) {
            optionsBox.classList.add('active');
        } else {
            optionsBox.classList.remove('active');
        }
    }

    questionType.addEventListener(
        'change',
        updateOptionsVisibility
    );

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
            optionsContainer.querySelectorAll('.option-row');

        if (rows.length <= 1) {
            return;
        }

        button.parentElement.remove();
    }

    questionType.value =
        @json($question->question_type);

    updateOptionsVisibility();
</script>

</body>
</html>