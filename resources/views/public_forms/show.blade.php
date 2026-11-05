<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $questionnaire->title }} - Questionnaire | KFHI</title>

    <!-- HTML5-QRCode Scanner Library for reliable camera scanning on all devices -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

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
            padding: 20px 15px 60px;
        }

        .container {
            width: min(800px, 100%);
            margin: 0 auto;
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-brand {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #2e7d32;
            text-transform: uppercase;
        }

        .btn-dash-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            color: #2e7d32;
            border: 1px solid #b8d4bd;
            padding: 8px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.15s ease;
        }

        .btn-dash-link:hover {
            background: #e8f5e9;
        }

        .header-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 26px 28px;
            margin-bottom: 20px;
            border-left: 6px solid #2e7d32;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .brand-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        h1 {
            color: #1e4626;
            font-size: 25px;
            margin-bottom: 8px;
        }

        .event-sub {
            color: #65756a;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .description {
            color: #4f6356;
            line-height: 1.6;
            font-size: 14px;
            border-top: 1px solid #edf4ee;
            padding-top: 12px;
            margin-top: 12px;
        }

        /* Enumerator Identification Banner */
        .enumerator-bar {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(46, 125, 50, 0.07);
            border: 1px solid #d0e4d4;
        }

        .enum-verified {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            color: #1f4d27;
            font-size: 14px;
        }

        .enum-toggle-box {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .enum-input-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .enum-input-row input {
            padding: 9px 12px;
            border: 1px solid #cfe0d3;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            flex: 1;
            min-width: 140px;
        }

        .btn-inline {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        /* QR Scanner Section */
        .qr-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            border: 2px dashed #9bc9a0;
        }

        .qr-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .qr-header h2 {
            font-size: 17px;
            color: #1b5e20;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .scanner-controls {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .btn-scan {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 9px;
            padding: 9px 15px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.15s ease;
        }

        .btn-scan-primary { background: #2e7d32; color: #ffffff; }
        .btn-scan-primary:hover { background: #246328; }
        .btn-scan-secondary { background: #e8f3eb; color: #2e7d32; }
        .btn-scan-secondary:hover { background: #d7ebd9; }

        .video-box {
            display: none;
            position: relative;
            max-width: 440px;
            margin: 0 auto 15px;
            border-radius: 14px;
            overflow: hidden;
            background: #000;
            border: 3px solid #2e7d32;
        }

        #qrReader {
            width: 100%;
        }

        .identified-box {
            display: none;
            background: #f1f8f3;
            border-radius: 14px;
            padding: 16px;
            margin-top: 14px;
            border: 1px solid #c8e2ce;
        }

        .identified-box.active {
            display: block;
        }

        .child-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .child-chip {
            background: #ffffff;
            border-radius: 8px;
            padding: 8px 10px;
            border: 1px solid #d7e8db;
        }

        .chip-label {
            font-size: 10px;
            font-weight: 700;
            color: #65756a;
            text-transform: uppercase;
        }

        .chip-val {
            font-size: 13px;
            font-weight: 700;
            color: #1f4427;
        }

        .alert-error {
            background: #fdeaea;
            border: 1px solid #f5c6cb;
            color: #b71c1c;
            border-radius: 12px;
            padding: 15px 18px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .alert-error ul {
            margin-left: 20px;
            margin-top: 6px;
        }

        /* Question Cards */
        .question-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 18px;
            box-shadow: 0 4px 18px rgba(46, 125, 50, 0.06);
            border: 1px solid #e5ede7;
        }

        .q-number {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 3px 8px;
            border-radius: 5px;
            margin-bottom: 8px;
        }

        .q-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f3d2b;
            margin-bottom: 6px;
        }

        .q-required {
            color: #dc2626;
            margin-left: 3px;
        }

        .q-help {
            font-size: 13px;
            color: #617366;
            margin-bottom: 14px;
            line-height: 1.4;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="tel"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            width: 100%;
            border: 1px solid #cfe0d3;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            outline: none;
            background: #fbfdfb;
            color: #1f3d2b;
            transition: border 0.15s ease, box-shadow 0.15s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.10);
            background: #ffffff;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .option-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .option-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 1px solid #dce8df;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .option-label:hover {
            background: #f4faf6;
        }

        .option-label input {
            accent-color: #2e7d32;
            width: 17px;
            height: 17px;
        }

        .rating-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .rating-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border: 1px solid #cfe0d3;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        .rating-box input {
            display: none;
        }

        .rating-box:has(input:checked) {
            background: #2e7d32;
            color: #ffffff;
            border-color: #2e7d32;
        }

        .error-msg {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            font-weight: 600;
        }

        .submit-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px 28px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .submit-notice {
            font-size: 13px;
            color: #65776a;
            line-height: 1.4;
        }

        .btn-submit {
            background: #2e7d32;
            color: #ffffff;
            border: none;
            padding: 14px 32px;
            border-radius: 11px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(46, 125, 50, 0.25);
            transition: background 0.15s ease;
        }

        .btn-submit:hover {
            background: #236327;
        }

        @media (max-width: 600px) {
            .submit-card {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-submit {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="top-nav">
        <div class="nav-brand">KFHI Data Collection Portal</div>
        <a href="{{ route('enumerator.dashboard') }}" class="btn-dash-link">
            📊 Open Enumerator Dashboard ↗
        </a>
    </div>

    <div class="header-card">
        <span class="brand-badge">KFHI Field Questionnaire</span>
        <h1>{{ $questionnaire->title }}</h1>
        @if($questionnaire->event)
            <div class="event-sub">Associated Event: <strong>{{ $questionnaire->event->title }}</strong></div>
        @endif
        @if($questionnaire->description)
            <div class="description">{{ $questionnaire->description }}</div>
        @endif
    </div>

    <!-- Enumerator Identification & Tracking Bar -->
    <div class="enumerator-bar">
        @if(session('verified_enumerator_id'))
            <div class="enum-verified">
                <div>
                    👤 Active Field Enumerator: <strong>{{ session('verified_enumerator_name') }}</strong>
                    <span style="background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 12px; margin-left: 6px;">{{ session('verified_enumerator_code') }}</span>
                </div>
                <a href="{{ route('enumerator.switch') }}" style="color: #c44; text-decoration: none; font-size: 13px; font-weight: 600;">
                    Switch Identity
                </a>
            </div>
        @else
            <div class="enum-toggle-box">
                <div style="font-size: 13px; color: #35583b; line-height: 1.4;">
                    <strong>📱 Field Enumerator Mode:</strong> Enter your Name & Common Password to enable camera QR code scanning and attribute submissions to your profile.
                </div>
                <div class="enum-input-row">
                    <input type="text" id="inlineEnumName" placeholder="Your Full Name (e.g. Mohamed Faizer)" required>
                    <input type="password" id="inlineEnumPass" placeholder="Enter your password" required>
                    <button type="button" class="btn-inline" onclick="verifyInlineEnumerator()">
                        Unlock Scanner
                    </button>
                </div>
                <div id="inlineVerifyStatus" style="font-size: 12px; font-weight: 600; display: none;"></div>
            </div>
        @endif
    </div>

    <!-- ONLINE CAMERA QR CODE SCANNER -->
    <div class="qr-card">
        <div class="qr-header">
            <h2>📷 Online Child QR Code Scanner</h2>
            <span id="scanStatus" style="font-size: 12px; font-weight: 700; color: #2e7d32;">
                Ready to scan
            </span>
        </div>

        <p style="font-size: 13px; color: #5d7163; margin-bottom: 14px; line-height: 1.4;">
            Scan a child's QR badge using your device camera or upload an image to automatically identify the child and autofill questionnaire fields.
        </p>

        <div class="scanner-controls">
            <button type="button" class="btn-scan btn-scan-primary" id="btnToggleCamera" onclick="toggleHtml5Camera()">
                📷 Open Camera Scanner
            </button>

            <label class="btn-scan btn-scan-secondary" style="cursor: pointer;">
                📁 Upload QR Image
                <input type="file" id="qrFileInput" accept="image/*" style="display: none;" onchange="handleQrFileUpload(event)">
            </label>

            <div style="display: flex; gap: 6px; flex: 1; min-width: 220px;">
                <input type="text" id="manualCodeInput" placeholder="Or type Child Code (e.g. KFHI-COL-001)" style="padding: 8px 12px; font-size: 13px;">
                <button type="button" class="btn-scan btn-scan-secondary" onclick="lookupManualCode()">
                    Find Child
                </button>
            </div>
        </div>

        <!-- Video element container for HTML5 QR Reader -->
        <div id="videoBox" class="video-box">
            <div id="qrReader"></div>
            <button type="button" onclick="stopHtml5Camera()" style="position: absolute; top: 10px; right: 10px; z-index: 10; background: rgba(0,0,0,0.6); color: #fff; border: none; border-radius: 6px; padding: 6px 12px; cursor: pointer; font-size: 12px;">
                ✕ Close Camera
            </button>
        </div>

        <!-- Identified Child Verification Details Card -->
        <div id="identifiedChildBox" class="identified-box">
            <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #2e7d32; margin-bottom: 4px;">
                ✅ Child Identified & Autofilled
            </div>
            <div style="font-size: 16px; font-weight: 800; color: #1f4427;" id="childNameDisplay">---</div>

            <div class="child-grid">
                <div class="child-chip">
                    <div class="chip-label">Child Code</div>
                    <div class="chip-val" id="childCodeDisplay">---</div>
                </div>
                <div class="child-chip">
                    <div class="chip-label">Gender</div>
                    <div class="chip-val" id="childGenderDisplay">---</div>
                </div>
                <div class="child-chip">
                    <div class="chip-label">Date of Birth</div>
                    <div class="chip-val" id="childDobDisplay">---</div>
                </div>
                <div class="child-chip">
                    <div class="chip-label">School / Grade</div>
                    <div class="chip-val" id="childSchoolDisplay">---</div>
                </div>
                <div class="child-chip">
                    <div class="chip-label">District</div>
                    <div class="chip-val" id="childDistrictDisplay">---</div>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <strong>Submission could not be accepted:</strong>
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('forms.store', $questionnaire->public_token) }}" id="surveyForm">
        @csrf

        <!-- Hidden tracking inputs -->
        <input type="hidden" name="child_id" id="hiddenChildId" value="{{ old('child_id') }}">
        <input type="hidden" name="enumerator_name" id="hiddenEnumeratorName" value="{{ session('verified_enumerator_name') }}">

        @foreach($questionnaire->questions as $index => $question)
            @php
                $fieldName = 'question_' . $question->id;
                $val = old($fieldName, $question->setting('default_value'));
                $help = $question->setting('help_text');
                $placeholder = $question->setting('placeholder') ?: 'Your answer...';
            @endphp

            <div class="question-card" id="card_{{ $question->id }}">
                <span class="q-number">Question {{ $index + 1 }}</span>
                <div class="q-title">
                    {{ $question->question_text }}
                    @if($question->is_required)
                        <span class="q-required">*</span>
                    @endif
                </div>

                @if($help)
                    <div class="q-help">{{ $help }}</div>
                @endif

                {{-- SHORT TEXT --}}
                @if($question->question_type === 'text')
                    <input type="text"
                           id="input_q_{{ $question->id }}"
                           name="{{ $fieldName }}"
                           value="{{ $val }}"
                           placeholder="{{ $placeholder }}"
                           @if($question->setting('max_length')) maxlength="{{ $question->setting('max_length') }}" @endif
                           @if($question->is_required) required @endif>

                {{-- EMAIL --}}
                @elseif($question->question_type === 'email')
                    <input type="email"
                           id="input_q_{{ $question->id }}"
                           name="{{ $fieldName }}"
                           value="{{ $val }}"
                           placeholder="name@example.com"
                           @if($question->is_required) required @endif>

                {{-- PHONE --}}
                @elseif($question->question_type === 'phone')
                    <input type="tel"
                           id="input_q_{{ $question->id }}"
                           name="{{ $fieldName }}"
                           value="{{ $val }}"
                           placeholder="07X XXX XXXX"
                           @if($question->is_required) required @endif>

                {{-- NUMBER --}}
                @elseif($question->question_type === 'number')
                    <input type="number"
                           id="input_q_{{ $question->id }}"
                           name="{{ $fieldName }}"
                           value="{{ $val }}"
                           placeholder="{{ $placeholder }}"
                           @if($question->setting('min_value') !== null) min="{{ $question->setting('min_value') }}" @endif
                           @if($question->setting('max_value') !== null) max="{{ $question->setting('max_value') }}" @endif
                           @if($question->is_required) required @endif>

                {{-- LONG TEXT / TEXTAREA --}}
                @elseif($question->question_type === 'textarea')
                    <textarea id="input_q_{{ $question->id }}"
                              name="{{ $fieldName }}"
                              placeholder="{{ $placeholder }}"
                              @if($question->setting('max_length')) maxlength="{{ $question->setting('max_length') }}" @endif
                              @if($question->is_required) required @endif>{{ $val }}</textarea>

                {{-- DATE --}}
                @elseif($question->question_type === 'date')
                    <input type="date"
                           id="input_q_{{ $question->id }}"
                           name="{{ $fieldName }}"
                           value="{{ $val }}"
                           @if($question->is_required) required @endif>

                {{-- TIME --}}
                @elseif($question->question_type === 'time')
                    <input type="time"
                           id="input_q_{{ $question->id }}"
                           name="{{ $fieldName }}"
                           value="{{ $val }}"
                           @if($question->is_required) required @endif>

                {{-- RADIO --}}
                @elseif($question->question_type === 'radio')
                    <div class="option-group">
                        @foreach($question->options as $opt)
                            <label class="option-label">
                                <input type="radio"
                                       name="{{ $fieldName }}"
                                       value="{{ $opt->option_value }}"
                                       @checked($val === $opt->option_value)
                                       @if($question->is_required) required @endif>
                                <span>{{ $opt->option_text }}</span>
                            </label>
                        @endforeach
                    </div>

                {{-- DROPDOWN --}}
                @elseif($question->question_type === 'select')
                    <select id="input_q_{{ $question->id }}"
                            name="{{ $fieldName }}"
                            @if($question->is_required) required @endif>
                        <option value="">-- Please Select --</option>
                        @foreach($question->options as $opt)
                            <option value="{{ $opt->option_value }}" @selected($val === $opt->option_value)>
                                {{ $opt->option_text }}
                            </option>
                        @endforeach
                    </select>

                {{-- CHECKBOX --}}
                @elseif($question->question_type === 'checkbox')
                    @php $checkedVals = is_array($val) ? $val : []; @endphp
                    <div class="option-group">
                        @foreach($question->options as $opt)
                            <label class="option-label">
                                <input type="checkbox"
                                       name="{{ $fieldName }}[]"
                                       value="{{ $opt->option_value }}"
                                       @checked(in_array($opt->option_value, $checkedVals, true))>
                                <span>{{ $opt->option_text }}</span>
                            </label>
                        @endforeach
                    </div>

                {{-- POLL --}}
                @elseif($question->question_type === 'poll')
                    <div class="option-group">
                        @foreach($question->options as $opt)
                            <label class="option-label">
                                <input type="radio"
                                       name="{{ $fieldName }}"
                                       value="{{ $opt->option_value }}"
                                       @checked($val === $opt->option_value)
                                       @if($question->is_required) required @endif>
                                <span>{{ $opt->option_text }}</span>
                            </label>
                        @endforeach
                    </div>

                {{-- YES / NO --}}
                @elseif($question->question_type === 'yes_no')
                    <div class="option-group">
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

                @error($fieldName)
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>
        @endforeach

        <div class="submit-card">
            <div class="submit-notice">
                Please verify your responses before submitting.<br>
                Thank you for completing this questionnaire.
            </div>
            <button type="submit" class="btn-submit">Submit Survey Form</button>
        </div>
    </form>

</div>

<script>
    const fieldMapping = @json($fieldMapping ?? []);
    let html5QrScanner = null;

    function setScanStatus(msg, isError = false) {
        const el = document.getElementById('scanStatus');
        if (el) {
            el.textContent = msg;
            el.style.color = isError ? '#dc2626' : '#2e7d32';
        }
    }

    // Process scanned QR payload via server endpoint
    async function processQrPayload(payload) {
        setScanStatus('Looking up child data...');
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch('{{ route("forms.qr.lookup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: payload })
            });

            const data = await res.json();
            if (data.success && data.child) {
                applyChildData(data.child);
                setScanStatus('✅ Child identified! Mapped questions filled.');
                stopHtml5Camera();
            } else {
                setScanStatus(data.message || 'Could not identify child from QR code.', true);
            }
        } catch (e) {
            setScanStatus('Error looking up QR code: ' + e.message, true);
        }
    }

    function applyChildData(child) {
        document.getElementById('hiddenChildId').value = child.id || '';
        document.getElementById('childCodeDisplay').textContent = child.child_code || 'N/A';
        document.getElementById('childNameDisplay').textContent = child.full_name || 'N/A';
        document.getElementById('childGenderDisplay').textContent = child.gender || 'N/A';
        document.getElementById('childDobDisplay').textContent = child.date_of_birth || 'N/A';
        document.getElementById('childSchoolDisplay').textContent = (child.school_name || 'N/A') + (child.grade ? ' (' + child.grade + ')' : '');
        document.getElementById('childDistrictDisplay').textContent = child.district || 'N/A';
        document.getElementById('identifiedChildBox').classList.add('active');

        // Autofill questionnaire fields based on configured field mapping
        if (Array.isArray(fieldMapping)) {
            fieldMapping.forEach(mapping => {
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
                        input.style.borderColor = '#2e7d32';
                        input.style.background = '#f2faf4';
                    }
                }
            });
        }
    }

    function lookupManualCode() {
        const code = document.getElementById('manualCodeInput').value.trim();
        if (!code) {
            alert('Please enter a Child Code.');
            return;
        }

        const payload = JSON.stringify({ kfhi: 'offline_v1', code: code, name: code });
        processQrPayload(payload);
    }

    // Camera scanner toggle using Html5Qrcode
    async function toggleHtml5Camera() {
        const videoBox = document.getElementById('videoBox');
        const btn = document.getElementById('btnToggleCamera');

        if (html5QrScanner) {
            stopHtml5Camera();
            return;
        }

        videoBox.style.display = 'block';
        btn.textContent = '⏹️ Stop Camera';

        try {
            html5QrScanner = new Html5Qrcode("qrReader");
            await html5QrScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText) => {
                    processQrPayload(decodedText);
                },
                (error) => {}
            );
            setScanStatus('Camera active. Point at child QR code.');
        } catch (err) {
            setScanStatus('Unable to open camera: ' + err, true);
            videoBox.style.display = 'none';
            btn.textContent = '📷 Open Camera Scanner';
            html5QrScanner = null;
        }
    }

    function stopHtml5Camera() {
        if (html5QrScanner) {
            html5QrScanner.stop().then(() => {
                html5QrScanner.clear();
                html5QrScanner = null;
            }).catch(() => {
                html5QrScanner = null;
            });
        }
        document.getElementById('videoBox').style.display = 'none';
        document.getElementById('btnToggleCamera').textContent = '📷 Open Camera Scanner';
    }

    // QR Image File Upload Scanner
    async function handleQrFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        setScanStatus('Scanning image file...');
        try {
            const scanner = new Html5Qrcode("qrReader");
            const decodedText = await scanner.scanFile(file, true);
            processQrPayload(decodedText);
        } catch (e) {
            setScanStatus('Could not find QR code in this image. Try another photo or enter code manually.', true);
        }
    }

    // Inline Enumerator Verification
    async function verifyInlineEnumerator() {
        const name = document.getElementById('inlineEnumName').value.trim();
        const pass = document.getElementById('inlineEnumPass').value.trim();
        const statusEl = document.getElementById('inlineVerifyStatus');

        if (!name || !pass) {
            alert('Please enter both your Full Name and the  Password.');
            return;
        }

        statusEl.style.display = 'block';
        statusEl.style.color = '#2e7d32';
        statusEl.textContent = 'Verifying access...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch('{{ route("forms.enumerator.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    enumerator_name: name,
                    access_password: pass
                })
            });

            const data = await res.json();
            if (data.success) {
                statusEl.textContent = data.message;
                document.getElementById('hiddenEnumeratorName').value = data.enumerator.name;
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                statusEl.style.color = '#dc2626';
                statusEl.textContent = data.message || 'Incorrect password.';
            }
        } catch (e) {
            statusEl.style.color = '#dc2626';
            statusEl.textContent = 'Verification error: ' + e.message;
        }
    }
</script>

</body>
</html>
