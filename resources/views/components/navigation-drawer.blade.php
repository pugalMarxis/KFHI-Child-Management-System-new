<style>
    .site-sidebar {
        position: fixed;
        z-index: 1100;
        inset: 0 auto 0 0;
        width: min(270px, 88vw);
        overflow-y: auto;
        padding: 24px 16px;
        background: #ffffff;
        border-right: 1px solid #dfe9e2;
        box-shadow: 8px 0 28px rgba(31, 41, 55, 0.14);
        transform: translateX(-105%);
        transition: transform 180ms ease;
    }

    .site-sidebar.is-open {
        transform: translateX(0);
    }

    .site-sidebar h2 {
        margin: 0 10px 28px;
        color: #2e7d32;
        font-size: 20px;
    }

    .site-sidebar .nav-title {
        margin: 22px 10px 8px;
        color: #8a978d;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .site-sidebar .nav-link,
    .site-sidebar .identification-toggle {
        display: block;
        width: 100%;
        margin-bottom: 4px;
        padding: 11px 12px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #53665a;
        font: inherit;
        font-size: 13px;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }

    .site-sidebar .nav-link:hover,
    .site-sidebar .nav-link.active,
    .site-sidebar .identification-toggle:hover {
        background: #e8f5e9;
        color: #2e7d32;
        font-weight: 700;
    }

    .site-sidebar .identification-toggle {
        display: flex;
        justify-content: space-between;
    }

    .site-sidebar .identification-toggle::after {
        content: '+';
        font-size: 16px;
    }

    .site-sidebar details[open] .identification-toggle::after {
        content: '-';
    }

    .site-sidebar .identification-links {
        margin-left: 12px;
        border-left: 1px solid #dfe9e2;
    }

    .site-sidebar .logout-button {
        background: #fbeaea;
        color: #b44949;
        cursor: pointer;
    }

    .site-sidebar-backdrop {
        position: fixed;
        z-index: 1090;
        inset: 0;
        display: none;
        border: 0;
        background: rgba(31, 41, 55, 0.35);
    }

    .site-sidebar-backdrop.is-open {
        display: block;
    }

    .site-sidebar-toggle {
        position: fixed;
        z-index: 1110;
        right: 22px;
        bottom: 22px;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 44px;
        padding: 0 16px;
        border: 0;
        border-radius: 7px;
        background: #2e7d32;
        color: #ffffff;
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 5px 16px rgba(28, 76, 39, 0.2);
        cursor: pointer;
    }

    .site-sidebar-toggle:hover {
        background: #25652a;
    }

    @media (prefers-reduced-motion: reduce) {
        .site-sidebar {
            transition: none;
        }
    }
</style>

<button type="button" class="site-sidebar-toggle" id="siteSidebarToggle" aria-expanded="false" aria-controls="siteSidebar">
    <span aria-hidden="true">&#9776;</span> Menu
</button>
<button type="button" class="site-sidebar-backdrop" id="siteSidebarBackdrop" aria-label="Close navigation" tabindex="-1"></button>

<aside class="site-sidebar" id="siteSidebar" aria-label="Main navigation" aria-hidden="true">
    <h2>KFHI-CMS</h2>

    @if(auth()->user()->isAdmin())
        <div class="nav-title">Main Menu</div>
        <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a class="nav-link" href="{{ route('users.index') }}">User Management</a>
        <a class="nav-link" href="{{ route('children.index') }}">Child Management</a>
        <a class="nav-link {{ request()->routeIs('guardians.*') ? 'active' : '' }}" href="{{ route('guardians.index') }}">Guardians</a>

        <details>
            <summary class="identification-toggle">Identification</summary>
            <div class="identification-links">
                <a class="nav-link" href="{{ route('admin.qr.scan') }}">Online QR Scanner</a>
                <a class="nav-link" href="{{ route('admin.offline_qr', ['tool' => 'generator']) }}">Offline QR Generator</a>
                <a class="nav-link" href="{{ route('admin.offline_qr', ['tool' => 'scanner']) }}">Offline QR Scanner</a>
                <a class="nav-link" href="{{ route('admin.offline_qr', ['tool' => 'log']) }}">Field Scan Log</a>
            </div>
        </details>

        <div class="nav-title">Activities</div>
        <a class="nav-link" href="{{ route('events.index') }}">Events</a>
        <a class="nav-link" href="{{ route('events.index') }}">Attendance</a>
        <a class="nav-link" href="#">Questionnaires</a>
        <a class="nav-link" href="#">Follow-ups</a>
        <div class="nav-title">System</div>
        <a class="nav-link" href="#">Reports</a>
        <a class="nav-link" href="#">Audit Logs</a>
    @else
        <div class="nav-title">Main Menu</div>
        <a class="nav-link" href="{{ route('child_officer.dashboard') }}">Dashboard</a>
        <a class="nav-link" href="{{ route('child_officer.children.index') }}">Children</a>
        <a class="nav-link" href="{{ route('child_officer.children.create') }}">Register Child</a>
        <a class="nav-link {{ request()->routeIs('child_officer.guardians.*') ? 'active' : '' }}" href="{{ route('child_officer.guardians.create') }}">Guardians</a>

        <details>
            <summary class="identification-toggle">Identification</summary>
            <div class="identification-links">
                <a class="nav-link" href="{{ route('child_officer.qr.scan') }}">Online QR Scanner</a>
                <a class="nav-link" href="#">Digital IDs</a>
            </div>
        </details>

        <div class="nav-title">Activities</div>
        <a class="nav-link" href="#">Follow-ups</a>
        <div class="nav-title">Reports</div>
        <a class="nav-link" href="#">Child Reports</a>
        <div class="nav-title">Account</div>
        <a class="nav-link" href="#">Profile</a>
    @endif

    <form method="POST" action="{{ route('logout') }}" style="margin-top:20px;">
        @csrf
        <button type="submit" class="nav-link logout-button">Logout</button>
    </form>
</aside>

<script>
    const siteSidebar = document.getElementById('siteSidebar');
    const siteSidebarToggle = document.getElementById('siteSidebarToggle');
    const siteSidebarBackdrop = document.getElementById('siteSidebarBackdrop');

    function setSiteSidebarOpen(open) {
        siteSidebar.classList.toggle('is-open', open);
        siteSidebarBackdrop.classList.toggle('is-open', open);
        siteSidebar.setAttribute('aria-hidden', String(!open));
        siteSidebarToggle.setAttribute('aria-expanded', String(open));
        siteSidebarToggle.innerHTML = open ? 'Close menu' : '<span aria-hidden="true">&#9776;</span> Menu';
    }

    siteSidebarToggle.addEventListener('click', function () {
        setSiteSidebarOpen(!siteSidebar.classList.contains('is-open'));
    });
    siteSidebarBackdrop.addEventListener('click', function () {
        setSiteSidebarOpen(false);
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') setSiteSidebarOpen(false);
    });
</script>