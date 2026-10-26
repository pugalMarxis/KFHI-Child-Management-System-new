
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Child Follow-up Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #24352c; }
        h2 { color: #176b45; margin-bottom: 5px; }
        p { color: #66756d; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #176b45; color: white; }
        th, td { border: 1px solid #d7e3dc; padding: 6px; text-align: left; }
        tr:nth-child(even) { background: #f2f8f4; }
    </style>
</head>
<body>
    <h2>Child Follow-up Report</h2>
    <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Child</th>
                <th>Category</th>
                <th>Follow-up Date</th>
                <th>Next Date</th>
                <th>Assigned To</th>
                <th>Priority</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($followUps as $followUp)
                <tr>
                    <td>{{ $followUp->id }}</td>
                    <td>
                        {{ $followUp->child?->child_code }} -
                        {{ $followUp->child?->first_name }}
                        {{ $followUp->child?->last_name }}
                    </td>
                    <td>{{ $followUp->category ?? '-' }}</td>
                    <td>{{ $followUp->follow_up_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $followUp->next_follow_up_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $followUp->assignedTo?->name ?? '-' }}</td>
                    <td>{{ $followUp->priority ?? '-' }}</td>
                    <td>{{ $followUp->status ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No follow-up records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
