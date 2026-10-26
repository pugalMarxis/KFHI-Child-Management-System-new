
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Follow-up Report | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f8f5; color: #26382c; }
        .co-layout { display: flex; min-height: 100vh; }
        .co-main { flex: 1; min-width: 0; padding: 32px; }
        .page-heading { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 26px; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        h2 { margin: 0 0 8px; font-size: 17px; }
        .muted { color: #728075; font-size: 13px; }
        .eyebrow { color: #2e7d32; font-size: 11px; font-weight: bold; letter-spacing: 1.4px; text-transform: uppercase; margin-bottom: 9px; }
        .actions, .filter-actions { display: flex; flex-wrap: wrap; gap: 9px; }
        .btn { display: inline-block; padding: 11px 15px; border: 1px solid #2e7d32; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: #2e7d32; color: white; }
        .btn-outline { background: white; color: #2e7d32; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
        .stat, .panel { background: white; border: 1px solid #e1eae3; border-radius: 13px; padding: 21px; box-shadow: 0 3px 12px rgba(33,70,42,.035); }
        .stat-label { color: #647469; font-size: 12px; }
        .stat-number { margin-top: 13px; font-size: 29px; font-weight: 700; }
        .stat-icon { display: inline-grid; place-items: center; width: 35px; height: 35px; border-radius: 10px; background: #e8f5e9; color: #2e7d32; float: right; font-size: 17px; }
        .filters { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 14px; align-items: end; }
        .field label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 7px; }
        .field input, .field select { width: 100%; min-width: 0; padding: 11px; border: 1px solid #dce5de; border-radius: 7px; background: white; color: #26382c; font-size: 13px; }
        .filter-actions { margin-top: 16px; }
        .table-panel { margin-top: 22px; padding: 0; overflow: hidden; }
        .panel-heading { padding: 21px 22px; border-bottom: 1px solid #e8eee9; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #f7faf7; color: #647469; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        th, td { padding: 15px 18px; border-bottom: 1px solid #edf1ed; }
        td { font-size: 13px; vertical-align: middle; }
        tbody tr:hover { background: #f9fcf9; }
        .subtext { display: block; color: #849087; font-size: 11px; margin-top: 5px; }
        .badge { display: inline-block; border-radius: 20px; padding: 6px 9px; font-size: 11px; font-weight: 700; background: #edf1ee; color: #536257; }
        .status-completed { background: #e5f5e8; color: #26733a; }
        .status-pending, .status-open { background: #fff3df; color: #98620b; }
        .status-overdue { background: #fdeaea; color: #b33e3e; }
        .priority-high, .priority-urgent { color: #b33e3e; }
        .priority-medium, .priority-normal { color: #98620b; }
        .priority-low { color: #26733a; }
        .empty { text-align: center; color: #728075; padding: 38px 15px; }
        .pagination { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 18px 22px; font-size: 12px; color: #728075; }
        .pagination nav p { margin: 0; }
        .pagination nav span, .pagination nav a { display: inline-block; padding: 7px 10px; margin: 2px; border-radius: 6px; text-decoration: none; }
        .pagination nav a { color: #2e7d32; background: #edf6ee; }
        @media(max-width: 1050px) { .filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media(max-width: 650px) { .co-main { padding: 18px 12px; } h1 { font-size: 23px; } .stats { gap: 10px; } .stat { padding: 15px; } .stat-number { font-size: 24px; } .filters { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="co-layout">
    @include('components.child-officer-sidebar')

    <main class="co-main">
        <header class="page-heading">
            <div>
                <div class="eyebrow">REPORTS / EVENT MANAGEMENT</div>
                <h1>Event Follow-up Report</h1>
                <p class="muted">Monitor event-related tasks, deadlines, assigned officers and outcomes.</p>
            </div>
            <div class="actions">
                <a class="btn btn-primary" href="{{ route('child_officer.follow_up_reports.events.export.excel', request()->query()) }}">Export Excel</a>
                <a class="btn btn-outline" href="{{ route('child_officer.follow_up_reports.events.export.pdf', request()->query()) }}">Export PDF</a>
            </div>
        </header>

        <section class="stats">
            @foreach ([
                ['Total follow-ups', $total, '▤'],
                ['Open / pending', $pending, '◷'],
                ['Completed', $completed, '✓'],
                ['Overdue', $overdue, '!'],
            ] as [$label, $value, $icon])
                <article class="stat">
                    <span class="stat-icon">{{ $icon }}</span>
                    <div class="stat-label">{{ $label }}</div>
                    <div class="stat-number">{{ number_format($value) }}</div>
                </article>
            @endforeach
        </section>

        <section class="panel">
            <h2>Find event follow-ups</h2>
            <p class="muted">Search and filter event-related follow-up tasks.</p>
            <form method="GET">
                <div class="filters">
                    <div class="field">
                        <label for="search">Search</label>
                        <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Event, title, description...">
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="">All priorities</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="date_from">Due date from</label>
                        <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="field">
                        <label for="date_to">Due date to</label>
                        <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">Apply filters</button>
                    <a class="btn btn-outline" href="{{ route('child_officer.follow_up_reports.events.index') }}">Reset</a>
                </div>
            </form>
        </section>

        <section class="panel table-panel">
            <div class="panel-heading">
                <h2>Event follow-up records</h2>
                <p class="muted">Task status, deadlines and outcome details.</p>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Event</th><th>Follow-up</th><th>Assigned to</th>
                        <th>Due date</th><th>Priority</th><th>Status</th><th>Outcome</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($followUps as $followUp)
                        @php
                            $statusClass = strtolower(str_replace('_', '-', $followUp->status ?? 'pending'));
                            $priorityClass = 'priority-' . strtolower($followUp->priority ?? 'medium');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $followUp->event?->title ?? 'Event unavailable' }}</strong>
                                <span class="subtext">{{ $followUp->event?->event_code ?? '' }}</span>
                            </td>
                            <td>
                                <strong>{{ $followUp->title }}</strong>
                                <span class="subtext">{{ \Illuminate\Support\Str::limit($followUp->description ?? '', 70) }}</span>
                            </td>
                            <td>{{ $followUp->assignee?->name ?? 'Unassigned' }}</td>
                            <td>{{ $followUp->due_date?->format('Y-m-d') ?? '—' }}</td>
                            <td><span class="{{ $priorityClass }}">{{ ucfirst($followUp->priority ?? 'medium') }}</span></td>
                            <td><span class="badge status-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $followUp->status ?? 'pending')) }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($followUp->outcome ?? '—', 45) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">No event follow-up records match your filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <span>Showing {{ $followUps->firstItem() ?? 0 }}–{{ $followUps->lastItem() ?? 0 }} of {{ number_format($followUps->total()) }} records</span>
                <div>{{ $followUps->links() }}</div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
