<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Children - Child Officer | KFHI-CMS</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f8f5;
            color: #1f2937;
        }

        .page-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 25px 18px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 35px;
            padding-left: 10px;
        }

        .nav-title {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            margin: 25px 10px 10px;
            letter-spacing: 1px;
        }

        .nav-link {
            display: block;
            text-decoration: none;
            color: #4b5563;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        /* Main */
        .main-content {
            flex: 1;
            padding: 25px 35px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 25px;
            border-bottom: 1px solid #e5e7eb;
        }

        .header h1 {
            font-size: 22px;
        }

        .officer-name {
            color: #2e7d32;
            font-weight: 600;
        }

        .page-heading {
            margin-top: 30px;
        }

        .page-heading h2 {
            font-size: 27px;
            margin-bottom: 7px;
        }

        .page-heading p {
            color: #6b7280;
        }

        /* Search */
        .filter-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto auto;
            gap: 12px;
            align-items: end;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            outline: none;
            font-size: 14px;
            background: #ffffff;
        }

        .form-control:focus {
            border-color: #66bb6a;
        }

        .btn {
            padding: 11px 17px;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }

        .btn-primary {
            background: #2e7d32;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #256b29;
        }

        .btn-light {
            background: #f1f5f2;
            color: #374151;
        }

        /* Table */
        .table-box {
            margin-top: 25px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 17px;
        }

        .child-count {
            color: #6b7280;
            font-size: 13px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th,
        td {
            padding: 15px 18px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        th {
            background: #fafcfb;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        tr:hover td {
            background: #fbfefb;
        }

        .child-photo {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e8f5e9;
        }

        .photo-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #e8f5e9;
            color: #2e7d32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .child-code {
            font-weight: 600;
            color: #2e7d32;
        }

        .child-name {
            font-weight: 600;
            color: #1f2937;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-inactive {
            background: #f3f4f6;
            color: #6b7280;
        }

        .view-btn {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }

        .view-btn:hover {
            text-decoration: underline;
        }

        /* Empty */
        .empty-state {
            text-align: center;
            padding: 45px 20px;
            color: #6b7280;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .filter-form {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 650px) {
            .page-container {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 20px;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }
        }
        .delete-btn {
    border: none;
    background: none;
    color: #dc2626;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    margin-left: 12px;
    padding: 0;
}

.delete-btn:hover {
    text-decoration: underline;
}
.success-message {
    margin-top: 20px;
    padding: 13px 16px;
    border-radius: 10px;
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
    font-size: 14px;
    font-weight: 600;
}
.bulk-delete-button:disabled { opacity: .55; cursor: not-allowed; }
    </style>
</head>

<body>

<div class="page-container">

    @include('components.child-officer-sidebar')


    <!-- Main -->
    <main class="main-content">

        <header class="header">

            <h1>
                Children
            </h1>

            <div>
                Child Officer
                <span class="officer-name">
                    {{ auth()->user()->name }}
                </span>
            </div>

        </header>
        @if(session('success'))

<div class="success-message">
    {{ session('success') }}
</div>

@endif
@if($errors->any())
<div role="alert" style="margin-top:20px;padding:13px 16px;border-radius:10px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-size:14px;font-weight:600;">
    {{ $errors->first() }}
</div>
@endif

        <section class="page-heading">

            <h2>
                Registered Children
            </h2>

            <p>
                Search, filter and view registered children.
            </p>

            <a href="{{ route('admin.child-sync.index') }}" class="nav-link" style="display:inline-block;margin-top:12px;">
                Sync children for offline use
            </a>

        </section>


        <!-- Filters -->
        <div class="filter-box">

            <form method="GET"
                  action="{{ route('child_officer.children.index') }}"
                  class="filter-form">

                <div class="form-group">

                    <label for="search">
                        Search
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        placeholder="Search by ID, name or district..."
                        value="{{ request('search') }}"
                    >

                </div>


                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="form-control"
                    >

                        <option value="">
                            All Status
                        </option>

                        <option value="active"
                            {{ request('status') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="inactive"
                            {{ request('status') === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="district">
                        District
                    </label>

                    <select
                        id="district"
                        name="district"
                        class="form-control"
                    >

                        <option value="">
                            All Districts
                        </option>

                        @foreach($districts as $district)

                            <option value="{{ $district }}"
                                {{ request('district') === $district ? 'selected' : '' }}>

                                {{ $district }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <button type="submit"
                        class="btn btn-primary">
                    Search
                </button>


                <a href="{{ route('child_officer.children.index') }}"
                   class="btn btn-light">
                    Reset
                </a>

            </form>

        </div>


        <!-- Children Table -->
        <div class="table-box">

            <div class="table-header">

                <h3>
                    Children
                </h3>

                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <span class="child-count">{{ $children->count() }} children found</span>
                    @if($children->count())
                        <span>Selected: <strong id="selectedChildrenCount">0</strong></span>
                        <button type="button" class="btn bulk-delete-button" id="deleteSelectedChildren" disabled style="background:#b42318;color:white;">
                            Delete selected
                        </button>
                    @endif
                </div>

            </div>


            <div class="table-wrapper">

                @if($children->count())

                    <table>

                        <thead>

                            <tr>
                                <th><input type="checkbox" id="selectAllChildren" aria-label="Select all children shown"></th>
                                <th>Photo</th>
                                <th>Child Code</th>
                                <th>Name</th>
                                <th>District</th>
                                <th>GN Division</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                        </thead>


                        <tbody>

                            @foreach($children as $child)

                                <tr>

                                    <td><input type="checkbox" class="child-row-select" value="{{ $child->id }}" aria-label="Select {{ $child->child_code }}"></td>

                                    <td>

                                        @if($child->profile_photo)

                                            <img
                                                src="{{ asset('storage/' . $child->profile_photo) }}"
                                                alt="Child Photo"
                                                class="child-photo"
                                            >

                                        @else

                                            <div class="photo-placeholder">
                                                {{ strtoupper(substr($child->first_name, 0, 1)) }}
                                            </div>

                                        @endif

                                    </td>


                                    <td>

                                        <span class="child-code">
                                            {{ $child->child_code }}
                                        </span>

                                    </td>


                                    <td>

                                        <span class="child-name">

                                            {{ $child->first_name }}

                                            @if($child->middle_name)
                                                {{ $child->middle_name }}
                                            @endif

                                            {{ $child->last_name }}

                                        </span>

                                    </td>


                                    <td>
                                        {{ $child->district ?? 'N/A' }}
                                    </td>


                                    <td>
                                        {{ $child->gn_division ?? 'N/A' }}
                                    </td>


                                    <td>

                                        <span class="status
                                            {{ $child->status === 'active'
                                                ? 'status-active'
                                                : 'status-inactive' }}">

                                            {{ ucfirst($child->status) }}

                                        </span>

                                    </td>

                                    <td>

<a
    href="{{ route('child_officer.children.show', $child) }}"
    class="view-btn"
>
    View
</a>

</td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                @else

                    <div class="empty-state">

                        <h3>
                            No children found
                        </h3>

                        <p>
                            Try changing your search or filter.
                        </p>

                    </div>

                @endif

            </div>


        </div>

    </main>

</div>

<form id="bulkDeleteChildrenForm" method="POST" action="{{ route('child_officer.children.bulk-delete') }}" hidden>
    @csrf
    <div id="selectedChildIds"></div>
</form>

<script>
(() => {
    const selectAll = document.getElementById('selectAllChildren');
    const checkboxes = [...document.querySelectorAll('.child-row-select')];
    const deleteButton = document.getElementById('deleteSelectedChildren');
    if (!selectAll || !deleteButton) return;

    const update = () => {
        const selected = checkboxes.filter(item => item.checked);
        document.getElementById('selectedChildrenCount').textContent = String(selected.length);
        deleteButton.disabled = selected.length === 0;
        selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
    };
    selectAll.addEventListener('change', () => {
        checkboxes.forEach(item => { item.checked = selectAll.checked; });
        update();
    });
    checkboxes.forEach(item => item.addEventListener('change', update));
    deleteButton.addEventListener('click', () => {
        const ids = checkboxes.filter(item => item.checked).map(item => item.value);
        if (!ids.length || !confirm(`Delete ${ids.length} selected child record(s)? Their QR codes will be deactivated. This cannot be undone.`)) return;
        const container = document.getElementById('selectedChildIds');
        container.replaceChildren(...ids.map(id => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'child_ids[]'; input.value = id;
            return input;
        }));
        document.getElementById('bulkDeleteChildrenForm').submit();
    });
})();
</script>
</body>
</html>
