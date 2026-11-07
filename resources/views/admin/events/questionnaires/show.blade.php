<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $questionnaire->title }} – KFHI</title>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f0f4f1;
            color: #1e3a26;
            min-height: 100vh;
            line-height: 1.5;
        }

        /* ── Layout ── */
        .page-wrap {
            flex: 1 1 0;
            min-width: 0;
            width: auto;
            max-width: 1200px;
            margin: 0 auto;
            padding: 28px clamp(18px, 3vw, 40px) 64px;
        }

        .skip-link {
            position: fixed;
            z-index: 1100;
            top: 12px;
            left: 12px;
            padding: 10px 14px;
            transform: translateY(-160%);
            border-radius: 6px;
            background: #173d22;
            color: #fff;
            font-weight: 700;
        }
        .skip-link:focus { transform: translateY(0); }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* ── Alerts ── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 10px;
            padding: 13px 16px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .alert-success { background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
        .alert-error   { background: #fdeaea; color: #b71c1c; border: 1px solid #f5c2c2; }

        /* ── Header card ── */
        .hdr {
            background: #fff;
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 18px;
            box-shadow: 0 4px 18px rgba(46,125,50,.08);
            border-top: 5px solid #2e7d32;
        }

        .hdr-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }

        .hdr h1 {
            font-size: 24px;
            font-weight: 800;
            color: #1a3d22;
            margin-bottom: 4px;
        }

        .hdr-sub {
            font-size: 13px;
            color: #5e7565;
        }

        .hdr-desc {
            margin-top: 8px;
            font-size: 14px;
            color: #4a5e50;
        }

        /* ── Status badge ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
        }
        .status-draft     { background: #fff3e0; color: #bf360c; }
        .status-published { background: #e8f5e9; color: #1b5e20; }
        .status-closed    { background: #eceff1; color: #455a64; }

        /* ── Stats row ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .stat-box {
            background: #f4fbf6;
            border: 1px solid #d8eddf;
            border-radius: 12px;
            padding: 14px 16px;
            text-align: center;
        }

        .stat-num {
            font-size: 26px;
            font-weight: 800;
            color: #2e7d32;
            display: block;
        }
        .stat-num.orange { color: #e65100; }
        .stat-num.blue   { color: #1565c0; }

        .stat-lbl {
            font-size: 11px;
            font-weight: 700;
            color: #5e7565;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── Share strip ── */
        .share-strip {
            display: grid;
            grid-template-columns: minmax(150px, .7fr) minmax(240px, 2fr) auto;
            align-items: end;
            gap: 12px;
            background: #f3f8f4;
            border: 1px solid #c9ddcd;
            border-radius: 10px;
            padding: 16px;
            margin-top: 18px;
        }

        .share-heading h2 {
            font-size: 15px;
            line-height: 1.3;
            color: #1e4b26;
        }
        .share-heading p {
            margin-top: 4px;
            color: #53665a;
            font-size: 13px;
        }

        .share-field label {
            display: block;
            margin-bottom: 5px;
            color: #344b3b;
            font-size: 12px;
            font-weight: 700;
        }
        .share-input {
            width: 100%;
            min-width: 0;
            min-height: 42px;
            padding: 9px 11px;
            border: 1px solid #9bb9a2;
            border-radius: 6px;
            background: #fff;
            font-size: 14px;
            color: #203d28;
            font-family: monospace;
        }

        /* ── Toolbar ── */
        .toolbar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 42px;
            border: none;
            border-radius: 9px;
            padding: 9px 17px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: filter .15s, background .15s;
        }
        .btn:hover { filter: brightness(.93); }
        .btn:focus-visible { outline: 3px solid #66bb6a; outline-offset: 2px; }
        button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible,
        textarea:focus-visible, summary:focus-visible {
            outline: 3px solid #155e2b;
            outline-offset: 3px;
        }

        .btn-primary  { background: #2e7d32; color: #fff; }
        .btn-ghost    { background: #e6f2e8; color: #2e7d32; }
        .btn-excel    { background: #107c41; color: #fff; }
        .btn-copy     { background: #fff; color: #2e7d32; border: 1.5px solid #b2dfdb; }
        .btn-danger   { background: #fdeaea; color: #c62828; }

        /* ── Config accordion ── */
        .accordion {
            background: #fff;
            border: 1px solid #d6eadc;
            border-radius: 14px;
            margin-bottom: 18px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(46,125,50,.05);
        }

        .accordion summary {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 16px 22px;
            font-size: 15px;
            font-weight: 700;
            color: #1e4b26;
            cursor: pointer;
            user-select: none;
        }
        .accordion summary::-webkit-details-marker { display: none; }
        .accordion summary::before {
            content: '▸';
            font-size: 13px;
            color: #2e7d32;
            transition: transform .2s;
        }
        .accordion[open] summary::before { transform: rotate(90deg); }

        .accordion-body {
            padding: 0 22px 22px;
            border-top: 1px solid #edf4ee;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e4b26;
            margin: 16px 0 8px;
        }

        .section-hint {
            font-size: 12px;
            color: #637869;
            margin-bottom: 12px;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 8px;
            margin-bottom: 14px;
        }

        .checkbox-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            padding: 8px 12px;
            background: #f4fbf5;
            border-radius: 8px;
            border: 1px solid #d5e9dc;
            cursor: pointer;
        }
        .checkbox-pill input { accent-color: #2e7d32; }

        .form-field { margin-bottom: 16px; }
        .form-field label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #2e5e33;
            margin-bottom: 5px;
        }
        .form-field select, .form-field input[type="text"] {
            padding: 8px 11px;
            border: 1px solid #cde0d3;
            border-radius: 8px;
            font-size: 13px;
            max-width: 340px;
            width: 100%;
        }

        /* ── Mapping table ── */
        .map-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 10px;
        }
        .map-table th {
            background: #f2f8f4;
            color: #2e7d32;
            font-weight: 700;
            padding: 9px 12px;
            border: 1px solid #dceede;
            text-align: left;
        }
        .map-table td {
            padding: 8px 12px;
            border: 1px solid #e8f2ea;
            vertical-align: middle;
        }
        .map-table tr:nth-child(even) td { background: #fafdfa; }
        .map-table select {
            padding: 5px 8px;
            border: 1px solid #cde0d3;
            border-radius: 6px;
            font-size: 12px;
        }
        .map-table input[type="checkbox"] { accent-color: #2e7d32; }
        .lock-hint { font-size: 11px; color: #7a8f80; display: block; margin-top: 2px; }

        /* ── Questions panel ── */
        .panel {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 18px rgba(46,125,50,.07);
        }

        .panel-hdr {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .panel-hdr h2 {
            font-size: 17px;
            font-weight: 800;
            color: #1a3d22;
        }

        /* ── Question cards ── */
        .q-card {
            border: 1px solid #daeadf;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 12px;
            background: #fafdfb;
            transition: border-color .15s, box-shadow .15s;
        }
        .q-card:hover {
            border-color: #a3cfae;
            box-shadow: 0 3px 12px rgba(46,125,50,.07);
        }

        .q-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }

        .q-num {
            font-size: 11px;
            font-weight: 700;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 2px 7px;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .q-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e3d26;
            flex: 1;
        }

        .q-badges {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
        }
        .badge-type    { background: #e8f3eb; color: #2e7d32; }
        .badge-req     { background: #fdeaea; color: #c62828; }
        .badge-id      { background: #e0f2fe; color: #0277bd; }
        .badge-map     { background: #fff8e1; color: #b45309; }

        .q-help {
            font-size: 12px;
            color: #6d8073;
            margin: 6px 0 4px;
        }

        .q-preview-box {
            margin: 10px 0 12px;
            padding: 10px 13px;
            background: #f4fbf6;
            border-radius: 9px;
            border: 1px dashed #cee8d4;
            font-size: 12px;
            color: #4e6b55;
        }

        .q-actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .btn-sm {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: filter .15s;
        }
        .btn-sm:hover { filter: brightness(.92); }
        .btn-sm:focus-visible { outline: 3px solid #155e2b; outline-offset: 3px; }

        .btn-edit { background: #eef5f0; color: #2e7d32; }
        .btn-del  { background: #fdeaea; color: #c62828; }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #7a8f80;
        }
        .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
        .empty-state h3 { font-size: 17px; font-weight: 700; color: #2e5e33; margin-bottom: 6px; }
        .empty-state p  { font-size: 13px; margin-bottom: 18px; }

        /* ── Modal ── */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: rgba(0,0,0,.45);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal.open { display: flex; }

        .modal-box {
            background: #fff;
            border-radius: 18px;
            max-width: 780px;
            width: 100%;
            max-height: 88vh;
            overflow-y: auto;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(0,0,0,.18);
        }

        .modal-hdr {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #edf4ee;
            padding-bottom: 13px;
            margin-bottom: 18px;
        }
        .modal-hdr h3 { font-size: 17px; color: #1a3d22; }

        .modal-close {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #7a8f80;
            line-height: 1;
            padding: 2px 6px;
            border-radius: 6px;
        }
        .modal-close:hover { background: #f0f4f1; }

        .preview-q {
            margin-bottom: 18px;
            padding: 14px 16px;
            border: 1px solid #daeadf;
            border-radius: 11px;
        }
        .preview-q-title {
            font-weight: 700;
            color: #1a3d22;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .preview-q-help {
            font-size: 12px;
            color: #6a7d70;
            margin-bottom: 8px;
        }

        .pf-input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #cfe0d3;
            border-radius: 8px;
            font-size: 13px;
        }

        .rating-boxes {
            display: flex;
            gap: 6px;
        }
        .rating-box {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 700;
            border-radius: 7px;
            font-size: 13px;
        }

        /* ── Save btn row ── */
        .save-row { margin-top: 18px; }

        @media (max-width: 760px) {
            .page-wrap { padding: 20px 16px 40px; }
            .hdr, .panel { padding: 20px; }
            .share-strip { grid-template-columns: 1fr; align-items: stretch; }
            .share-strip .btn { justify-content: center; }
            .map-table { min-width: 680px; }
        }

        @media (max-width: 420px) {
            .page-wrap { padding: 16px 12px 32px; }
            .hdr, .panel { padding: 16px; }
            .stats-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .stat-box { padding: 12px 8px; }
            .toolbar .btn, .toolbar form, .toolbar form .btn { flex: 1 1 100%; }
            .q-top { flex-wrap: wrap; }
            .q-title { flex-basis: calc(100% - 52px); }
            .q-badges { flex-basis: 100%; }
            .modal-box { padding: 18px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body class="admin-layout-body">

<a class="skip-link" href="#main-content">Skip to main content</a>
@include('components.admin-sidebar')

<main class="page-wrap" id="main-content" tabindex="-1">

    {{-- ── Alerts ── --}}
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" role="alert">
            <span>✕</span> {{ session('error') }}
        </div>
    @endif

    {{-- ══════════════════════════════════════════════
         HEADER CARD
    ══════════════════════════════════════════════ --}}
    <div class="hdr">

        <div class="hdr-top">
            <div>
                <h1>{{ $questionnaire->title }}</h1>
                <div class="hdr-sub">
                    Event: <strong>{{ $event->title }}</strong>
                    @if($event->event_code) &nbsp;·&nbsp; {{ $event->event_code }} @endif
                    &nbsp;·&nbsp; Created by: {{ $questionnaire->creator?->name ?? 'Admin' }}
                </div>
                @if($questionnaire->description)
                    <p class="hdr-desc">{{ $questionnaire->description }}</p>
                @endif
            </div>

            <span class="status-badge status-{{ $questionnaire->status }}" aria-label="Status: {{ $questionnaire->status }}">
                @if($questionnaire->status === 'published') ● @elseif($questionnaire->status === 'draft') ○ @else ✕ @endif
                {{ ucfirst($questionnaire->status) }}
            </span>
        </div>

        {{-- Stats row --}}
        <div class="stats-row" role="list" aria-label="Questionnaire statistics">
            <div class="stat-box" role="listitem">
                <span class="stat-num">{{ $questionnaire->questions->count() }}</span>
                <div class="stat-lbl">Questions</div>
            </div>
            <div class="stat-box" role="listitem">
                <span class="stat-num">{{ $responseStats['total'] }}</span>
                <div class="stat-lbl">Submissions</div>
            </div>
            <div class="stat-box" role="listitem">
                <span class="stat-num orange">{{ $responseStats['pending'] }}</span>
                <div class="stat-lbl">Awaiting Review</div>
            </div>
            <div class="stat-box" role="listitem">
                <span class="stat-num blue">{{ $responseStats['merged'] }}</span>
                <div class="stat-lbl">Merged</div>
            </div>
        </div>

        {{-- ── Share strip (published only) ── --}}
        @if($questionnaire->isPublished())
            <section class="share-strip" aria-labelledby="shareHeading">
                <div class="share-heading">
                    <h2 id="shareHeading">Field Enumerator Entry link</h2>
                    <p>Enumerators verify their identity, open the dashboard, then choose a questionnaire.</p>
                </div>
                <div class="share-field">
                    <label for="enumeratorEntryLinkInput">Entry page URL</label>
                <input class="share-input"
                       id="enumeratorEntryLinkInput"
                       type="url"
                       value="{{ route('enumerator.verify') }}"
                       readonly
                       aria-describedby="copyStatus">
                </div>
                <button type="button" class="btn btn-copy" onclick="copyEnumeratorEntryLink()">
                    Copy entry link
                </button>
                <span class="sr-only" id="copyStatus" role="status" aria-live="polite"></span>
            </section>
        @endif

        {{-- ── Toolbar ── --}}
        <div class="toolbar" aria-label="Questionnaire actions">
            <a href="{{ route('events.questionnaires.index', $event) }}" class="btn btn-ghost">
            Back to questionnaires
            </a>

            @if($questionnaire->isDraft())
                <form method="POST" action="{{ route('events.questionnaires.publish', [$event, $questionnaire]) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Publish this questionnaire and generate the public share link?')">
                        🚀  Publish Questionnaire
                    </button>
                </form>
            @endif

            <button type="button" class="btn btn-ghost" onclick="openPreview()">
                Preview questionnaire
            </button>

            <a href="{{ route('events.questionnaires.export.excel', [$event, $questionnaire]) }}" class="btn btn-excel">
                Export to Excel
            </a>

            <a href="{{ route('admin.responses.index', ['questionnaire_id' => $questionnaire->id]) }}" class="btn btn-ghost">
                View responses ({{ $responseStats['total'] }})
            </a>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════
         DUPLICATE PREVENTION & CHILD MAPPING ACCORDION
    ══════════════════════════════════════════════ --}}
    <details class="accordion">
        <summary>⚙️ Duplicate Prevention &amp; Child QR Autofill</summary>

        <div class="accordion-body">
            <form method="POST" action="{{ route('events.questionnaires.settings.update', [$event, $questionnaire]) }}">
                @csrf

                {{-- Primary Identifier --}}
                <p class="section-title">Primary Identifier — Duplicate Prevention</p>
                <p class="section-hint">
                    Tick the question(s) that uniquely identify a respondent (e.g. Child ID, Phone).
                    Duplicate submissions with the same identifier will be blocked.
                </p>

                <div class="checkbox-grid">
                    @foreach($questionnaire->questions as $q)
                        <label class="checkbox-pill">
                            <input type="checkbox"
                                   name="primary_question_ids[]"
                                   value="{{ $q->id }}"
                                   @checked(in_array($q->id, $questionnaire->primary_identifier_settings['question_ids'] ?? []))>
                            {{ $q->question_text }}
                            <span style="font-size:11px;color:#7a8f80;">({{ ucfirst(str_replace('_', ' ', $q->question_type)) }})</span>
                        </label>
                    @endforeach
                </div>

                <div class="form-field">
                    <label for="primary_scope">Uniqueness scope</label>
                    <select name="primary_scope" id="primary_scope">
                        <option value="questionnaire" @selected(($questionnaire->primary_identifier_settings['scope'] ?? 'questionnaire') === 'questionnaire')>
                            Within this questionnaire only
                        </option>
                        <option value="event" @selected(($questionnaire->primary_identifier_settings['scope'] ?? '') === 'event')>
                            Across all questionnaires in this event
                        </option>
                        <option value="global" @selected(($questionnaire->primary_identifier_settings['scope'] ?? '') === 'global')>
                            Across the entire KFHI system
                        </option>
                    </select>
                </div>

                <hr style="border:0;border-top:1px solid #edf4ee;margin:18px 0;">

                {{-- Child QR Mapping --}}
                <p class="section-title">Child QR Autofill Mapping</p>
                <p class="section-hint">
                    When an enumerator scans a child's QR code, mapped fields auto-populate from the child record.
                </p>

                <div style="overflow-x:auto;">
                    <table class="map-table">
                        <thead>
                            <tr>
                                <th>Question</th>
                                <th>Maps to Child Field</th>
                                <th>Autofill on Scan</th>
                                <th>Enumerator Can Edit?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $existingMappings = collect($questionnaire->child_field_mapping ?? [])->keyBy('question_id');
                            @endphp
                            @foreach($questionnaire->questions as $idx => $q)
                                @php $mapped = $existingMappings->get($q->id); @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $q->question_text }}</strong>
                                        <input type="hidden" name="mappings[{{ $idx }}][question_id]" value="{{ $q->id }}">
                                    </td>
                                    <td>
                                        <select name="mappings[{{ $idx }}][child_field]">
                                            <option value="">— None —</option>
                                            @foreach($availableChildFields as $fKey => $fLabel)
                                                <option value="{{ $fKey }}" @selected(($mapped['child_field'] ?? '') === $fKey)>
                                                    {{ $fLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="checkbox" name="mappings[{{ $idx }}][autofill]" value="1"
                                               @checked($mapped['autofill'] ?? false)>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="checkbox" name="mappings[{{ $idx }}][readonly_for_enumerator]" value="0"
                                               @checked(!($mapped['readonly_for_enumerator'] ?? true))>
                                        <span class="lock-hint">Unchecked = locked</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="save-row">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </details>


    {{-- ══════════════════════════════════════════════
         QUESTIONS PANEL
    ══════════════════════════════════════════════ --}}
    <section class="panel" aria-labelledby="questionsHeading">
        <div class="panel-hdr">
            <h2 id="questionsHeading">Questions ({{ $questionnaire->questions->count() }})</h2>
            <a href="{{ route('events.questionnaires.questions.create', [$event, $questionnaire]) }}"
               class="btn btn-primary">
                + Add Question
            </a>
        </div>

        @if($questionnaire->questions->count())
            <div id="questionsContainer">
                @foreach($questionnaire->questions as $index => $question)
                    @php
                        $mapEntry = collect($questionnaire->child_field_mapping ?? [])
                                        ->firstWhere('question_id', $question->id);
                    @endphp

                    <article class="q-card" data-id="{{ $question->id }}">
                        <div class="q-top">
                            <span class="q-num" aria-label="Question {{ $index + 1 }}">#{{ $index + 1 }}</span>
                            <span class="q-title">{{ $question->question_text }}</span>
                            <div class="q-badges">
                                <span class="badge badge-type">
                                    {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                </span>
                                @if($question->is_required)
                                    <span class="badge badge-req">Required</span>
                                @endif
                                @if(in_array($question->id, $questionnaire->primary_identifier_settings['question_ids'] ?? []))
                                    <span class="badge badge-id">🔑 Identifier</span>
                                @endif
                                @if(!empty($mapEntry['child_field']))
                                    <span class="badge badge-map">📱 {{ $mapEntry['child_field'] }}</span>
                                @endif
                            </div>
                        </div>

                        @if($question->setting('help_text'))
                            <p class="q-help">ℹ️ {{ $question->setting('help_text') }}</p>
                        @endif

                        <div class="q-preview-box" aria-label="Answer preview">
                            @if($question->isOptionType())
                                <strong>Options:</strong> {{ $question->options->pluck('option_label')->implode(' · ') ?: 'No options yet' }}
                            @elseif($question->question_type === 'yes_no')
                                🔘 Yes &nbsp;&nbsp; 🔘 No
                            @elseif($question->question_type === 'rating')
                                ⭐ 1 · 2 · 3 · 4 · 5
                            @elseif($question->question_type === 'textarea')
                                📝 Multiline text
                            @elseif($question->question_type === 'email')
                                ✉️ Email address
                            @elseif($question->question_type === 'phone')
                                📞 Phone number
                            @elseif($question->question_type === 'number')
                                🔢 Number
                                @if($question->setting('min_value') !== null || $question->setting('max_value') !== null)
                                    ({{ $question->setting('min_value') ?? '−∞' }} – {{ $question->setting('max_value') ?? '+∞' }})
                                @endif
                            @elseif($question->question_type === 'date')
                                📅 Date
                            @elseif($question->question_type === 'time')
                                🕐 Time
                            @else
                                🔤 Short text
                            @endif
                        </div>

                        <div class="q-actions">
                            <a href="{{ route('events.questionnaires.questions.edit', [$event, $questionnaire, $question]) }}"
                               class="btn-sm btn-edit"
                               aria-label="Edit question {{ $index + 1 }}">
                                ✏️ Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('events.questionnaires.questions.destroy', [$event, $questionnaire, $question]) }}"
                                  onsubmit="return confirm('Delete this question? This cannot be undone.')"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-del"
                                        aria-label="Delete question {{ $index + 1 }}">
                                    🗑 Delete
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="icon">📝</div>
                <h3>No questions yet</h3>
                <p>Add questions to build your questionnaire form.</p>
                <a href="{{ route('events.questionnaires.questions.create', [$event, $questionnaire]) }}"
                   class="btn btn-primary">
                    + Add First Question
                </a>
            </div>
        @endif
    </section>

</main>{{-- /main-content --}}


{{-- ══════════════════════════════════════════════
     PREVIEW MODAL
══════════════════════════════════════════════ --}}
<div class="modal" id="previewModal" role="dialog" aria-modal="true" aria-labelledby="previewTitle">
    <div class="modal-box">
        <div class="modal-hdr">
            <h3 id="previewTitle">👁️ Preview: {{ $questionnaire->title }}</h3>
            <button type="button" class="modal-close" onclick="closePreview()" aria-label="Close preview">&times;</button>
        </div>

        <div class="alert alert-success" style="font-size:13px;font-weight:500;margin-bottom:18px;">
            This is how the form will appear to respondents and field enumerators.
        </div>

        @foreach($questionnaire->questions as $i => $q)
            <div class="preview-q">
                <div class="preview-q-title">
                    {{ $i + 1 }}. {{ $q->question_text }}
                    @if($q->is_required) <span style="color:#c62828" aria-hidden="true">*</span> @endif
                </div>

                @if($q->setting('help_text'))
                    <div class="preview-q-help">{{ $q->setting('help_text') }}</div>
                @endif

                @if(in_array($q->question_type, ['text', 'phone', 'email']))
                    <input class="pf-input" type="{{ $q->question_type === 'email' ? 'email' : 'text' }}"
                           placeholder="{{ $q->setting('placeholder') ?: 'Your answer…' }}" disabled>

                @elseif($q->question_type === 'number')
                    <input class="pf-input" type="number" placeholder="Enter number…" disabled>

                @elseif($q->question_type === 'textarea')
                    <textarea class="pf-input" placeholder="{{ $q->setting('placeholder') ?: 'Detailed response…' }}"
                              style="min-height:70px;resize:vertical;" disabled></textarea>

                @elseif($q->question_type === 'date')
                    <input class="pf-input" type="date" disabled>

                @elseif($q->question_type === 'time')
                    <input class="pf-input" type="time" disabled>

                @elseif($q->question_type === 'select')
                    <select class="pf-input" disabled>
                        <option>— Choose —</option>
                        @foreach($q->options as $opt)
                            <option>{{ $opt->option_label }}</option>
                        @endforeach
                    </select>

                @elseif(in_array($q->question_type, ['radio', 'poll']))
                    @foreach($q->options as $opt)
                        <label style="display:flex;align-items:center;gap:8px;margin-bottom:5px;font-size:13px;">
                            <input type="radio" name="preview_{{ $q->id }}" disabled> {{ $opt->option_label }}
                        </label>
                    @endforeach

                @elseif($q->question_type === 'checkbox')
                    @foreach($q->options as $opt)
                        <label style="display:flex;align-items:center;gap:8px;margin-bottom:5px;font-size:13px;">
                            <input type="checkbox" disabled> {{ $opt->option_label }}
                        </label>
                    @endforeach

                @elseif($q->question_type === 'yes_no')
                    <label style="margin-right:16px;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                        <input type="radio" name="preview_{{ $q->id }}" disabled> Yes
                    </label>
                    <label style="font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                        <input type="radio" name="preview_{{ $q->id }}" disabled> No
                    </label>

                @elseif($q->question_type === 'rating')
                    <div class="rating-boxes" aria-label="Rating 1 to 5">
                        @for($r = 1; $r <= 5; $r++)
                            <div class="rating-box">{{ $r }}</div>
                        @endfor
                    </div>
                @endif
            </div>
        @endforeach

        <div style="text-align:right;margin-top:16px;">
            <button type="button" class="btn btn-ghost" onclick="closePreview()">Close</button>
        </div>
    </div>
</div>


<script>
    /* ── Copy link ── */
    function copyEnumeratorEntryLink() {
        const input = document.getElementById('enumeratorEntryLinkInput');
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(function() {
            showToast('Enumerator entry link copied!');
        }).catch(function() {
            input.select();
            document.execCommand('copy');
            showToast('Enumerator entry link copied!');
        });
    }

    /* ── Toast ── */
    function showToast(msg) {
        const status = document.getElementById('copyStatus');
        if (status) status.textContent = msg;
        let t = document.getElementById('_toast');
        if (!t) {
            t = document.createElement('div');
            t.id = '_toast';
            t.setAttribute('aria-hidden', 'true');
            Object.assign(t.style, {
                position: 'fixed', bottom: '28px', right: '28px',
                background: '#2e7d32', color: '#fff',
                padding: '10px 20px', borderRadius: '10px',
                fontSize: '14px', fontWeight: '700',
                boxShadow: '0 4px 16px rgba(0,0,0,.2)',
                zIndex: '9999', transition: 'opacity .3s'
            });
            document.body.appendChild(t);
        }
        t.textContent = '✓ ' + msg;
        t.style.opacity = '1';
        clearTimeout(t._timer);
        t._timer = setTimeout(function() { t.style.opacity = '0'; }, 2400);
    }

    /* ── Preview modal ── */
    let previewOpener = null;

    function openPreview() {
        previewOpener = document.activeElement;
        document.getElementById('previewModal').classList.add('open');
        document.body.style.overflow = 'hidden';
        document.querySelector('#previewModal .modal-close').focus();
    }

    function closePreview() {
        document.getElementById('previewModal').classList.remove('open');
        document.body.style.overflow = '';
        if (previewOpener) previewOpener.focus();
    }

    window.addEventListener('click', function(e) {
        const m = document.getElementById('previewModal');
        if (e.target === m) closePreview();
    });

    window.addEventListener('keydown', function(e) {
        const modal = document.getElementById('previewModal');
        if (!modal.classList.contains('open')) return;
        if (e.key === 'Escape') {
            closePreview();
            return;
        }
        if (e.key === 'Tab') {
            const focusable = modal.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
</script>

</body>
</html>