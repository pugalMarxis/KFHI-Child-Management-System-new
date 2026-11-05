<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Enumerators - KFHI-CMS</title>

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
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-input {
            padding: 10px 14px;
            border: 1px solid #cfe0d3;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: #fbfdfb;
        }

        .filter-input:focus {
            border-color: #2e7d32;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 14px 16px;
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
            padding: 4px 10px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #fee2e2; color: #dc2626; }

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

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.45);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active { display: flex; }

        .modal-card {
            background: #ffffff;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #214b28;
            margin-bottom: 6px;
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

    <div class="header-card">
        <div>
            <h1>Survey Enumerators</h1>
            <p style="color: #65756a; font-size: 14px;">
                Manage field enumerators, assign questionnaires, and maintain personal verification PINs.
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('enumerator.dashboard') }}" target="_blank" class="btn" style="background: #eef7f1; color: #2e7d32; border: 1px solid #b8d4bd; text-decoration: none;">
                📱 Open Field Enumerator Portal ↗
            </a>
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                + Register New Enumerator
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Registered Enumerators</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">{{ $stats['active'] }}</div>
            <div class="stat-label">Active Field Enumerators</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #dc2626;">{{ $stats['inactive'] }}</div>
            <div class="stat-label">Inactive Profiles</div>
        </div>
    </div>

    <!-- Common Enumerator Access Password Card -->
    <div style="background: #ffffff; border-radius: 16px; padding: 22px 26px; margin-bottom: 25px; box-shadow: 0 4px 18px rgba(46, 125, 50, 0.08); border-left: 6px solid #2e7d32; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
        <div>
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #2e7d32; margin-bottom: 4px;">
                🔑 FIELD ENUMERATOR ACCESS
            </div>
            
            <p style="margin: 0; color: #5c7063; font-size: 13px; line-height: 1.5; max-width: 650px;">
                Field enumerators simply enter their <strong>Full Name</strong> and this <strong>Common Password</strong> to access the dashboard and survey forms. You do not need to register every enumerator in advance!
            </p>
        </div>

        <form method="POST" action="{{ auth()->user()->role === 'child_officer' ? route('child_officer.enumerators.common_password') : route('admin.enumerators.common_password') }}" style="display: flex; gap: 8px; align-items: center;">
            @csrf
            
             <input
                    type="password"
                    name="common_password"
                    placeholder="Set a new shared password"
                    required
                    autocomplete="new-password"
                    style="padding: 10px 14px; border: 1px solid #cfe0d3; border-radius: 9px; font-size: 14px; width: 220px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 16px; font-size: 13px;">
                Update Password
            </button>
        </form>
    </div>

    <div class="panel">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" class="filter-input" placeholder="Search by name, ID, phone..." value="{{ request('search') }}" style="flex: 1; min-width: 200px;">

            <select name="status" class="filter-input">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ url()->current() }}" class="btn btn-secondary">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Enumerator ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Assigned Forms</th>
                        <th>Total Submissions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enumerators as $enum)
                        <tr>
                            <td><strong style="color: #1e4b26;">{{ $enum->enumerator_code }}</strong></td>
                            <td>{{ $enum->name }}</td>
                            <td>{{ $enum->phone ?: 'N/A' }}</td>
                            <td>
                                <span style="font-weight: 700; color: #2e7d32;">
                                    {{ $enum->questionnaires->count() }} Forms
                                </span>
                            </td>
                            <td>
                                <strong>{{ $enum->responses_count }}</strong>
                            </td>
                            <td>
                                <span class="badge-status status-{{ $enum->status }}">
                                    {{ $enum->status }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn-sm btn-secondary" onclick="openEditModal({{ json_encode($enum) }}, {{ json_encode($enum->questionnaires->pluck('id')) }})">
                                    Edit / Assign
                                </button>
                                <form method="POST"
                                    action="{{ auth()->user()->role === 'child_officer'
                                        ? route('child_officer.enumerators.destroy', $enum)
                                        : route('admin.enumerators.destroy', $enum) }}"
                                    style="display: inline;"
                                    onsubmit="return confirm('Permanently delete {{ addslashes($enum->name) }}? Their submitted responses will remain, but this enumerator record will be removed.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger" style="background:#dc2626;color:white;border:0;padding:7px 12px;border-radius:6px;cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 35px; color: #728477;">
                                No enumerators found. Click "+ Register New Enumerator" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $enumerators->links() }}
        </div>
    </div>

</div>

{{-- CREATE ENUMERATOR MODAL --}}
<div class="modal" id="createModal">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 style="color: #1e4b26;">Register Field Enumerator</h3>
            <button type="button" onclick="closeCreateModal()" style="border:none; background:none; font-size:22px; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.enumerators.store') }}">
            @csrf
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" class="filter-input" style="width:100%;" required placeholder="e.g. Ruwan Perera">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="filter-input" style="width:100%;" placeholder="e.g. +94 77 123 4567">
            </div>

            <div class="form-group">
                <label>Verification PIN (4 to 6 Digits) *</label>
                <input type="text" name="pin_code" class="filter-input" style="width:100%;" required placeholder="e.g. 1234">
                <small style="color: #65756a;">Used by the enumerator to verify their individual identity after shared login.</small>
            </div>

            <div class="form-group">
                <label>Account Status</label>
                <select name="status" class="filter-input" style="width:100%;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label>Assign Published Questionnaires</label>
                <div style="max-height: 150px; overflow-y: auto; padding: 10px; border: 1px solid #dcece1; border-radius: 8px;">
                    @foreach($publishedQuestionnaires as $pq)
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 6px;">
                            <input type="checkbox" name="questionnaire_ids[]" value="{{ $pq->id }}">
                            <span>{{ $pq->title }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Enumerator</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT ENUMERATOR MODAL --}}
<div class="modal" id="editModal">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 style="color: #1e4b26;">Edit Enumerator: <span id="editEnumCode"></span></h3>
            <button type="button" onclick="closeEditModal()" style="border:none; background:none; font-size:22px; cursor:pointer;">&times;</button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" id="editName" name="name" class="filter-input" style="width:100%;" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="editPhone" name="phone" class="filter-input" style="width:100%;">
            </div>

            <div class="form-group">
                <label>Reset PIN (Leave blank to keep unchanged)</label>
                <input type="text" name="pin_code" class="filter-input" style="width:100%;" placeholder="Enter new 4-digit PIN...">
            </div>

            <div class="form-group">
                <label>Account Status</label>
                <select id="editStatus" name="status" class="filter-input" style="width:100%;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label>Assign Published Questionnaires</label>
                <div style="max-height: 150px; overflow-y: auto; padding: 10px; border: 1px solid #dcece1; border-radius: 8px;" id="editQuestionnairesContainer">
                    @foreach($publishedQuestionnaires as $pq)
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 6px;">
                            <input type="checkbox" name="questionnaire_ids[]" value="{{ $pq->id }}" class="edit-q-checkbox" id="edit_q_{{ $pq->id }}">
                            <span>{{ $pq->title }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
    }

    function openEditModal(enumerator, assignedIds) {
        document.getElementById('editEnumCode').textContent = enumerator.enumerator_code;
        document.getElementById('editName').value = enumerator.name;
        document.getElementById('editPhone').value = enumerator.phone || '';
        document.getElementById('editStatus').value = enumerator.status;
        document.getElementById('editForm').action = '/admin/enumerators/' + enumerator.id;

        document.querySelectorAll('.edit-q-checkbox').forEach(cb => {
            cb.checked = assignedIds.includes(parseInt(cb.value));
        });

        document.getElementById('editModal').classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }
</script>

</body>
</html>
