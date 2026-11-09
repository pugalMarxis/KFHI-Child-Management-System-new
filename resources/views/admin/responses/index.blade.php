<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Responses & Response Review - KFHI-CMS</title>

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
        }

        .container {
            width: min(1260px, 95%);
            margin: 0 auto;
            padding: 30px 0 60px;
        }

        .header-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 26px 30px;
            margin-bottom: 24px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            border-left: 6px solid #2e7d32;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .header-card h1 {
            color: #1e4b26;
            font-size: 26px;
            margin-bottom: 6px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 4px 18px rgba(46, 125, 50, 0.06);
            border: 1px solid #e1eee4;
        }

        .stat-num {
            font-size: 26px;
            font-weight: 800;
            color: #2e7d32;
        }

        .stat-label {
            font-size: 13px;
            color: #627467;
            margin-top: 4px;
            font-weight: 600;
        }

        .panel {
            background: #ffffff;
            border-radius: 20px;
            padding: 26px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            margin-bottom: 26px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #edf4ee;
            padding-bottom: 14px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .panel-header h2 {
            font-size: 20px;
            color: #1e4b26;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-input {
            padding: 9px 12px;
            border: 1px solid #cfe0d3;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: #fbfdfb;
        }

        .filter-input:focus { border-color: #2e7d32; }

        .filter-date-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            color: #52685a;
            font-size: 11px;
            font-weight: 700;
        }

        .skip-link {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .skip-link:focus {
            left: 16px;
            top: 16px;
            width: auto;
            height: auto;
            padding: 10px 14px;
            background: #1e4b26;
            color: #fff;
            border-radius: 8px;
            z-index: 1000;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 8px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.15s ease;
        }

        .btn:focus-visible,
        .filter-input:focus-visible,
        a:focus-visible,
        button:focus-visible,
        select:focus-visible {
            outline: 3px solid rgba(46, 125, 50, 0.35);
            outline-offset: 2px;
        }

        .btn-primary { background: #2e7d32; color: #ffffff; }
        .btn-secondary { background: #e8f3eb; color: #2e7d32; }
        .btn-danger { background: #b42318; color: #ffffff; }
        .btn:disabled { opacity: .55; cursor: not-allowed; }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            padding: 13px 14px;
            border-bottom: 1px solid #edf4ee;
            text-align: left;
        }

        th {
            background: #f2f8f4;
            color: #2e7d32;
            font-weight: 700;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-submitted { background: #fef3c7; color: #92400e; }
        .status-under_review { background: #e0f2fe; color: #0369a1; }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-merged { background: #dcfce7; color: #166534; font-weight: 800; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-returned { background: #ffedd5; color: #c2410c; }
        .status-pending { background: #f3f4f6; color: #4b5563; }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body class="admin-layout-body">
<a href="#main-content" class="skip-link">Skip to main content</a>

@if(auth()->user()->role === 'child_officer')
    @include('components.child-officer-sidebar')
@else
    @include('components.admin-sidebar')
@endif

<main id="main-content" class="container" tabindex="-1">

    <div class="header-card">
        <div>
            <h1>Form Responses & Review Management</h1>
            <p style="color: #65756a; font-size: 14px;">
                Review submitted questionnaires, verify data against child records, and monitor enumerator activity.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-success" role="alert" style="background:#fee2e2;color:#991b1b;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- SUMMARY STATS --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Submissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #d97706;">{{ $stats['awaiting_review'] }}</div>
            <div class="stat-label">Awaiting Review</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #2e7d32;">{{ $stats['approved'] }}</div>
            <div class="stat-label">Approved Submissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #166534;">{{ $stats['merged'] }}</div>
            <div class="stat-label">Merged into Child Table</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #dc2626;">{{ $stats['rejected'] }}</div>
            <div class="stat-label">Rejected Submissions</div>
        </div>
    </div>

    {{-- SUBMISSIONS LIST PANEL --}}
    <div class="panel">
        <div class="panel-header">
            <h2>Submitted Questionnaire Responses ({{ $responses->total() }})</h2>
        </div>

        <form method="GET" class="filter-bar" aria-label="Filter submitted responses">
            <input type="text" name="search" class="filter-input" placeholder="Search child, enumerator, identifier..." value="{{ request('search') }}" style="flex: 1; min-width: 180px;" aria-label="Search response records">

            <select name="questionnaire_id" class="filter-input" aria-label="Filter by questionnaire">
                <option value="">All Questionnaires</option>
                @foreach($questionnaires as $q)
                    <option value="{{ $q->id }}" @selected(request('questionnaire_id') == $q->id)>{{ $q->title }}</option>
                @endforeach
            </select>

            <select name="event_id" class="filter-input" aria-label="Filter by event">
                <option value="">All Events</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" @selected(request('event_id') == $ev->id)>{{ $ev->title }}</option>
                @endforeach
            </select>

            <select name="enumerator_id" class="filter-input" aria-label="Filter by enumerator">
                <option value="">All Enumerators</option>
                @foreach($enumerators as $en)
                    <option value="{{ $en->id }}" @selected(request('enumerator_id') == $en->id)>{{ $en->name }} ({{ $en->enumerator_code }})</option>
                @endforeach
            </select>

            <select name="review_status" class="filter-input" aria-label="Filter by review status">
                <option value="">All Review Statuses</option>
                <option value="submitted" @selected(request('review_status') === 'submitted')>Submitted (Pending)</option>
                <option value="under_review" @selected(request('review_status') === 'under_review')>Under Review</option>
                <option value="approved" @selected(request('review_status') === 'approved')>Approved</option>
                <option value="rejected" @selected(request('review_status') === 'rejected')>Rejected</option>
                <option value="returned" @selected(request('review_status') === 'returned')>Returned for Correction</option>
            </select>

            <select name="merge_status" class="filter-input" aria-label="Filter by merge status">
                <option value="">All Merge Statuses</option>
                <option value="pending" @selected(request('merge_status') === 'pending')>Pending Merge</option>
                <option value="merged" @selected(request('merge_status') === 'merged')>Merged</option>
            </select>

            <label class="filter-date-group">
                From date
                <input type="date" name="start_date" class="filter-input" value="{{ request('start_date') }}" aria-label="Filter submissions from date">
            </label>

            <label class="filter-date-group">
                To date
                <input type="date" name="end_date" class="filter-input" value="{{ request('end_date') }}" aria-label="Filter submissions to date">
            </label>

            <button type="submit" class="btn btn-secondary" aria-label="Apply response filters">Filter</button>
            @if(request()->hasAny(['search', 'questionnaire_id', 'event_id', 'enumerator_id', 'review_status', 'merge_status', 'start_date', 'end_date']))
                <a href="{{ url()->current() }}" class="btn btn-secondary" aria-label="Clear response filters">Clear</a>
            @endif


            
            <div style="margin-left:auto; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('admin.responses.export', array_merge(request()->query(), ['export_type' => 'full'])) }}" class="btn btn-primary" aria-label="Export the current filtered response list to Excel">
                    Export current view
                </a>
                <a href="{{ route('admin.responses.export', array_merge(request()->query(), ['export_type' => 'child'])) }}" class="btn btn-secondary" aria-label="Export only child details for the current filtered response list">
                    Export child data only
                </a>
                <button type="button" id="exportSelectedResponses" class="btn btn-secondary" disabled>
                    Export selected (<span id="selectedResponseCount">0</span>)
                </button>
                <button type="button" id="deleteSelectedResponses" class="btn btn-danger" disabled>
                    Delete selected (<span id="deleteResponseCount">0</span>)
                </button>
            </div>
        </form>

        <form id="bulkDeleteResponsesForm" method="POST" action="{{ route('admin.responses.bulk-delete') }}" hidden>
            @csrf
            <div id="bulkDeleteResponseIds"></div>
        </form>

        <p style="margin:0 0 10px; color:#627467; font-size:12px;">
            Tick rows one at a time, or use the checkbox at the top of the table to select every response on this page.
        </p>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllVisibleResponses" aria-label="Select all responses shown on this page"></th>
                        <th>ID</th>
                        <th>Questionnaire</th>
                        <th>Event</th>
                        <th>Child Code</th>
                        <th>Enumerator</th>
                        <th>Submission Time</th>
                        <th>Review Status</th>
                        <th>Merge Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $resp)
                        <tr>
                            <td><input type="checkbox" class="response-row-select" value="{{ $resp->id }}" aria-label="Select response {{ $resp->id }}"></td>
                            <td>#{{ $resp->id }}</td>
                            <td>
                                <strong>{{ $resp->questionnaire?->title ?? 'N/A' }}</strong>
                            </td>
                            <td>{{ $resp->questionnaire?->event?->title ?? 'General' }}</td>
                            <td>
                                @if($resp->child)
                                    <strong style="color: #1e4b26;">{{ $resp->child->child_code }}</strong>
                                    <div style="font-size: 11px; color: #627467;">{{ $resp->child->full_name }}</div>
                                @elseif(!empty($resp->child_snapshot['child_code']))
                                    <strong>{{ $resp->child_snapshot['child_code'] }}</strong>
                                @else
                                    <span style="color: #9ca3af;">N/A (Public)</span>
                                @endif
                            </td>
                            <td>
                                @if($resp->enumerator)
                                    <div>{{ $resp->enumerator->name }}</div>
                                    <small style="color: #627467;">{{ $resp->enumerator->enumerator_code }}</small>
                                @else
                                    <span style="color: #64748b;">{{ $resp->enumerator_name ?? 'Public Respondent' }}</span>
                                @endif
                            </td>
                            <td>{{ $resp->submitted_at?->format('d M Y, h:i A') }}</td>
                            <td>
                                <span class="badge-status status-{{ $resp->review_status }}">
                                    {{ ucfirst(str_replace('_', ' ', $resp->review_status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status status-{{ $resp->merge_status }}">
                                    {{ ucfirst($resp->merge_status) }}
                                </span>
                            </td>
                            <td>
                                 <a href="{{ route('admin.responses.show', $resp) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                    🔍 Review & Merge
                                </a>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 35px; color: #728477;">
                                No responses match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $responses->links() }}
        </div>
    </div>

    {{-- ENUMERATOR PRODUCTIVITY & ACTIVITY MONITORING --}}
    <div class="panel">
        <div class="panel-header">
            <h2>📊 Enumerator Activity & Productivity Monitoring</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" id="exportSelectedEnumerators" class="btn btn-secondary" disabled>
                    Export selected (<span id="selectedEnumeratorCount">0</span>)
                </button>
                <button type="button" id="deleteSelectedEnumerators" class="btn btn-danger" disabled>
                    Delete selected (<span id="deleteEnumeratorCount">0</span>)
                </button>
            </div>
        </div>

        <form id="bulkDeleteEnumeratorsForm" method="POST" action="{{ route('admin.enumerators.bulk-delete') }}" hidden>
            @csrf
            <div id="bulkDeleteEnumeratorIds"></div>
        </form>

        <p style="margin:0 0 10px; color:#627467; font-size:12px;">
            Tick individual enumerators, or use the table’s top checkbox to select everyone shown.
        </p>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllVisibleEnumerators" aria-label="Select all enumerators shown in this table"></th>
                        <th>Enumerator ID</th>
                        <th>Enumerator Name</th>
                        <th>Assigned Forms</th>
                        <th>Total Submissions</th>
                        <th>Today's Submissions</th>
                        <th>Pending Review</th>
                        <th>Approved Submissions</th>
                        <th>First Activity</th>
                        <th>Latest Activity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enumeratorStats as $es)
                        <tr>
                            <td><input type="checkbox" class="enumerator-row-select" value="{{ $es->id }}" aria-label="Select enumerator {{ $es->name }}"></td>
                            <td><strong>{{ $es->enumerator_code }}</strong></td>
                            <td>{{ $es->name }}</td>
                            <td>{{ $es->questionnaires_count ?? $es->questionnaires->count() }}</td>
                            <td><strong style="color: #1e4b26;">{{ $es->total_submissions }}</strong></td>
                            <td>{{ $es->today_submissions }}</td>
                            <td style="color: #d97706; font-weight: 700;">{{ $es->pending_submissions }}</td>
                            <td style="color: #16a34a; font-weight: 700;">{{ $es->approved_submissions }}</td>
                            <td>{{ $es->first_activity ? \Carbon\Carbon::parse($es->first_activity)->format('d M Y, H:i') : 'Never' }}</td>
                            <td>{{ $es->latest_activity ? \Carbon\Carbon::parse($es->latest_activity)->format('d M Y, H:i') : 'Never' }}</td>
                            <td>
                                <button type="button" class="btn-sm btn-secondary" onclick="openEditModal({{ json_encode($es) }}, {{ json_encode($es->questionnaires->pluck('id')) }})">
                                    Edit / Assign
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 25px; color: #728477;">
                                No enumerators currently registered.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
(() => {
    const selectAll = document.getElementById('selectAllVisibleResponses');
    const rowCheckboxes = [...document.querySelectorAll('.response-row-select')];
    const exportButton = document.getElementById('exportSelectedResponses');
    const deleteButton = document.getElementById('deleteSelectedResponses');
    const selectedCount = document.getElementById('selectedResponseCount');
    const deleteCount = document.getElementById('deleteResponseCount');
    const exportUrl = @json(route('admin.responses.export'));

    function updateSelection() {
        const selected = rowCheckboxes.filter(checkbox => checkbox.checked);
        selectedCount.textContent = String(selected.length);
        exportButton.disabled = selected.length === 0;
        deleteButton.disabled = selected.length === 0;
        deleteCount.textContent = String(selected.length);
        selectAll.checked = rowCheckboxes.length > 0 && selected.length === rowCheckboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < rowCheckboxes.length;
    }

    selectAll.addEventListener('change', () => {
        rowCheckboxes.forEach(checkbox => { checkbox.checked = selectAll.checked; });
        updateSelection();
    });
    rowCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateSelection));

    exportButton.addEventListener('click', () => {
        const selectedIds = rowCheckboxes.filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
        if (!selectedIds.length) return;

        const url = new URL(exportUrl, window.location.href);
        new URLSearchParams(window.location.search).forEach((value, key) => {
            if (key !== 'page' && key !== 'response_ids[]' && key !== 'export_type') {
                url.searchParams.set(key, value);
            }
        });
        url.searchParams.set('export_type', 'full');
        selectedIds.forEach(id => url.searchParams.append('response_ids[]', id));
        window.location.assign(url.href);
    });

    deleteButton.addEventListener('click', () => {
        const selectedIds = rowCheckboxes.filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
        if (!selectedIds.length) return;
        if (!confirm(`Permanently delete ${selectedIds.length} selected response(s) and their answers? This cannot be undone. A merged response will block the whole deletion.`)) return;

        const container = document.getElementById('bulkDeleteResponseIds');
        container.replaceChildren(...selectedIds.map(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'response_ids[]';
            input.value = id;
            return input;
        }));
        document.getElementById('bulkDeleteResponsesForm').submit();
    });

    const selectAllEnumerators = document.getElementById('selectAllVisibleEnumerators');
    const enumeratorCheckboxes = [...document.querySelectorAll('.enumerator-row-select')];
    const exportEnumeratorsButton = document.getElementById('exportSelectedEnumerators');
    const deleteEnumeratorsButton = document.getElementById('deleteSelectedEnumerators');
    const selectedEnumeratorCount = document.getElementById('selectedEnumeratorCount');
    const deleteEnumeratorCount = document.getElementById('deleteEnumeratorCount');

    function updateEnumeratorSelection() {
        const selected = enumeratorCheckboxes.filter(checkbox => checkbox.checked);
        selectedEnumeratorCount.textContent = String(selected.length);
        exportEnumeratorsButton.disabled = selected.length === 0;
        deleteEnumeratorsButton.disabled = selected.length === 0;
        deleteEnumeratorCount.textContent = String(selected.length);
        selectAllEnumerators.checked = enumeratorCheckboxes.length > 0 && selected.length === enumeratorCheckboxes.length;
        selectAllEnumerators.indeterminate = selected.length > 0 && selected.length < enumeratorCheckboxes.length;
    }

    selectAllEnumerators.addEventListener('change', () => {
        enumeratorCheckboxes.forEach(checkbox => { checkbox.checked = selectAllEnumerators.checked; });
        updateEnumeratorSelection();
    });
    enumeratorCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateEnumeratorSelection));
    exportEnumeratorsButton.addEventListener('click', () => {
        const selectedIds = enumeratorCheckboxes.filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
        if (!selectedIds.length) return;

        const url = new URL(@json(route('admin.responses.export-enumerators')), window.location.href);
        selectedIds.forEach(id => url.searchParams.append('enumerator_ids[]', id));
        window.location.assign(url.href);
    });

    deleteEnumeratorsButton.addEventListener('click', () => {
        const selectedIds = enumeratorCheckboxes.filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
        if (!selectedIds.length) return;
        if (!confirm(`Permanently delete ${selectedIds.length} selected enumerator record(s)? Their submitted responses will be kept. The shared password record is protected.`)) return;

        const container = document.getElementById('bulkDeleteEnumeratorIds');
        container.replaceChildren(...selectedIds.map(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enumerator_ids[]';
            input.value = id;
            return input;
        }));
        document.getElementById('bulkDeleteEnumeratorsForm').submit();
    });
})();
</script>

</body>
</html>
