
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Follow-up Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #24352c; }
        h2 { color: #176b45; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #176b45; color: white; }
        th, td { border: 1px solid #d7e3dc; padding: 6px; text-align: left; }
        tr:nth-child(even) { background: #f2f8f4; }
    </style>
</head>
<body>
    <h2>Event Follow-up Report</h2>
    <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Event</th>
                <th>Follow-up</th>
                <th>Assigned To</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Outcome</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($followUps as $followUp)
                <tr>
                    <td>{{ $followUp->id }}</td>
                    <td>
                        {{ $followUp->event?->event_code }}
                        {{ $followUp->event?->title }}
                    </td>
                    <td>{{ $followUp->title }}</td>
                    <td>{{ $followUp->assignee?->name ?? '-' }}</td>
                    <td>{{ $followUp->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $followUp->priority }}</td>
                    <td>{{ $followUp->status }}</td>
                    <td>{{ $followUp->outcome ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No event follow-up records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
