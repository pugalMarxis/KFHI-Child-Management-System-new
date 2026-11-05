<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $child->first_name }} {{ $child->last_name }} - KFHI-CMS</title>

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

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #2e7d32;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Profile */

        .profile-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 30px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .profile-photo {
            width: 110px;
            height: 110px;
            border-radius: 18px;
            object-fit: cover;
            border: 3px solid #e8f5e9;
        }

        .photo-placeholder {
            width: 110px;
            height: 110px;
            border-radius: 18px;
            background: #e8f5e9;
            color: #2e7d32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: 700;
        }

        .profile-info h2 {
            font-size: 27px;
            margin-bottom: 8px;
        }

        .child-code {
            color: #2e7d32;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
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

        /* Sections */

        .section-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 25px;
        }

        .info-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
        }

        .card-title {
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 16px;
            font-weight: 700;
            color: #2e7d32;
        }

        .info-list {
            padding: 8px 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 13px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            font-size: 13px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            text-align: right;
        }

        /* Guardians */

        .guardian {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .guardian:last-child {
            border-bottom: none;
        }

        .guardian-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .guardian-info {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }

        /* QR / Digital ID */

        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 25px;
        }

        .action-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 22px;
        }

        .action-card h3 {
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .action-card p {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .action-btn {
            display: inline-block;
            background: #2e7d32;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 9px;
            font-size: 13px;
        }

        .action-btn:hover {
            background: #256b29;
        }

        .not-available {
            color: #9ca3af;
            font-size: 13px;
        }

        /* Follow-ups */

        .followup-card {
            margin-top: 25px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
        }

        .followup-item {
            padding: 17px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .followup-item:last-child {
            border-bottom: none;
        }

        .followup-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .followup-description {
            color: #6b7280;
            font-size: 13px;
        }

        /* Responsive */

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .section-grid,
            .action-grid {
                grid-template-columns: 1fr;
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

            .profile-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-row {
                flex-direction: column;
                gap: 5px;
            }

            .info-value {
                text-align: left;
            }
        }
        .success-message {
    background: #e8f5e9;
    border: 1px solid #a5d6a7;
    color: #2e7d32;
    padding: 13px 16px;
    border-radius: 10px;
    margin: 15px 0 20px;
    font-size: 14px;
    font-weight: 600;
}
.guardian-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 0 20px;
    border-bottom: 1px solid #e5e7eb;
}

.guardian-header .card-title {
    padding: 18px 0;
    border-bottom: none;
}

.primary-badge {
    display: inline-block;
    margin-left: 8px;
    padding: 4px 8px;
    border-radius: 12px;
    background: #e8f5e9;
    color: #2e7d32;
    font-size: 11px;
    font-weight: 600;
}

@media (max-width: 650px) {
    .guardian-header {
        align-items: flex-start;
        flex-direction: column;
        padding-bottom: 15px;
    }

    .guardian-header .action-btn {
        margin-bottom: 5px;
    }
}
.guardian-edit-btn {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 7px;
    background: #e8f5e9;
    color: #2e7d32;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    margin-right: 8px;
}

.guardian-edit-btn:hover {
    background: #c8e6c9;
}

.guardian-delete-btn {
    border: none;
    padding: 7px 12px;
    border-radius: 7px;
    background: #ffebee;
    color: #c62828;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.guardian-delete-btn:hover {
    background: #ffcdd2;
}
/* Additional Information */

.section-card {
    background: rgba(255, 255, 255, 0.85);
    border-radius: 20px;
    padding: 25px;
    margin-top: 25px;
    box-shadow: 0 10px 30px rgba(0, 80, 50, 0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0 0 5px;
    color: #14532d;
}

.section-header p {
    margin: 0;
    color: #64748b;
}

.information-list {
    display: grid;
    gap: 15px;
}

.information-card {
    padding: 20px;
    border-radius: 16px;
    background: #f7fcf9;
    border: 1px solid #dcefe3;
}

.information-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
}

.information-card h3 {
    margin: 8px 0 0;
    color: #166534;
}

.info-type {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    background: #dcfce7;
    color: #166534;
    font-size: 12px;
    font-weight: 700;
}

.info-date {
    color: #64748b;
    font-size: 13px;
    white-space: nowrap;
}

.info-description {
    margin: 15px 0 0;
    line-height: 1.6;
    color: #475569;
    white-space: pre-line;
}

.info-footer {
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 13px;
}

.empty-information {
    text-align: center;
    padding: 35px 20px;
    color: #64748b;
}

.empty-icon {
    font-size: 40px;
    margin-bottom: 10px;
}

.empty-information h3 {
    margin: 0 0 8px;
    color: #166534;
}

.empty-information p {
    margin-bottom: 20px;
}

@media (max-width: 700px) {
    .section-header,
    .information-top {
        flex-direction: column;
        align-items: flex-start;
    }
}
    </style>
</head>

<body>

<div class="page-container">

    @include('components.child-officer-sidebar')


    <!-- Main -->

    <main class="main-content">

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        <header class="header">

            <h1>
                Child Details
            </h1>

            <div>
                Child Officer
                <span class="officer-name">
                    {{ auth()->user()->name }}
                </span>
            </div>

        </header>


        <a href="{{ route('child_officer.children.index') }}"
           class="back-link">

            ← Back to Children

        </a>


        <!-- Profile -->

        <section class="profile-card">

            @if($child->profile_photo)

                <img
                    src="{{ asset('storage/' . $child->profile_photo) }}"
                    alt="Child Photo"
                    class="profile-photo"
                >

            @else

                <div class="photo-placeholder">
                    {{ strtoupper(substr($child->first_name, 0, 1)) }}
                </div>

            @endif


            <div class="profile-info">

                <h2>

                    {{ $child->first_name }}

                    @if($child->middle_name)
                        {{ $child->middle_name }}
                    @endif

                    {{ $child->last_name }}

                </h2>

                <div class="child-code">
                    {{ $child->child_code }}
                </div>


                <span class="status
                    {{ $child->status === 'active'
                        ? 'status-active'
                        : 'status-inactive' }}">

                    {{ ucfirst($child->status) }}

                </span>
                <div style="margin-top: 15px;">

<a
    href="{{ route('child_officer.children.edit', $child) }}"
    class="action-btn"
>
    Edit Child
</a>

</div>
            </div>

        </section>


        <!-- Information -->

        <div class="section-grid">

            <!-- Personal Information -->

            <div class="info-card">

                <div class="card-title">
                    Personal Information
                </div>

                <div class="info-list">

                    <div class="info-row">

                        <span class="info-label">
                            Date of Birth
                        </span>

                        <span class="info-value">
                            {{ $child->date_of_birth
                                ? $child->date_of_birth->format('d M Y')
                                : 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Gender
                        </span>

                        <span class="info-value">
                            {{ $child->gender ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            School
                        </span>

                        <span class="info-value">
                            {{ $child->school_name ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Grade
                        </span>

                        <span class="info-value">
                            {{ $child->grade ?? 'N/A' }}
                        </span>

                    </div>

                </div>

            </div>


            <!-- Location -->

            <div class="info-card">

                <div class="card-title">
                    Location Information
                </div>

                <div class="info-list">

                    <div class="info-row">

                        <span class="info-label">
                            District
                        </span>

                        <span class="info-value">
                            {{ $child->district ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            GN Division
                        </span>

                        <span class="info-value">
                            {{ $child->gn_division ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Address
                        </span>

                        <span class="info-value">
                            {{ $child->address ?? 'N/A' }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <section class="info-card" style="margin-top: 25px;">
            <div class="card-title">
                Additional Custom Fields
            </div>

            @if(!empty($child->custom_fields))
                <div class="info-list">
                    @foreach($child->custom_fields as $key => $value)
                        <div class="info-row">
                            <span class="info-label">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </span>
                            <span class="info-value">
                                {{ $value !== '' ? $value : 'N/A' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label">No additional custom fields recorded.</span>
                    </div>
                </div>
            @endif
        </section>

        {{-- Additional Information --}}
<div class="section-card">
    <div class="section-header">
        <div>
            <h2>Additional Information</h2>
            <p>Additional records and notes related to this child.</p>
        </div>

        <a href="{{ route('child_officer.child_information.create', $child) }}"
           class="action-btn">
            + Add Information
        </a>
    </div>

    @if($child->additionalInformation->count())
        <div class="information-list">
            @foreach($child->additionalInformation as $info)
                <div class="information-card">

                    <div class="information-top">
                        <div>
                            <span class="info-type">
                                {{ $info->information_type }}
                            </span>

                            <h3>{{ $info->title }}</h3>
                        </div>

                        @if($info->information_date)
                            <span class="info-date">
                                {{ $info->information_date->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    @if($info->description)
                        <p class="info-description">
                            {{ $info->description }}
                        </p>
                    @endif

                    @if($info->addedBy)
                        <div class="info-footer">
                            Added by:
                            <strong>{{ $info->addedBy->name }}</strong>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @else
        <div class="empty-information">
            <div class="empty-icon">📝</div>
            <h3>No Additional Information</h3>
            <p>
                No additional information has been recorded for this child yet.
            </p>

            <a href="{{ route('child_officer.child_information.create', $child) }}"
               class="action-btn">
                + Add First Information
            </a>
        </div>
    @endif
</div>
<!-- Guardians -->

<div class="info-card" style="margin-top:25px;">

<div class="guardian-header">

    <div class="card-title">
        Guardians
    </div>

    <a
        href="{{ route('child_officer.guardians.create') }}"
        class="action-btn"
    >
        + Add Guardian
    </a>

</div>

    @forelse($child->guardians as $guardian)
    <div class="guardian">

<div class="guardian-name">

    {{ $guardian->full_name }}

    @if($guardian->is_primary)
        <span class="primary-badge">
            Primary
        </span>
    @endif

</div>


<div class="guardian-info">

    Relationship:
    {{ $guardian->relationship }}

    <br>

    Phone:
    {{ $guardian->phone }}

    @if($guardian->alternate_phone)

        <br>

        Alternate Phone:
        {{ $guardian->alternate_phone }}

    @endif

    @if($guardian->occupation)

        <br>

        Occupation:
        {{ $guardian->occupation }}

    @endif

    @if($guardian->guardian_type)

        <br>

        Guardian Type:
        {{ $guardian->guardian_type }}

    @endif

    @if($guardian->address)

        <br>

        Address:
        {{ $guardian->address }}

    @endif

</div>


<!-- Guardian Actions -->

<div style="margin-top: 12px;">

    <a
        href="{{ route('child_officer.guardians.edit', $guardian) }}"
        class="guardian-edit-btn"
    >
        Edit
    </a>


    <form
        action="{{ route('child_officer.guardians.destroy', $guardian) }}"
        method="POST"
        style="display: inline;"
        onsubmit="return confirm('Are you sure you want to delete this guardian information?');"
    >

        @csrf

        @method('DELETE')

        <button
            type="submit"
            class="guardian-delete-btn"
        >
            Delete
        </button>

    </form>

</div>

</div>

    @empty

        <div class="guardian">

            <span class="not-available">
                No guardian information available.
            </span>

        </div>

    @endforelse

</div>

        <!-- QR / Digital ID -->

        <div class="action-grid">

            <div class="action-card">

                <h3>
                    Secure QR Code
                </h3>

                <p>
                    Scan a child's QR code to identify a registered child.
                </p>

                <a
                    href="{{ route('child_officer.qr.scan') }}"
                    class="action-btn"
                    style="background:#e8f5e9; color:#2e7d32; margin-left:6px; border:1px solid #c8e6c9;"
                >
                    Open QR Scanner
                </a>
            </div>


            <div class="action-card">

                <h3>
                    Digital ID
                </h3>

                <p>
                    View the digital identification information for this child.
                </p>

                @if($child->digitalId)

                    <span class="not-available">
                        Digital ID available.
                    </span>

                @else

                    <span class="not-available">
                        Digital ID not available.
                    </span>

                @endif

            </div>

        </div>


    </main>

</div>

</body>
</html>