<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Management - KFHI CMS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-wrap: wrap;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f2f7f4;
            color: #26352b;
        }

        .page {
            flex: 1;
            min-width: 0;
            min-height: 100vh;
            padding: 32px;
        }

        /* ==============================
           HEADER
        ============================== */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 28px;
        }

        .header-left h1 {
            font-size: 28px;
            color: #1f5d3a;
            margin-bottom: 7px;
        }

        .header-left p {
            color: #718078;
            font-size: 14px;
        }

        .add-btn {
            text-decoration: none;
            background: #2f7d4a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(47, 125, 74, 0.18);
        }

        .add-btn:hover {
            background: #25663b;
        }

        /* ==============================
           MAIN CARD
        ============================== */

        .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(40, 80, 55, 0.07);
            border: 1px solid #e2ece5;
        }

        /* ==============================
           ALERTS
        ============================== */

        .alert {
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .success-alert {
            background: #e8f6ec;
            color: #267044;
            border: 1px solid #cce8d3;
        }

        .error-alert {
            background: #fbeaea;
            color: #b33a3a;
            border: 1px solid #f1cccc;
        }

        /* ==============================
           USER LIST
        ============================== */

        .user-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ==============================
           USER CARD
        ============================== */

        .user-card {
            display: flex;
            align-items: center;
            gap: 24px;

            padding: 22px 24px;

            background: #fbfdfc;

            border: 1px solid #e1ebe4;

            border-radius: 16px;

            transition: 0.2s ease;
        }

        .user-card:hover {
            background: #ffffff;
            border-color: #cfe1d5;
            box-shadow: 0 8px 22px rgba(40, 80, 55, 0.08);
            transform: translateY(-1px);
        }

        /* ==============================
           PROFILE
        ============================== */

        .profile-section {
            width: 105px;
            min-width: 105px;

            display: flex;
            flex-direction: column;
            align-items: center;

            text-align: center;
        }

        .profile-image,
        .profile-placeholder {
            width: 78px;
            height: 78px;

            border-radius: 50%;
        }

        .profile-image {
            object-fit: cover;
            border: 3px solid #dcebe1;
            background: #e8f4eb;
        }

        .profile-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;

            background: #e3f1e7;
            border: 3px solid #d4e7da;

            color: #2e7d4f;

            font-size: 25px;
            font-weight: 700;
        }

        /* ==============================
           MAIN USER INFORMATION
        ============================== */

        .user-main {
            flex: 1;
            min-width: 0;
        }

        .user-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .user-name {
            font-size: 20px;
            font-weight: 700;
            color: #294536;
            margin-bottom: 5px;
        }

        .system-role {
            color: #527061;
            font-size: 13px;
            font-weight: 600;
        }

        /* ==============================
           STATUS
        ============================== */

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            padding: 6px 11px;

            border-radius: 20px;

            font-size: 11px;
            font-weight: 700;

            white-space: nowrap;
        }

        .status-active {
            background: #e6f5ea;
            color: #278044;
        }

        .status-active::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #35a85a;
        }

        .status-inactive {
            background: #fbe8e8;
            color: #b33a3a;
        }

        .status-inactive::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #c84c4c;
        }

        /* ==============================
           DIVIDER
        ============================== */

        .user-divider {
            height: 1px;
            background: #e5ece7;
            margin: 15px 0;
        }

        /* ==============================
           DETAILS
        ============================== */

        .user-details {
            display: flex;
            align-items: center;
            flex-wrap: wrap;

            gap: 10px 32px;
        }

        .detail {
            display: flex;
            align-items: center;
            gap: 6px;

            font-size: 13px;
            color: #53645a;

            white-space: nowrap;
        }

        .detail-label {
            color: #829087;
            font-weight: 600;
        }

        /* ==============================
           ACTIONS
        ============================== */

        .user-bottom {
            display: flex;
            justify-content: flex-end;
            align-items: center;

            margin-top: 16px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .actions form {
            margin: 0;
        }

        .action-btn {
            text-decoration: none;

            padding: 8px 15px;

            border-radius: 8px;

            font-size: 12px;
            font-weight: 700;

            border: none;
            cursor: pointer;
        }

        .edit-btn {
            background: #eaf4ed;
            color: #2f7045;
        }

        .edit-btn:hover {
            background: #dceee1;
        }

        .delete-btn {
            background: #fbeaea;
            color: #b43c3c;
        }

        .delete-btn:hover {
            background: #f5dcdc;
        }

        /* ==============================
           EMPTY STATE
        ============================== */

        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #7b887f;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .empty strong {
            display: block;
            color: #526158;
            font-size: 16px;
            margin-bottom: 6px;
        }

        .empty p {
            font-size: 13px;
        }

        /* ==============================
           RESPONSIVE
        ============================== */

        @media (max-width: 800px) {

            .user-card {
                align-items: flex-start;
            }

            .user-top {
                flex-direction: column;
                gap: 10px;
            }

            .user-details {
                gap: 9px 20px;
            }
        }

        @media (max-width: 600px) {

            .page {
                padding: 18px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-left h1 {
                font-size: 24px;
            }

            .add-btn {
                width: 100%;
                text-align: center;
            }

            .card {
                padding: 14px;
            }

            .user-card {
                flex-direction: column;
                align-items: center;
                text-align: center;

                padding: 24px 18px;
            }

            .profile-section {
                width: 100%;
            }

            .user-main {
                width: 100%;
            }

            .user-top {
                align-items: center;
            }

            .user-details {
                justify-content: center;
                flex-direction: column;
                gap: 8px;
            }

            .user-bottom {
                justify-content: center;
            }

            .actions {
                width: 100%;
            }

            .action-btn {
                flex: 1;
                text-align: center;
            }
        }

    </style>
</head>

<body>

@include('components.admin-sidebar')

<div class="page">

    <!-- HEADER -->

    <div class="header">

        <div class="header-left">

            <h1>User Management</h1>

            <p>
                Manage KFHI CMS administrators and field members.
            </p>

        </div>

        <a
            href="{{ route('users.create') }}"
            class="add-btn"
        >
            + Add New User
        </a>

    </div>


    <!-- MAIN CARD -->

    <div class="card">

        <!-- SUCCESS MESSAGE -->

        @if(session('success'))

            <div class="alert success-alert">
                {{ session('success') }}
            </div>

        @endif


        <!-- ERROR MESSAGE -->

        @if(session('error'))

            <div class="alert error-alert">
                {{ session('error') }}
            </div>

        @endif


        <!-- USER LIST -->

        <div class="user-list">

            @forelse($users as $user)

                <div class="user-card">

                    <!-- PROFILE -->

                    <div class="profile-section">

                        @if($user->profile_photo)

                            <img
                                src="{{ asset('storage/' . $user->profile_photo) }}"
                                alt="{{ $user->name }}"
                                class="profile-image"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >

                            <div
                                class="profile-placeholder"
                                style="display:none;"
                            >
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>

                        @else

                            <div class="profile-placeholder">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>

                        @endif

                    </div>


                    <!-- MAIN USER INFORMATION -->

                    <div class="user-main">

                        <!-- NAME + STATUS -->

                        <div class="user-top">

                            <div>

                                <div class="user-name">
                                    {{ $user->name }}
                                </div>

                                <div class="system-role">
                                    System Role:
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </div>

                            </div>


                            <!-- STATUS -->

                            @if($user->status === 'active')

                                <span class="status status-active">
                                    Active
                                </span>

                            @else

                                <span class="status status-inactive">
                                    Inactive
                                </span>

                            @endif

                        </div>


                        <!-- DIVIDER -->

                        <div class="user-divider"></div>


                        <!-- DETAILS -->

                        <div class="user-details">

                            <div class="detail">

                                <span class="detail-label">
                                    Email:
                                </span>

                                <span>
                                    {{ $user->email }}
                                </span>

                            </div>


                            <div class="detail">

                                <span class="detail-label">
                                    Phone:
                                </span>

                                <span>
                                    {{ $user->phone ?? '-' }}
                                </span>

                            </div>


                            <div class="detail">

                                <span class="detail-label">
                                    Username:
                                </span>

                                <span>
                                    {{ $user->username }}
                                </span>

                            </div>

                        </div>


                        <!-- ACTIONS -->

                        <div class="user-bottom">

                            <div class="actions">

                                <a
                                    href="{{ route('users.edit', $user) }}"
                                    class="action-btn edit-btn"
                                >
                                    Edit
                                </a>


                                <form
                                    action="{{ route('users.destroy', $user) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this user?');"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="action-btn delete-btn"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty">

                    <div class="empty-icon">
                        👥
                    </div>

                    <strong>
                        No users found
                    </strong>

                    <p>
                        There are currently no users in the system.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

</div>

</body>
</html>