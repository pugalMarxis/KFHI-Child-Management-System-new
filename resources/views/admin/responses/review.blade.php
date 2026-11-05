<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submission #{{ $response->id }} - KFHI-CMS</title>

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
            width: min(1200px, 94%);
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
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            flex-wrap: wrap;
        }

        .header-card h1 {
            color: #1e4b26;
            font-size: 24px;
            margin-bottom: 6px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .meta-box {
            background: #f4faf6;
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #dcece1;
            font-size: 13px;
        }

        .meta-box strong {
            display: block;
            color: #2e7d32;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .badge-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-submitted { background: #fef3c7; color: #92400e; }
        .status-under_review { background: #e0f2fe; color: #0369a1; }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-merged { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-returned { background: #ffedd5; color: #c2410c; }

        .panel {
            background: #ffffff;
            border-radius: 20px;
            padding: 26px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            margin-bottom: 24px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            border-bottom: 1px solid #edf4ee;
            padding-bottom: 12px;
        }

        .panel-header h2 {
            font-size: 18px;
            color: #1e4b26;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            padding: 12px 14px;
            border-bottom: 1px solid #edf4ee;
            text-align: left;
        }

        th {
            background: #f2f8f4;
            color: #2e7d32;
            font-weight: 700;
        }

        .diff-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .diff-differs { background: #fee2e2; color: #b91c1c; }
        .diff-same { background: #f3f4f6; color: #6b7280; }

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
        .btn-danger { background: #fee2e2; color: #dc2626; }
        .btn-warning { background: #fef3c7; color: #92400e; }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .alert-error {
            background: #fdeaea;
            color: #c62828;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .q-answer-card {
            border: 1px solid #e1eee4;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            background: #fbfdfc;
        }
    </style>
</head>
<body class="admin-layout-body">

@if(auth()->user()->role === 'child_officer')
    @include('components.child-officer-sidebar')
@else
    @include('components.admin-sidebar')
@endif

<div class="container">

    <div style="margin-bottom: 16px;">
        <a href="{{ route('admin.responses.index') }}" style="color: #2e7d32; font-size: 14px; font-weight: 700; text-decoration: none;">
            ← Back to Form Responses
        </a>
    </div>

    <div class="header-card">
        <div class="top-row">
            <div>
                <h1>Submission #{{ $response->id }} Review</h1>
                <div style="color: #65756a; font-size: 14px;">
                    Questionnaire: <strong>{{ $response->questionnaire->title }}</strong>
                    @if($response->questionnaire->event) · Event: {{ $response->questionnaire->event->title }} @endif
                </div>
            </div>

            <div>
                <span class="badge-status status-{{ $response->review_status }}">
                    Review: {{ ucfirst(str_replace('_', ' ', $response->review_status)) }}
                </span>
                <span class="badge-status status-{{ $response->merge_status }}">
                    Merge: {{ ucfirst($response->merge_status) }}
                </span>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-box">
                <strong>Submitted By</strong>
                @if($response->enumerator)
                    {{ $response->enumerator->name }} ({{ $response->enumerator->enumerator_code }})
                @else
                    {{ $response->enumerator_name ?? 'Public Respondent' }}
                @endif
            </div>

            <div class="meta-box">
                <strong>Submission Timestamp</strong>
                {{ $response->submitted_at?->format('d M Y, h:i:s A') }}
            </div>

            <div class="meta-box">
                <strong>Linked Child Record</strong>
                @if($response->child)
                    <strong style="color: #1e4b26;">{{ $response->child->child_code }}</strong> ({{ $response->child->full_name }})
                @else
                    <span style="color: #718096;">None (Independent Form Submission)</span>
                @endif
            </div>

            <div class="meta-box">
                <strong>Configured Primary Identifier</strong>
                {{ $response->primary_identifier_value ?: 'None Configured' }}
            </div>
        </div>

        @if($response->review_notes)
            <div style="margin-top: 16px; padding: 12px 16px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; font-size: 13px; color: #92400e;">
                <strong>Reviewer Notes:</strong> {{ $response->review_notes }}
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    {{-- SIDE-BY-SIDE CHILD COMPARISON & MERGE SECTION --}}
    @if($response->child_id)
        <div class="panel">
            <div class="panel-header">
                <h2>🔍 Side-by-Side Comparison: Current Child Data vs Submitted Data</h2>
                <div>
                    @if($response->isMerged())
                        <span class="badge-status status-merged">✅ Merged on {{ $response->merged_at?->format('d M Y, H:i') }}</span>
                    @endif
                </div>
            </div>

            <p style="font-size: 13px; color: #627467; margin-bottom: 16px;">
                Review each mapped attribute. Submitted values do NOT modify the child table automatically. Select the fields you approve, and click <strong>"Merge Approved Fields"</strong> to commit updates safely to the child record.
            </p>

            <form method="POST" action="{{ route('admin.responses.merge', $response) }}">
                @csrf

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">Approve</th>
                                <th>Field Name</th>
                                <th>Current Child Record</th>
                                <th>Submitted Survey Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison as $fKey => $item)
                                <tr>
                                    <td>
                                        @if(!$response->isMerged())
                                            <input type="checkbox"
                                                   name="approved_fields[]"
                                                   value="{{ $fKey }}"
                                                   @checked($item['differs'])
                                                   style="width: 17px; height: 17px; accent-color: #2e7d32;">
                                        @else
                                            @if(in_array($fKey, $response->approved_fields ?? []))
                                                <span style="color: #2e7d32; font-weight: 800;">✓</span>
                                            @else
                                                <span style="color: #9ca3af;">-</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td><strong>{{ $item['label'] }}</strong></td>
                                    <td>
                                        <span style="color: #374151;">{{ $item['current'] ?: '—' }}</span>
                                    </td>
                                    <td>
                                        @if($item['differs'] && $item['submitted'] !== null)
                                            <strong style="color: #1e4b26; background: #e8f5e9; padding: 2px 6px; border-radius: 4px;">
                                                {{ $item['submitted'] }}
                                            </strong>
                                        @else
                                            <span>{{ $item['submitted'] ?: '—' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['differs'])
                                            <span class="diff-tag diff-differs">Proposed Update</span>
                                        @else
                                            <span class="diff-tag diff-same">Matches Current</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!$response->isMerged())
                    <div style="margin-top: 20px; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #214b28; margin-bottom: 6px;">Merge Notes (Audit Trail)</label>
                            <input type="text" name="notes" placeholder="Optional notes for this merge..." style="width: 100%; padding: 10px 12px; border: 1px solid #cfe0d3; border-radius: 8px; font-size: 13px;">
                        </div>

                        <button type="submit" class="btn btn-primary" onclick="return confirm('Merge selected approved fields into the authoritative child record? Original submission will be preserved permanently.')">
                            ✓ Merge Approved Fields into Child Record
                        </button>
                    </div>
                @endif
            </form>
        </div>
    @endif

    {{-- ALL SUBMITTED QUESTIONNAIRE ANSWERS --}}
    <div class="panel">
        <div class="panel-header">
            <h2>📝 Complete Survey Responses ({{ $response->answers->count() }} Questions)</h2>
        </div>

        @foreach($response->questionnaire->questions as $i => $q)
            @php
                $ans = $response->getAnswerForQuestion($q->id);
            @endphp
            <div class="q-answer-card">
                <div style="font-size: 12px; font-weight: 700; color: #2e7d32; margin-bottom: 4px;">
                    Question {{ $i + 1 }} · {{ ucfirst(str_replace('_', ' ', $q->question_type)) }}
                </div>
                <div style="font-weight: 700; color: #204928; margin-bottom: 8px;">
                    {{ $q->question_text }}
                </div>

                <div style="padding: 10px 14px; background: #ffffff; border: 1px solid #dcece1; border-radius: 8px; font-size: 14px;">
                    @if(is_array($ans))
                        <strong>Multiple selections:</strong> {{ implode(', ', $ans) }}
                    @elseif($ans !== null && $ans !== '')
                        {{ $ans }}
                    @else
                        <span style="color: #9ca3af; font-style: italic;">No answer provided (Optional)</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- REVIEW STATUS ACTION PANEL --}}
    <div class="panel">
        <div class="panel-header">
            <h2>⚖️ Review Decision & Workflow Status</h2>
        </div>

        <form method="POST" action="{{ route('admin.responses.status', $response) }}" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            @csrf

            <div style="flex: 1; min-width: 250px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #214b28; margin-bottom: 6px;">Review Status</label>
                <select name="review_status" style="width: 100%; padding: 10px 12px; border: 1px solid #cfe0d3; border-radius: 8px; font-size: 13px;">
                    <option value="under_review" @selected($response->review_status === 'under_review')>Mark Under Review</option>
                    <option value="approved" @selected($response->review_status === 'approved')>Approve Submission</option>
                    <option value="returned" @selected($response->review_status === 'returned')>Return for Correction</option>
                    <option value="rejected" @selected($response->review_status === 'rejected')>Reject Submission</option>
                </select>
            </div>

            <div style="flex: 2; min-width: 280px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #214b28; margin-bottom: 6px;">Review Decision Notes</label>
                <input type="text" name="review_notes" value="{{ old('review_notes', $response->review_notes) }}" placeholder="State reason for approval, return, or rejection..." style="width: 100%; padding: 10px 12px; border: 1px solid #cfe0d3; border-radius: 8px; font-size: 13px;">
            </div>

            <button type="submit" class="btn btn-secondary">
                Update Status
            </button>
        </form>
    </div>

</div>

</body>
</html>
