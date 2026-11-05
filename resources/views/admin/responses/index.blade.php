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

            <button type="submit" class="btn btn-secondary" aria-label="Apply response filters">Filter</button>
            @if(request()->hasAny(['search', 'questionnaire_id', 'event_id', 'enumerator_id', 'review_status', 'merge_status']))
                <a href="{{ url()->current() }}" class="btn btn-secondary" aria-label="Clear response filters">Clear</a>
            @endif


            
            <div style="margin-left:auto; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('admin.responses.export', array_merge(request()->query(), ['export_type' => 'full'])) }}" class="btn btn-primary" aria-label="Export the current filtered response list to Excel">
                    Export current view
                </a>
                <a href="{{ route('admin.responses.export', array_merge(request()->query(), ['export_type' => 'child'])) }}" class="btn btn-secondary" aria-label="Export only child details for the current filtered response list">
                    Export child data only
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
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

                                <form method="POST"
                                    action="{{ auth()->user()->role === 'child_officer'
                                        ? route('child_officer.responses.destroy', $resp)
                                        : route('admin.responses.destroy', $resp) }}"
                                    style="display: inline;"
                                    onsubmit="return confirm('Permanently delete response #{{ $resp->id }} and all its answers? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 35px; color: #728477;">
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
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
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
                            <td>
                                <form method="POST"
                                    action="{{ auth()->user()->role === 'child_officer'
                                        ? route('child_officer.enumerators.destroy', $es)
                                        : route('admin.enumerators.destroy', $es) }}"
                                    onsubmit="return confirm('Permanently delete {{ addslashes($es->name) }}? Their submitted responses will remain, but this enumerator record will be removed.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 25px; color: #728477;">
                                No enumerators currently registered.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</main>

</body>
</html>
