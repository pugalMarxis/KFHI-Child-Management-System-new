<style>
    body.admin-layout-body {
        display: flex;
        flex-wrap: wrap;
        min-height: 100vh;
        margin: 0;
    }

    body.admin-layout-body > .page,
    body.admin-layout-body > .container {
        flex: 1 1 calc(100% - 250px);
        min-width: 0;
    }

    .admin-app-sidebar {
        position: sticky;
        z-index: 20;
        top: 0;
        display: flex;
        flex: 0 0 250px;
        flex-direction: column;
        width: 250px;
        height: 100vh;
        overflow-y: auto;
        padding: 22px 16px;
        border-right: 1px solid #dfe9e2;
        background: #ffffff;
    }

    body.sidebar-collapsed .admin-app-sidebar {
        display: none;
    }

    .admin-app-sidebar .admin-logo {
        margin: 0 10px 24px;
        color: #2e7d32;
        font-size: 22px;
        font-weight: 700;
    }

    .admin-app-sidebar .admin-nav-title {
        margin: 20px 10px 8px;
        color: #8a978d;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .admin-app-sidebar .admin-nav-link,
    .admin-app-sidebar .admin-identification-toggle {
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

    .admin-app-sidebar .admin-nav-link:hover,
    .admin-app-sidebar .admin-nav-link.active,
    .admin-app-sidebar .admin-identification-toggle:hover,
    .admin-app-sidebar .admin-identification-toggle.active {
        background: #e8f5e9;
        color: #2e7d32;
        font-weight: 700;
    }

    .admin-app-sidebar .admin-identification-toggle {
        display: flex;
        justify-content: space-between;
        list-style: none;
    }

    .admin-app-sidebar .admin-identification-toggle::-webkit-details-marker {
        display: none;
    }

    .admin-app-sidebar .admin-identification-toggle::after {
        content: '+';
        font-size: 16px;
    }

    .admin-app-sidebar details[open] .admin-identification-toggle::after {
        content: '-';
    }

    .admin-app-sidebar .admin-identification-links {
        margin-left: 12px;
        border-left: 1px solid #dfe9e2;
    }

    .admin-app-sidebar .admin-logout {
        margin-top: auto;
        padding-top: 16px;
    }

    .admin-app-sidebar .admin-logout .admin-nav-link {
        background: #fbeaea;
        color: #b44949;
        cursor: pointer;
    }

    .admin-sidebar-toggle {
        position: fixed;
        z-index: 30;
        top: 16px;
        left: 198px;
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        padding: 0;
        border: 1px solid #dfe9e2;
        border-radius: 7px;
        background: #ffffff;
        color: #365344;
        cursor: pointer;
        transition: left 160ms ease, background 160ms ease, border-color 160ms ease;
    }

    .admin-sidebar-toggle:hover {
        border-color: #b8d4bd;
        background: #f1f8f2;
    }

    .admin-sidebar-toggle:focus-visible {
        outline: 3px solid rgba(46, 125, 50, 0.25);
        outline-offset: 2px;
    }

    .admin-sidebar-toggle-icon {
        position: relative;
        display: block;
        width: 16px;
        height: 13px;
        border: 1.5px solid currentColor;
        border-radius: 2px;
    }

    .admin-sidebar-toggle-icon::before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 4px;
        border-left: 1px solid currentColor;
        content: '';
    }

    body.sidebar-collapsed .admin-sidebar-toggle-icon::before {
        right: 4px;
        left: auto;
    }

    body.sidebar-collapsed .admin-sidebar-toggle {
        left: 14px;
    }

    @media (max-width: 650px) {
        .admin-app-sidebar {
            position: relative;
            flex-basis: 100%;
            width: 100%;
            height: auto;
            max-height: 55vh;
        }

        .admin-sidebar-toggle {
            top: 10px;
            left: auto;
            right: 12px;
        }

        body.sidebar-collapsed .admin-sidebar-toggle {
            left: auto;
        }

        body.admin-layout-body > .page,
        body.admin-layout-body > .container {
            flex-basis: 100%;
        }
    }
</style>

<button type="button" class="admin-sidebar-toggle" id="adminSidebarToggle" aria-label="Collapse navigation" title="Collapse navigation" aria-expanded="true" aria-controls="adminSidebar">
    <span class="admin-sidebar-toggle-icon" aria-hidden="true"></span>
</button>

<aside class="admin-app-sidebar" id="adminSidebar" aria-label="Administrator navigation">
    <div class="admin-logo">KFHI-CMS</div>

    <div class="admin-nav-title">Main Menu</div>
    <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a class="admin-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">User Management</a>
    <a class="admin-nav-link {{ request()->routeIs('children.*') ? 'active' : '' }}" href="{{ route('children.index') }}">Child Management</a>
    <a class="admin-nav-link {{ request()->routeIs('guardians.*') ? 'active' : '' }}" href="{{ route('guardians.index') }}">Guardians</a>

    <details {{ request()->routeIs('admin.qr.scan') ? 'open' : '' }}>
        <summary class="admin-identification-toggle {{ request()->routeIs('admin.qr.scan') ? 'active' : '' }}">Identification</summary>
        <div class="admin-identification-links">
            <a class="admin-nav-link {{ request()->routeIs('admin.qr.scan') ? 'active' : '' }}" href="{{ route('admin.qr.scan') }}">Online QR Scanner</a>
        </div>
    </details>

    <div class="admin-nav-title">Activities</div>
    <a class="admin-nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">Events</a>
    <a class="admin-nav-link {{ request()->routeIs('admin.responses.*') ? 'active' : '' }}" href="{{ route('admin.responses.index') }}">Form Responses</a>
    <a class="admin-nav-link {{ request()->routeIs('admin.enumerators.*') ? 'active' : '' }}" href="{{ route('admin.enumerators.index') }}">Enumerators</a>
    <a class="admin-nav-link {{ request()->routeIs('enumerator.*') ? 'active' : '' }}" href="{{ route('enumerator.dashboard') }}">Enumerator Portal ↗</a>
    <a class="admin-nav-link" href="{{ route('events.index') }}">Attendance</a>
    <a class="admin-nav-link" href="#">Follow-ups</a>
    <div class="admin-nav-title">System</div>
    <a class="admin-nav-link" href="#">Reports</a>
    <a class="admin-nav-link" href="#">Audit Logs</a>

    <form class="admin-logout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="admin-nav-link">Logout</button>
    </form>
</aside>

<script>
    const adminSidebar = document.getElementById('adminSidebar');
    const adminSidebarToggle = document.getElementById('adminSidebarToggle');
    const adminSidebarKey = 'kfhiSidebarCollapsed';

    function setAdminSidebarCollapsed(collapsed) {
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        const label = collapsed ? 'Expand navigation' : 'Collapse navigation';
        adminSidebarToggle.setAttribute('aria-label', label);
        adminSidebarToggle.title = label;
        adminSidebarToggle.setAttribute('aria-expanded', String(!collapsed));
        adminSidebar.setAttribute('aria-hidden', String(collapsed));
        localStorage.setItem(adminSidebarKey, String(collapsed));
    }

    setAdminSidebarCollapsed(localStorage.getItem(adminSidebarKey) === 'true');
    adminSidebarToggle.addEventListener('click', function () {
        setAdminSidebarCollapsed(!document.body.classList.contains('sidebar-collapsed'));
    });
</script>
