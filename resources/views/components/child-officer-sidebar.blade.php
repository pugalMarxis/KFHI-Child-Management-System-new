<style>
    .child-officer-sidebar {
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

    body.sidebar-collapsed .child-officer-sidebar {
        display: none;
    }

    .child-officer-sidebar .co-logo {
        margin: 0 10px 24px;
        color: #2e7d32;
        font-size: 22px;
        font-weight: 700;
    }

    .child-officer-sidebar .co-nav-title {
        margin: 20px 10px 8px;
        color: #8a978d;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .child-officer-sidebar .co-nav-link,
    .child-officer-sidebar .co-identification-toggle {
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

    .child-officer-sidebar .co-nav-link:hover,
    .child-officer-sidebar .co-nav-link.active,
    .child-officer-sidebar .co-identification-toggle:hover,
    .child-officer-sidebar .co-identification-toggle.active {
        background: #e8f5e9;
        color: #2e7d32;
        font-weight: 700;
    }

    .child-officer-sidebar .co-identification-toggle {
        display: flex;
        justify-content: space-between;
        list-style: none;
    }

    .child-officer-sidebar .co-identification-toggle::-webkit-details-marker {
        display: none;
    }

    .child-officer-sidebar .co-identification-toggle::after {
        content: '+';
        font-size: 16px;
    }

    .child-officer-sidebar details[open] .co-identification-toggle::after {
        content: '-';
    }

    .child-officer-sidebar .co-identification-links {
        margin-left: 12px;
        border-left: 1px solid #dfe9e2;
    }

    .child-officer-sidebar .co-logout {
        margin-top: auto;
        padding-top: 16px;
    }

    .child-officer-sidebar .co-logout .co-nav-link {
        background: #fbeaea;
        color: #b44949;
        cursor: pointer;
    }

    .child-officer-sidebar-toggle {
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

    .child-officer-sidebar-toggle:hover {
        border-color: #b8d4bd;
        background: #f1f8f2;
    }

    .child-officer-sidebar-toggle:focus-visible {
        outline: 3px solid rgba(46, 125, 50, 0.25);
        outline-offset: 2px;
    }

    .child-officer-sidebar-toggle-icon {
        position: relative;
        display: block;
        width: 16px;
        height: 13px;
        border: 1.5px solid currentColor;
        border-radius: 2px;
    }

    .child-officer-sidebar-toggle-icon::before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 4px;
        border-left: 1px solid currentColor;
        content: '';
    }

    body.sidebar-collapsed .child-officer-sidebar-toggle-icon::before {
        right: 4px;
        left: auto;
    }

    body.sidebar-collapsed .child-officer-sidebar-toggle {
        left: 14px;
    }

    @media (max-width: 650px) {
        .child-officer-sidebar {
            position: relative;
            flex-basis: 100%;
            width: 100%;
            height: auto;
            max-height: 55vh;
        }

        .child-officer-sidebar-toggle {
            top: 10px;
            left: auto;
            right: 12px;
        }

        body.sidebar-collapsed .child-officer-sidebar-toggle {
            left: auto;
        }
    }
</style>

<button type="button" class="child-officer-sidebar-toggle" id="childOfficerSidebarToggle" aria-label="Collapse navigation" title="Collapse navigation" aria-expanded="true" aria-controls="childOfficerSidebar">
    <span class="child-officer-sidebar-toggle-icon" aria-hidden="true"></span>
</button>

<aside class="child-officer-sidebar" id="childOfficerSidebar" aria-label="Child officer navigation">
    <div class="co-logo">KFHI-CMS</div>

    <div class="co-nav-title">Main Menu</div>
    <a class="co-nav-link {{ request()->routeIs('child_officer.dashboard') ? 'active' : '' }}" href="{{ route('child_officer.dashboard') }}">Dashboard</a>
    <div class="co-nav-title">Child Management</div>
    <a class="co-nav-link {{ request()->routeIs('child_officer.children.index', 'child_officer.children.show') ? 'active' : '' }}" href="{{ route('child_officer.children.index') }}">Children</a>
    <a class="co-nav-link {{ request()->routeIs('child_officer.children.create', 'child_officer.children.edit') ? 'active' : '' }}" href="{{ route('child_officer.children.create') }}">Register Child</a>
    <a class="co-nav-link" href="{{ route('child_officer.guardians.create') }}">Guardians</a>

    <details {{ request()->routeIs('child_officer.qr.scan', 'child_officer.offline_qr') ? 'open' : '' }}>
        <summary class="co-identification-toggle {{ request()->routeIs('child_officer.qr.scan', 'child_officer.offline_qr') ? 'active' : '' }}">Identification</summary>
        <div class="co-identification-links">
            <a class="co-nav-link {{ request()->routeIs('child_officer.qr.scan') ? 'active' : '' }}" href="{{ route('child_officer.qr.scan') }}">Online QR Scanner</a>
            <a class="co-nav-link {{ request()->routeIs('child_officer.offline_qr') && request('tool', 'generator') === 'generator' ? 'active' : '' }}" href="{{ route('child_officer.offline_qr', ['tool' => 'generator']) }}">Offline QR Generator</a>
            <a class="co-nav-link {{ request()->routeIs('child_officer.offline_qr') && request('tool') === 'scanner' ? 'active' : '' }}" href="{{ route('child_officer.offline_qr', ['tool' => 'scanner']) }}">Offline QR Scanner</a>
            <a class="co-nav-link {{ request()->routeIs('child_officer.offline_qr') && request('tool') === 'log' ? 'active' : '' }}" href="{{ route('child_officer.offline_qr', ['tool' => 'log']) }}">Field Scan Log</a>
            <a class="co-nav-link" href="#">Digital IDs</a>
        </div>
    </details>

    <div class="co-nav-title">Activities</div>
    <a class="co-nav-link {{ request()->routeIs('child_officer.follow-ups.*') ? 'active' : '' }}" href="{{ route('child_officer.follow-ups.index') }}">Follow-ups</a>
    <div class="co-nav-title">Reports</div>

    <a class="co-nav-link {{ request()->routeIs('child_officer.reports.index') ? 'active' : '' }}"
   href="{{ route('child_officer.reports.index') }}">
    Child Reports
</a>


<a class="co-nav-link {{ request()->routeIs('child_officer.registration_reports.*') ? 'active' : '' }}"
   href="{{ route('child_officer.registration_reports.index') }}">
    Child Registration Report
</a>

<a class="co-nav-link {{ request()->routeIs('child_officer.follow_up_reports.children.*') ? 'active' : '' }}"
   href="{{ route('child_officer.follow_up_reports.children.index') }}">
    Child Follow-up Report
</a>

<a class="co-nav-link {{ request()->routeIs('child_officer.follow_up_reports.events.*') ? 'active' : '' }}"
   href="{{ route('child_officer.follow_up_reports.events.index') }}">
    Event Follow-up Report
</a>


    <div class="co-nav-title">Account</div>
    <a class="co-nav-link" href="#">Profile</a>

    <form class="co-logout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="co-nav-link">Logout</button>
    </form>
</aside>

<script>
    const childOfficerSidebar = document.getElementById('childOfficerSidebar');
    const childOfficerSidebarToggle = document.getElementById('childOfficerSidebarToggle');
    const childOfficerSidebarKey = 'kfhiSidebarCollapsed';

    function setChildOfficerSidebarCollapsed(collapsed) {
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        const label = collapsed ? 'Expand navigation' : 'Collapse navigation';
        childOfficerSidebarToggle.setAttribute('aria-label', label);
        childOfficerSidebarToggle.title = label;
        childOfficerSidebarToggle.setAttribute('aria-expanded', String(!collapsed));
        childOfficerSidebar.setAttribute('aria-hidden', String(collapsed));
        localStorage.setItem(childOfficerSidebarKey, String(collapsed));
    }

    setChildOfficerSidebarCollapsed(localStorage.getItem(childOfficerSidebarKey) === 'true');
    childOfficerSidebarToggle.addEventListener('click', function () {
        setChildOfficerSidebarCollapsed(!document.body.classList.contains('sidebar-collapsed'));
    });
</script>
