<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Child Details - KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f8f4;
            color: #26352c;
        }

        .page {
            max-width: 1100px;
            margin: auto;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topbar h1 {
            margin: 0;
            color: #245c3a;
            font-size: 28px;
        }

        .topbar p {
            margin: 6px 0 0;
            color: #718078;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 11px 16px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .back-btn {
            background: #e8efeb;
            color: #526158;
        }

        .edit-btn {
            background: #2e7d4f;
            color: white;
        }

        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 5px 20px rgba(39, 83, 57, 0.08);
            margin-bottom: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e5eee8;
            margin-bottom: 25px;
        }

        .profile-icon {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            background: #e4f2e8;
            color: #2e7d4f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
        }

        .profile-name h2 {
            margin: 0 0 7px;
            color: #285d3d;
            font-size: 23px;
        }

        .child-code {
            color: #6d7d73;
            font-size: 13px;
        }

        .status {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #e7f6eb;
            color: #267044;
        }

        .status-inactive {
            background: #fbeaea;
            color: #b33a3a;
        }

        .section-title {
            color: #2e7049;
            font-size: 17px;
            margin: 0 0 18px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px 30px;
        }

        .info-item {
            padding: 13px 0;
            border-bottom: 1px solid #edf2ee;
        }

        .label {
            display: block;
            font-size: 12px;
            color: #7a8981;
            margin-bottom: 5px;
        }

        .value {
            font-size: 14px;
            color: #304138;
            font-weight: 600;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .notes {
            background: #f7faf8;
            border-radius: 10px;
            padding: 15px;
            line-height: 1.6;
            font-size: 14px;
            color: #526158;
        }

        @media (max-width: 700px) {
            .page {
                padding: 18px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .actions {
                width: 100%;
            }

            .btn {
                flex: 1;
                text-align: center;
            }

            .profile-header {
                align-items: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }
        }
        .profile-icon {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background: #e4f2e8;
    color: #2e7d4f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    font-weight: bold;
}
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="page">

    <div class="topbar">

        <div>
            <h1>Child Details</h1>
            <p>View complete information about the registered child.</p>
        </div>

        <div class="actions">

            <a
                href="{{ route('children.index') }}"
                class="btn back-btn"
            >
                ← Back
            </a>

            <a
                href="{{ route('children.edit', $child) }}"
                class="btn edit-btn"
            >
                Edit Child
            </a>
            @if($child->qrCode && $child->qrCode->status === 'active')

<a
    href="{{ route('children.qr.show', $child) }}"
    style="
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:10px 16px;
        background:#e8f5ec;
        color:#2e7d4f;
        border:1px solid #cde4d4;
        border-radius:9px;
        text-decoration:none;
        font-weight:600;
        font-size:13px;
    "
>
    ▣ View QR Code
</a>

@else

<form
    method="POST"
    action="{{ route('children.qr.generate', $child) }}"
    style="display:inline;"
>
    @csrf

    <button
        type="submit"
        style="
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:10px 16px;
            background:#2e7d4f;
            color:#ffffff;
            border:none;
            border-radius:9px;
            font-weight:600;
            font-size:13px;
            cursor:pointer;
        "
    >
        ▣ Generate QR Code
    </button>
</form>

@endif

        </div>

    </div>

    @if(session('success'))
        <div style="margin:0 0 20px; padding:14px 18px; border:1px solid #b9dfc1; border-radius:8px; background:#e8f5e9; color:#1e5c2a; font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    <div class="profile-card">

        <div class="profile-header">

        <div class="profile-icon">
    @if($child->profile_photo)
        <img
            src="{{ asset('storage/' . $child->profile_photo) }}"
            alt="{{ $child->first_name }} profile photo"
            style="
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 50%;
            "
        >
    @else
        {{ strtoupper(substr($child->first_name, 0, 1)) }}
    @endif
</div>

            <div class="profile-name">

                <h2>
                    {{ $child->first_name }}
                    {{ $child->middle_name }}
                    {{ $child->last_name }}
                </h2>

                <div class="child-code">
                    Child Code: {{ $child->child_code }}
                </div>

                @if($child->status === 'active')

                    <span class="status status-active">
                        Active
                    </span>

                @else

                    <span class="status status-inactive">
                        {{ ucfirst($child->status) }}
                    </span>

                @endif

            </div>

        </div>


        <h3 class="section-title">
            Personal Information
        </h3>

        <div class="info-grid">

            <div class="info-item">

                <span class="label">
                    First Name
                </span>

                <span class="value">
                    {{ $child->first_name }}
                </span>

            </div>


            <div class="info-item">

                <span class="label">
                    Middle Name
                </span>

                <span class="value">
                    {{ $child->middle_name ?: '-' }}
                </span>

            </div>


            <div class="info-item">

                <span class="label">
                    Last Name
                </span>

                <span class="value">
                    {{ $child->last_name }}
                </span>

            </div>


            <div class="info-item">

                <span class="label">
                    Date of Birth
                </span>

                <span class="value">
                    {{ $child->date_of_birth?->format('d M Y') ?? '-' }}
                </span>

            </div>


            <div class="info-item">

                <span class="label">
                    Gender
                </span>

                <span class="value">
                    {{ ucfirst($child->gender ?? '-') }}
                </span>

            </div>


            <div class="info-item">

                <span class="label">
                    Grade
                </span>

                <span class="value">
                    {{ $child->grade ?: '-' }}
                </span>

            </div>


            <div class="info-item">

                <span class="label">
                    School
                </span>

                <span class="value">
                    {{ $child->school_name ?: '-' }}
                </span>

            </div>


            <div class="info-item">

                <span class="label">
                    District
                </span>

                <span class="value">
                    {{ $child->district ?: '-' }}
                </span>

            </div>
            <div class="info-item">

<span class="label">
    GN Division
</span>

<span class="value">
    {{ $child->gn_division ?: '-' }}
</span>

</div>

            <div class="info-item full-width">

                <span class="label">
                    Address
                </span>

                <span class="value">
                    {{ $child->address ?: '-' }}
                </span>

            </div>


            <div class="info-item full-width">

                <span class="label">
                    Notes
                </span>

                <div class="notes">
                    {{ $child->notes ?: 'No additional notes available.' }}
                </div>

            </div>

        </div>

    </div>
    {{-- Guardians --}}
<div style="
    margin-top:24px;
    background:#ffffff;
    border:1px solid #dce8df;
    border-radius:16px;
    padding:24px;
    box-shadow:0 8px 24px rgba(35,80,50,0.06);
">

    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:15px;
        margin-bottom:18px;
        flex-wrap:wrap;
    ">
        <div>
            <h2 style="
                margin:0;
                color:#294536;
                font-size:20px;
            ">
                Guardians
            </h2>

            <p style="
                margin:5px 0 0;
                color:#718078;
                font-size:13px;
            ">
                Guardians registered for this child
            </p>
        </div>

        <a
            href="{{ route('guardians.create') }}"
            style="
                padding:10px 16px;
                background:#2e7d4f;
                color:#ffffff;
                text-decoration:none;
                border-radius:9px;
                font-size:13px;
                font-weight:600;
            "
        >
            + Add Guardian
        </a>
    </div>

    @if($child->guardians->count() > 0)

        <div style="
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
            gap:16px;
        ">

            @foreach($child->guardians as $guardian)

                <div style="
                    padding:18px;
                    background:#f7fbf8;
                    border:1px solid #dce8df;
                    border-radius:12px;
                ">

                    <div style="
                        display:flex;
                        justify-content:space-between;
                        align-items:flex-start;
                        gap:10px;
                    ">

                        <div>
                            <h3 style="
                                margin:0;
                                color:#294536;
                                font-size:17px;
                            ">
                                {{ $guardian->full_name }}
                            </h3>

                            <p style="
                                margin:5px 0 0;
                                color:#718078;
                                font-size:13px;
                            ">
                                {{ $guardian->relationship }}
                            </p>
                        </div>

                        @if($guardian->is_primary)
                            <span style="
                                padding:5px 9px;
                                background:#e3f3e8;
                                color:#2e7d4f;
                                border-radius:20px;
                                font-size:11px;
                                font-weight:700;
                            ">
                                Primary
                            </span>
                        @endif

                    </div>

                    <div style="
                        margin-top:15px;
                        display:grid;
                        gap:8px;
                        font-size:13px;
                        color:#5f6f66;
                    ">

                        <div>
                            <strong>Phone:</strong>
                            {{ $guardian->phone ?: 'Not provided' }}
                        </div>

                        <div>
                            <strong>Alternate:</strong>
                            {{ $guardian->alternate_phone ?: 'Not provided' }}
                        </div>

                        <div>
                            <strong>Occupation:</strong>
                            {{ $guardian->occupation ?: 'Not provided' }}
                        </div>

                    </div>

                    <div style="
                        margin-top:15px;
                    ">
                        <a
                            href="{{ route('guardians.show', $guardian) }}"
                            style="
                                color:#2e7d4f;
                                text-decoration:none;
                                font-size:13px;
                                font-weight:600;
                            "
                        >
                            View Guardian →
                        </a>
                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div style="
            padding:25px;
            text-align:center;
            background:#f7fbf8;
            border:1px dashed #cbded1;
            border-radius:12px;
            color:#718078;
        ">
            No guardians have been registered for this child yet.
        </div>

    @endif

</div>
</div>

</body>
</html>
