<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Child Identification | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef7f1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
            color: #26352c;
        }

        .card {
            width: 100%;
            max-width: 650px;
            background: #ffffff;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 15px 40px rgba(46, 125, 79, 0.12);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 110px;
            max-height: 70px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #2e7d4f;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .header p {
            color: #718078;
            font-size: 14px;
        }

        .verified {
            background: #e8f6ed;
            color: #2e7d4f;
            border: 1px solid #c9e5d3;
            padding: 12px 16px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .child-section {
            display: flex;
            gap: 25px;
            align-items: center;
            padding: 20px;
            background: #f7fbf8;
            border-radius: 18px;
            margin-bottom: 25px;
        }

        .photo {
            width: 120px;
            height: 120px;
            border-radius: 18px;
            object-fit: cover;
            border: 3px solid #d6eadc;
            flex-shrink: 0;
        }

        .initial {
            width: 120px;
            height: 120px;
            border-radius: 18px;
            background: #dff0e5;
            color: #2e7d4f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .child-name {
            font-size: 24px;
            font-weight: 700;
            color: #234b32;
            margin-bottom: 8px;
        }

        .child-code {
            color: #718078;
            font-size: 14px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail {
            background: #f7fbf8;
            border-radius: 14px;
            padding: 15px;
        }

        .label {
            display: block;
            color: #789084;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .value {
            color: #26352c;
            font-weight: 600;
            font-size: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3eee6;
            color: #829088;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            .card {
                padding: 22px;
                border-radius: 18px;
            }

            .child-section {
                flex-direction: column;
                text-align: center;
            }

            .details {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @auth
    <style>
        body {
            display: block;
            padding: 0;
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .app-sidebar {
            width: 250px;
            flex: 0 0 250px;
            padding: 24px 16px;
            background: #ffffff;
            border-right: 1px solid #dfe9e2;
        }

        .app-sidebar h2 {
            margin: 0 0 24px;
            color: #2e7d32;
            font-size: 20px;
        }

        .sidebar-label {
            margin: 22px 10px 8px;
            color: #8a978d;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidebar-link {
            display: block;
            padding: 11px 12px;
            margin-bottom: 4px;
            border-radius: 7px;
            color: #53665a;
            font-size: 13px;
            text-decoration: none;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 700;
        }

        .app-main {
            flex: 1;
            min-width: 0;
            padding: 35px;
        }

        .app-main .card {
            max-width: 850px;
            margin: 0 auto;
        }

        .app-return {
            max-width: 850px;
            margin: 0 auto 18px;
        }

        .app-return a {
            color: #2e7d32;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 700px) {
            .app-shell {
                display: block;
            }

            .app-sidebar {
                width: 100%;
                padding: 14px;
                border-right: 0;
                border-bottom: 1px solid #dfe9e2;
            }

            .app-sidebar h2 {
                margin-bottom: 10px;
            }

            .sidebar-nav {
                display: flex;
                gap: 5px;
                overflow-x: auto;
            }

            .sidebar-label {
                display: none;
            }

            .sidebar-link {
                flex: 0 0 auto;
                white-space: nowrap;
            }

            .app-main {
                padding: 20px 14px;
            }
        }
    </style>
    @endauth
</head>

<body>
@auth
<div class="app-shell">
<aside class="app-sidebar">
    <h2>KFHI-CMS</h2>
    <nav class="sidebar-nav">
        @if(auth()->user()->isAdmin())
            <div class="sidebar-label">Main Menu</div>
            <a class="sidebar-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="sidebar-link" href="{{ route('children.index') }}">Child Management</a>
            <a class="sidebar-link" href="{{ route('guardians.index') }}">Guardians</a>
            <div class="sidebar-label">Identification</div>
            <a class="sidebar-link" href="{{ route('admin.qr.scan') }}">Online QR Scanner</a>
        @else
            <div class="sidebar-label">Main Menu</div>
            <a class="sidebar-link" href="{{ route('child_officer.dashboard') }}">Dashboard</a>
            <a class="sidebar-link" href="{{ route('child_officer.children.index') }}">Children</a>
            <a class="sidebar-link" href="{{ route('child_officer.children.create') }}">Register Child</a>
            <a class="sidebar-link" href="{{ route('child_officer.guardians.create') }}">Guardians</a>
            <div class="sidebar-label">Identification</div>
            <a class="sidebar-link" href="{{ route('child_officer.qr.scan') }}">Online QR Scanner</a>
        @endif
    </nav>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:20px;">
        @csrf
        <button type="submit" class="sidebar-link" style="width:100%;border:0;background:#fbeaea;color:#b44949;text-align:left;cursor:pointer;">Logout</button>
    </form>
</aside>
<main class="app-main">
    <div class="app-return">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.qr.scan') : route('child_officer.qr.scan') }}">← Back to QR Scanner</a>
    </div>
    <div class="card">
@else
<a href="{{ route('home') }}" style="position:absolute;top:18px;left:20px;color:#2e7d32;font-weight:700;text-decoration:none;">← Back</a>
<div class="card">
@endauth

    <div class="header">

        <img
            src="{{ asset('images/logo.png') }}"
            alt="KFHI Logo"
            class="logo"
        >

        <h1>Child Identification</h1>

        <p>KFHI Child Management System</p>

    </div>

    <div class="verified">
        ✓ QR Code Verified Successfully
    </div>

    <div class="child-section">

        @if($child->profile_photo)

            <img
                src="{{ asset('storage/' . $child->profile_photo) }}"
                alt="{{ $child->first_name }}"
                class="photo"
            >

        @else

            <div class="initial">
                {{ strtoupper(substr($child->first_name, 0, 1)) }}
            </div>

        @endif

        <div>

            <div class="child-name">
                {{ $child->first_name }}
                @if($child->middle_name)
                    {{ $child->middle_name }}
                @endif
                {{ $child->last_name }}
            </div>

            <div class="child-code">
                Child Code: {{ $child->child_code }}
            </div>

        </div>

    </div>

    <div class="details">

        <div class="detail">
            <span class="label">Date of Birth</span>
            <span class="value">
                {{ $child->date_of_birth?->format('d M Y') ?? 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">Gender</span>
            <span class="value">
                {{ ucfirst($child->gender) }}
            </span>
        </div>

        <div class="detail">
            <span class="label">School</span>
            <span class="value">
                {{ $child->school_name ?: 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">Grade</span>
            <span class="value">
                {{ $child->grade ?: 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">District</span>
            <span class="value">
                {{ $child->district ?: 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">Status</span>
            <span class="value">
                {{ ucfirst($child->status) }}
            </span>
        </div>

    </div>

    <div class="footer">
        KFHI Child Management System<br>
        Secure QR-Based Child Identification
    </div>

</div>
@auth
</main>
</div>
@endauth
</body>
</html>
