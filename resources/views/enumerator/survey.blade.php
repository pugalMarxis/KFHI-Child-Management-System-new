<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $questionnaire->title }} - Enumerator Survey Form - KFHI</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #eef7f1;
            color: #1f3d2b;
            min-height: 100vh;
            padding: 25px 15px 60px;
        }

        .container {
            width: min(840px, 100%);
            margin: 0 auto;
        }

        .header-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 26px 30px;
            margin-bottom: 22px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            border-left: 6px solid #2e7d32;
        }

        .brand-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        h1 {
            color: #1e4b26;
            font-size: 24px;
            margin-bottom: 6px;
        }

        .meta-line {
            color: #5c7063;
            font-size: 13px;
        }

        /* QR Scanner Section */
        .qr-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 22px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            border: 2px dashed #a5d6a7;
        }

        .qr-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .qr-header h2 {
            font-size: 18px;
            color: #1b5e20;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .scanner-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.15s ease;
        }

        .btn-primary { background: #2e7d32; color: #ffffff; }
        .btn-primary:hover { background: #246328; }
        .btn-secondary { background: #e8f3eb; color: #2e7d32; }
        .btn-secondary:hover { background: #d7ebd9; }

        .scanner-reader {
            width: 100%;
            max-width: 520px;
            margin: 0 auto 15px;
            overflow: hidden;
            border-radius: 16px;
        }

        .identified-box {
            display: none;
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 15px;
        }

        .identified-box.active {
            display: block;
        }

        .identified-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
            font-size: 13px;
        }

        .id-item {
            background: #ffffff;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #bbf7d0;
        }

        .id-item strong {
            display: block;
            color: #15803d;
            font-size: 11px;
            text-transform: uppercase;
        }

        /* Questionnaire Fields */
        .question-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 18px;
            box-shadow: 0 4px 18px rgba(46, 125, 50, 0.06);
            border: 1px solid #e1eee4;
        }

        .q-number {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 3px 8px;
            border-radius: 8px;
            margin-bottom: 6px;
        }

        .q-title {
            font-size: 16px;
            font-weight: 700;
            color: #204928;
            margin-bottom: 6px;
        }

        .q-help {
            font-size: 13px;
            color: #687d6f;
            margin-bottom: 12px;
        }

        .locked-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            background: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 6px;
            margin-left: 6px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea,
        select {
            width: 100%;
            border: 1px solid #cfe0d3;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            background: #fbfdfb;
            color: #1f3d2b;
            outline: none;
            transition: border 0.15s ease;
        }

        input[readonly], textarea[readonly], select[readonly] {
            background: #f1f6f2 !important;
            color: #4b6352 !important;
            cursor: not-allowed;
            border-color: #d1dfd5;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.10);
        }

        textarea {
            min-height: 95px;
            resize: vertical;
        }

        .options-group {
            display: flex;
            flex-direction: column;
            gap: 9px;
            margin-top: 10px;
        }

        .option-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f7faf8;
            border: 1px solid #e1eee4;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .option-label:hover { background: #edf6ef; }
        .option-label input { accent-color: #2e7d32; width: 17px; height: 17px; }

        .rating-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .rating-box {
            position: relative;
        }

        .rating-box input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .rating-box span {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #f0f7f2;
            color: #2e7d32;
            font-weight: 700;
            border: 1px solid #d2e4d6;
            cursor: pointer;
        }

        .rating-box input:checked + span {
            background: #2e7d32;
            color: #ffffff;
        }

        .submit-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .alert-error {
            background: #fdeaea;
            border: 1px solid #f5c6cb;
            color: #b71c1c;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 22px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header-card">
        <span class="brand-badge">Field Survey Enumerator Collection</span>
        <h1>{{ $questionnaire->title }}</h1>
        <div class="meta-line">
            Enumerator: <strong>{{ $enumerator->name }} ({{ $enumerator->enumerator_code }})</strong>
            @if($questionnaire->event) · Event: {{ $questionnaire->event->title }} @endif
            · Session started at: {{ now()->format('h:i A') }}
        </div>
        <div style="margin-top: 12px;">
            <a href="{{ route('enumerator.dashboard') }}" style="font-size: 13px; color: #2e7d32; text-decoration: none; font-weight: 700;">
                ← Back to Enumerator Dashboard
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <strong>Please resolve the following submission errors:</strong>
            <ul style="margin-left: 20px; margin-top: 6px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- QR CODE SCANNER & CHILD IDENTIFICATION SECTION --}}
    <div class="qr-card">
        <div class="qr-header">
            <h2>📷 Child QR Scanner & Data Autofill</h2>
            <span style="font-size: 12px; color: #4b6352;">Scan badge or enter child code to autofill details</span>
        </div>

        <div class="scanner-controls">
            <button type="button" class="btn btn-primary" id="startEnumeratorScanner" onclick="startEnumeratorScanner()">
                Start Scanner
            </button>
            <button type="button" class="btn btn-secondary" id="stopEnumeratorScanner" onclick="stopCameraScanner()" disabled>
                Stop Scanner
            </button>

            <label class="btn btn-secondary" style="cursor: pointer;">
                📁 Upload QR Image
                <input type="file" id="qrFileInput" accept="image/*" style="display:none;" onchange="handleQrFileUpload(event)">
            </label>

            <div style="display: flex; gap: 6px; flex: 1; min-width: 260px;">
                <input type="text" id="manualCodeInput" placeholder="Or enter Child Code e.g. CH001..." style="padding: 10px 12px; border: 1px solid #cfe0d3; border-radius: 10px; font-size: 13px; flex: 1;">
                <button type="button" class="btn btn-secondary" onclick="lookupManualCode()">Lookup</button>
            </div>
        </div>

        <div id="qrReader" class="scanner-reader"></div>

        <div id="scanStatus" style="font-size: 13px; color: #65756a; margin-top: 4px;"></div>

        <div class="identified-box" id="identifiedBox">
            <strong style="color: #15803d; font-size: 14px;">✅ Child Identified Successfully:</strong>
            <div class="identified-grid">
                <div class="id-item">
                    <strong>Child Code</strong>
                    <span id="childCodeDisplay">-</span>
                </div>
                <div class="id-item">
                    <strong>Full Name</strong>
                    <span id="childNameDisplay">-</span>
                </div>
                <div class="id-item">
                    <strong>Gender</strong>
                    <span id="childGenderDisplay">-</span>
                </div>
                <div class="id-item">
                    <strong>Date of Birth</strong>
                    <span id="childDobDisplay">-</span>
                </div>
                <div class="id-item">
                    <strong>School & Grade</strong>
                    <span id="childSchoolDisplay">-</span>
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #166534;">
                Mapped questionnaire fields below have been automatically populated.
            </div>
        </div>
    </div>

    {{-- MAIN SURVEY FORM --}}
    <form method="POST" action="{{ route('enumerator.survey.store', $questionnaire) }}" id="surveyForm">
        @csrf
        <input type="hidden" name="child_id" id="hiddenChildId" value="{{ old('child_id') }}">
        <input type="hidden" name="started_at" value="{{ $startedAt }}">

        @php
            $mappingLookup = collect($fieldMapping)->keyBy('question_id');
        @endphp

        @foreach($questionnaire->questions as $index => $question)
            @php
                $fieldName = 'question_' . $question->id;
                $val = old($fieldName, $question->setting('default_value'));
                $help = $question->setting('help_text');
                $placeholder = $question->setting('placeholder') ?: 'Your response...';
                $map = $mappingLookup->get($question->id);
                $isMapped = !empty($map['child_field']);
                $isReadOnly = $isMapped && !empty($map['readonly_for_enumerator']);
            @endphp

            <div class="question-card" data-q-id="{{ $question->id }}" data-child-field="{{ $map['child_field'] ?? '' }}">
                <span class="q-number">Question {{ $index + 1 }}</span>
                <div class="q-title">
                    {{ $question->question_text }}
                    @if($question->is_required)
                        <span style="color: #c62828;">*</span>
                    @endif
                    @if($isReadOnly)
                        <span class="locked-badge">🔒 Read-Only (Child Record)</span>
                    @endif
                </div>

                @if($help)
                    <div class="q-help">{{ $help }}</div>
                @endif

                {{-- SHORT TEXT --}}
                @if($question->question_type === 'text')
                    <input type="text"
                           name="{{ $fieldName }}"
                           id="input_q_{{ $question->id }}"
                           value="{{ $val }}"
                           placeholder="{{ $placeholder }}"
                           @if($isReadOnly) readonly @endif
                           @if($question->is_required) required @endif>

                {{-- EMAIL --}}
                @elseif($question->question_type === 'email')
                    <input type="email"
                           name="{{ $fieldName }}"
                           id="input_q_{{ $question->id }}"
                           value="{{ $val }}"
                           placeholder="example@email.com"
                           @if($isReadOnly) readonly @endif
                           @if($question->is_required) required @endif>

                {{-- PHONE --}}
                @elseif($question->question_type === 'phone')
                    <input type="tel"
                           name="{{ $fieldName }}"
                           id="input_q_{{ $question->id }}"
                           value="{{ $val }}"
                           placeholder="+94 XX XXX XXXX"
                           @if($isReadOnly) readonly @endif
                           @if($question->is_required) required @endif>

                {{-- LONG TEXT / TEXTAREA --}}
                @elseif($question->question_type === 'textarea')
                    <textarea name="{{ $fieldName }}"
                              id="input_q_{{ $question->id }}"
                              placeholder="{{ $placeholder }}"
                              @if($isReadOnly) readonly @endif
                              @if($question->is_required) required @endif>{{ $val }}</textarea>

                {{-- NUMBER --}}
                @elseif($question->question_type === 'number')
                    <input type="number"
                           name="{{ $fieldName }}"
                           id="input_q_{{ $question->id }}"
                           value="{{ $val }}"
                           placeholder="Enter number..."
                           @if($question->setting('min_value') !== null) min="{{ $question->setting('min_value') }}" @endif
                           @if($question->setting('max_value') !== null) max="{{ $question->setting('max_value') }}" @endif
                           step="any"
                           @if($isReadOnly) readonly @endif
                           @if($question->is_required) required @endif>

                {{-- DATE --}}
                @elseif($question->question_type === 'date')
                    <input type="date"
                           name="{{ $fieldName }}"
                           id="input_q_{{ $question->id }}"
                           value="{{ $val }}"
                           @if($isReadOnly) readonly @endif
                           @if($question->is_required) required @endif>

                {{-- TIME --}}
                @elseif($question->question_type === 'time')
                    <input type="time"
                           name="{{ $fieldName }}"
                           id="input_q_{{ $question->id }}"
                           value="{{ $val }}"
                           @if($isReadOnly) readonly @endif
                           @if($question->is_required) required @endif>

                {{-- DROPDOWN MENU --}}
                @elseif($question->question_type === 'select')
                    <select name="{{ $fieldName }}" id="input_q_{{ $question->id }}" @if($question->is_required) required @endif>
                        <option value="">-- Please select an option --</option>
                        @foreach($question->options as $opt)
                            @php $optVal = $opt->option_value ?? $opt->option_label; @endphp
                            <option value="{{ $optVal }}" @selected($val === $optVal)>{{ $opt->option_label }}</option>
                        @endforeach
                    </select>

                {{-- RADIO BUTTONS / POLL --}}
                @elseif($question->question_type === 'radio' || $question->question_type === 'poll')
                    <div class="options-group">
                        @foreach($question->options as $opt)
                            @php $optVal = $opt->option_value ?? $opt->option_label; @endphp
                            <label class="option-label">
                                <input type="radio"
                                       name="{{ $fieldName }}"
                                       value="{{ $optVal }}"
                                       @checked($val === $optVal)
                                       @if($question->is_required && $loop->first) required @endif>
                                <span>{{ $opt->option_label }}</span>
                            </label>
                        @endforeach
                    </div>

                {{-- CHECKBOXES --}}
                @elseif($question->question_type === 'checkbox')
                    @php $checkedVals = is_array($val) ? $val : []; @endphp
                    <div class="options-group">
                        @foreach($question->options as $opt)
                            @php $optVal = $opt->option_value ?? $opt->option_label; @endphp
                            <label class="option-label">
                                <input type="checkbox"
                                       name="{{ $fieldName }}[]"
                                       value="{{ $optVal }}"
                                       @checked(in_array($optVal, $checkedVals))>
                                <span>{{ $opt->option_label }}</span>
                            </label>
                        @endforeach
                    </div>

                {{-- YES / NO --}}
                @elseif($question->question_type === 'yes_no')
                    <div class="options-group">
                        <label class="option-label">
                            <input type="radio"
                                   name="{{ $fieldName }}"
                                   value="Yes"
                                   @checked($val === 'Yes')
                                   @if($question->is_required) required @endif>
                            <span>Yes</span>
                        </label>
                        <label class="option-label">
                            <input type="radio"
                                   name="{{ $fieldName }}"
                                   value="No"
                                   @checked($val === 'No')>
                            <span>No</span>
                        </label>
                    </div>

                {{-- RATING --}}
                @elseif($question->question_type === 'rating')
                    <div class="rating-group">
                        @for($rate = 1; $rate <= 5; $rate++)
                            <label class="rating-box">
                                <input type="radio"
                                       name="{{ $fieldName }}"
                                       value="{{ $rate }}"
                                       @checked((string)$val === (string)$rate)
                                       @if($question->is_required && $rate === 1) required @endif>
                                <span>{{ $rate }}</span>
                            </label>
                        @endfor
                    </div>
                @endif
            </div>
        @endforeach

        <div class="submit-card">
            <div>
                <strong style="color: #2e7d32;">Ready to submit?</strong>
                <div style="font-size: 13px; color: #65776a;">
                    Response will be safely saved in staging for administrative review before merging.
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 13px 28px; font-size: 15px;">
                Submit Survey Response
            </button>
        </div>
    </form>

</div>

<script src="{{ rtrim(request()->getBaseUrl(), '/') }}/js/offline-child-cache.js"></script>
<script src="{{ rtrim(request()->getBaseUrl(), '/') }}/js/html5-qrcode.min.js"></script>
<script>
    const fieldMapping = @json($fieldMapping);
    const currentEnumeratorId = @json($enumerator->id);
    let qrScanner = null;
    let scannerRunning = false;
    let qrLookupInProgress = false;

    function setScanStatus(msg, isError = false) {
        const el = document.getElementById('scanStatus');
        el.textContent = msg;
        el.style.color = isError ? '#dc2626' : '#2e7d32';
    }

    async function processQrPayload(payload) {
        if (qrLookupInProgress) return;
        qrLookupInProgress = true;
        try {
            await lookupQrPayload(payload);
        } finally {
            qrLookupInProgress = false;
        }
    }

    // Check permitted local records first when offline, otherwise ask Laravel.
    async function lookupQrPayload(payload) {
        setScanStatus('Processing QR data...');

        let childCode = '';
        try {
            const parsed = JSON.parse(payload);
            childCode = parsed.code || parsed.c || '';
        } catch (e) {
            // New printable cards contain only the stable child code.
            const rawCode = String(payload || '').trim();
            if (rawCode && !rawCode.includes('/qr/scan/')) childCode = rawCode;
        }

        const lookupLocally = async () => {
            if (!childCode || !window.KFHIChildOfflineCache) return false;
            const child = await window.KFHIChildOfflineCache.findByCode(childCode, currentEnumeratorId);
            if (!child || child.status !== 'active') return false;
            applyChildData(child);
            setScanStatus('Child details loaded from this phone’s saved records.');
            stopCameraScanner();
            return true;
        };

        if (!navigator.onLine) {
            try {
                if (await lookupLocally()) return;
            } catch (e) {}
            setScanStatus('This child is not saved on this phone. Connect and sync this child first.', true);
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('{{ route("enumerator.qr.lookup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: payload })
            });

            const data = await res.json();
            if (data.success && data.child) {
                applyChildData(data.child);
                setScanStatus('Child data retrieved and fields autofilled successfully!');
                stopCameraScanner();
            } else {
                setScanStatus(data.message || 'Could not identify child from QR code.', true);
            }
        } catch (e) {
            try {
                if (await lookupLocally()) return;
            } catch (localError) {}
            setScanStatus('Could not contact the server, and this child is not saved on this phone.', true);
        }
    }

    function applyChildData(child) {
        document.getElementById('hiddenChildId').value = child.id || '';
        document.getElementById('childCodeDisplay').textContent = child.child_code || 'N/A';
        document.getElementById('childNameDisplay').textContent = child.full_name || 'N/A';
        document.getElementById('childGenderDisplay').textContent = child.gender || 'N/A';
        document.getElementById('childDobDisplay').textContent = child.date_of_birth || 'N/A';
        document.getElementById('childSchoolDisplay').textContent = (child.school_name || 'N/A') + (child.grade ? ' (' + child.grade + ')' : '');
        document.getElementById('identifiedBox').classList.add('active');

        // Autofill questionnaire fields based on creator's mapping
        fieldMapping.forEach(mapping => {
            if (!mapping.autofill) return;

            const qId = mapping.question_id;
            const childField = mapping.child_field;
            let val = null;

            if (childField === 'child_code') val = child.child_code;
            else if (childField === 'first_name') val = child.first_name || child.full_name?.split(' ')[0];
            else if (childField === 'last_name') val = child.last_name;
            else if (childField === 'date_of_birth') val = child.date_of_birth;
            else if (childField === 'gender') val = child.gender;
            else if (childField === 'school_name') val = child.school_name;
            else if (childField === 'grade') val = child.grade;
            else if (childField === 'district') val = child.district;
            else if (childField === 'gn_division') val = child.gn_division;
            else if (childField === 'address') val = child.address;

            if (val !== null && val !== undefined) {
                const input = document.getElementById('input_q_' + qId);
                if (input) {
                    input.value = val;
                }
            }
        });
    }

    function lookupManualCode() {
        const code = document.getElementById('manualCodeInput').value.trim();
        if (!code) {
            alert('Please enter a Child Code.');
            return;
        }

        // Wrap as mock offline json or code for lookup
        const payload = JSON.stringify({ kfhi: 'offline_v1', code: code, name: code });
        processQrPayload(payload);
    }

    // Use the same local html5-qrcode library and camera setup as the staff scanner.
    async function startEnumeratorScanner() {
        if (scannerRunning) return;
        if (typeof window.Html5Qrcode !== 'function') {
            setScanStatus('The QR scanner library did not load. Refresh while online and try again.', true);
            return;
        }

        try {
            qrScanner = new Html5Qrcode('qrReader');
            await qrScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                decodedText => processQrPayload(decodedText),
                () => {}
            );
            scannerRunning = true;
            document.getElementById('startEnumeratorScanner').disabled = true;
            document.getElementById('stopEnumeratorScanner').disabled = false;
            setScanStatus('Scanner started. Point the camera at the child QR code.');
        } catch (error) {
            scannerRunning = false;
            qrScanner = null;
            setScanStatus('Unable to start the camera. Allow camera access and use HTTPS or localhost.', true);
        }
    }

    async function stopCameraScanner() {
        if (qrScanner && scannerRunning) {
            try {
                await qrScanner.stop();
                qrScanner.clear();
            } catch (error) {
                console.error('Could not stop QR scanner cleanly:', error);
            }
        }
        qrScanner = null;
        scannerRunning = false;
        document.getElementById('startEnumeratorScanner').disabled = false;
        document.getElementById('stopEnumeratorScanner').disabled = true;
    }

    async function handleQrFileUpload(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        if (typeof window.Html5Qrcode !== 'function') {
            setScanStatus('The QR scanner library did not load. Refresh while online and try again.', true);
            return;
        }

        if (scannerRunning) await stopCameraScanner();
        const fileDecoder = new Html5Qrcode('qrReader');
        try {
            const decodedText = await fileDecoder.scanFile(file, true);
            await processQrPayload(decodedText);
        } catch (error) {
            setScanStatus('No readable QR code was found in that image.', true);
        } finally {
            try { fileDecoder.clear(); } catch (error) {}
            event.target.value = '';
        }
    }

    window.addEventListener('pagehide', () => {
        if (qrScanner && scannerRunning) qrScanner.stop().catch(() => {});
    });
</script>

</body>
</html>
