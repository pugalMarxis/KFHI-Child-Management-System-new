<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Scan QR | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f8f3;
            color: #1f2937;
            min-height: 100vh;
        }

        .page-shell {
            display: flex;
            min-height: 100vh;
        }

        .app-sidebar {
            width: 250px;
            flex: 0 0 250px;
            background: #ffffff;
            border-right: 1px solid #dfe9e2;
            padding: 24px 16px;
        }

        .app-sidebar h2 {
            margin: 0 0 24px;
            color: #2e7d32;
            font-size: 20px;
        }

        .sidebar-toggle {
            border: 1px solid #dfe9e2;
            border-radius: 7px;
            padding: 8px 10px;
            background: #ffffff;
            color: #365344;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .header .sidebar-toggle {
            margin-right: auto;
        }

        .page-shell.sidebar-collapsed .app-sidebar {
            display: none;
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

        .identification-menu {
            margin-bottom: 4px;
        }

        .identification-menu summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 12px;
            border-radius: 7px;
            color: #53665a;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            list-style: none;
        }

        .identification-menu summary::-webkit-details-marker {
            display: none;
        }

        .identification-menu summary::after {
            content: '+';
            font-size: 16px;
        }

        .identification-menu[open] summary::after {
            content: '-';
        }

        .identification-menu summary:hover,
        .identification-menu summary.active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .identification-menu .sidebar-link {
            margin-left: 12px;
            padding-left: 14px;
            border-left: 1px solid #dfe9e2;
        }

        .app-main {
            flex: 1;
            min-width: 0;
        }

        .header {
            background: #ffffff;
            border-bottom: 1px solid #dfe9e2;
            padding: 18px 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-size: 20px;
            font-weight: 700;
            color: #2e7d32;
        }

        .role {
            font-size: 14px;
            color: #64748b;
        }

        .container {
            width: min(1000px, 100%);
            margin: 30px auto;
            padding: 0 35px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            color: #2e7d32;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        .page-title {
            margin-bottom: 8px;
            font-size: 28px;
            color: #1f2937;
        }

        .page-description {
            color: #64748b;
            margin-bottom: 25px;
        }

        .scanner-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.08);
            border: 1px solid #e2ebe5;
        }

        .scanner-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .scanner-header h2 {
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .scanner-header p {
            color: #64748b;
            font-size: 14px;
        }

        #reader {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 16px;
        }

        .scanner-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #2e7d32;
            color: white;
        }

        .btn-primary:hover {
            background: #256b29;
        }

        .btn-secondary {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .btn-secondary:hover {
            background: #d7ecd9;
        }

        .status {
            max-width: 520px;
            margin: 20px auto 0;
            padding: 14px;
            border-radius: 10px;
            background: #f1f8f3;
            color: #52635a;
            text-align: center;
            font-size: 14px;
        }

        .result-card {
            display: none;
            max-width: 520px;
            margin: 25px auto 0;
            padding: 22px;
            border-radius: 14px;
            background: #f7fbf8;
            border: 1px solid #cde3d1;
        }

        .result-card h3 {
            color: #2e7d32;
            margin-bottom: 15px;
        }

        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 9px 0;
            border-bottom: 1px solid #e5eee7;
        }

        .result-row:last-of-type {
            border-bottom: none;
        }

        .result-label {
            color: #64748b;
            font-size: 13px;
        }

        .result-value {
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        /* Result action buttons */
        .result-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #e2ebe4;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 16px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s ease;
            flex: 1;
            min-width: 140px;
            text-align: center;
        }

        .action-btn.primary {
            background: #2e7d32;
            color: #ffffff;
        }

        .action-btn.primary:hover {
            background: #256b29;
        }

        .action-btn.secondary {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .action-btn.secondary:hover {
            background: #d7ecd9;
        }

        .action-btn.outline {
            background: #ffffff;
            color: #2e7d32;
            border: 1px solid #a9c9ad;
        }

        .action-btn.outline:hover {
            background: #f1f8f2;
        }

        @media (max-width: 650px) {

            .page-shell {
                display: block;
            }

            .app-sidebar {
                width: 100%;
                padding: 14px;
                border-right: 0;
                border-bottom: 1px solid #dfe9e2;
            }

            .app-sidebar h2 {
                margin: 0 0 10px;
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

            .header {
                padding: 15px 18px;
                gap: 12px;
            }

            .container {
                margin: 25px auto;
            }

            .scanner-card {
                padding: 20px;
            }

            .page-title {
                font-size: 23px;
            }

            .result-actions {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                flex: none;
            }
        }
    </style>
</head>

<body>

<div class="page-shell">
@if(auth()->user()->isAdmin())
    @include('components.admin-sidebar')
@else
    @include('components.child-officer-sidebar')
@endif

<div class="app-main">
<header class="header">

    <div class="brand">
        KFHI-CMS
    </div>

    <div class="role">
        {{ auth()->user()->isAdmin() ? 'Administrator' : 'Child Officer' }}: {{ auth()->user()->name }}
    </div>

</header>


<main class="container">

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
        <a
            href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('child_officer.dashboard') }}"
            class="back-btn"
            style="margin-bottom:0;"
        >
            ← Back to Dashboard
        </a>

        @if(auth()->user()->isAdmin())
        <a
            href="{{ route('admin.offline_qr', ['tool' => 'generator']) }}"
            style="display:inline-flex; align-items:center; gap:8px; padding:8px 14px; background:#e8f5e9; color:#2e7d32; border-radius:9px; text-decoration:none; font-weight:600; font-size:13px; border:1px solid #cde3d1;"
        >
            ⚡ Open Offline QR (No Network Mode) →
        </a>
        @endif
    </div>

    <h1 class="page-title">
        Scan Child QR Code
    </h1>

    <p class="page-description">
        Scan a child's secure QR code to identify the registered child. Works with online URL tokens and offline self-contained QR codes.
    </p>


    <div class="scanner-card">

        <div class="scanner-header">

            <h2>
                QR Scanner
            </h2>

            <p>
                Allow camera access and position the child's QR code
                inside the scanning area.
            </p>

        </div>


        <!-- QR Reader -->
        <div id="reader"></div>


        <!-- Scanner Buttons -->
        <div class="scanner-actions">

            <button
                type="button"
                class="btn btn-primary"
                id="startScanner"
            >
                Start Scanner
            </button>

            <button
                type="button"
                class="btn btn-secondary"
                id="stopScanner"
            >
                Stop Scanner
            </button>

        </div>


        <!-- Scanner Status -->
        <div
            class="status"
            id="scannerStatus"
        >
            Scanner is ready.
        </div>


        <!-- Child Result -->
        <div
            class="result-card"
            id="resultCard"
        >

            <h3>
                Child Identified
            </h3>


            <div class="result-row">

                <span class="result-label">
                    Child Code
                </span>

                <span
                    class="result-value"
                    id="childCode"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Child Name
                </span>

                <span
                    class="result-value"
                    id="childName"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Gender
                </span>

                <span
                    class="result-value"
                    id="childGender"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Date of Birth
                </span>

                <span
                    class="result-value"
                    id="childDob"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    School
                </span>

                <span
                    class="result-value"
                    id="childSchool"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Grade
                </span>

                <span
                    class="result-value"
                    id="childGrade"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    GN Division
                </span>

                <span
                    class="result-value"
                    id="childGn"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    District
                </span>

                <span
                    class="result-value"
                    id="childDistrict"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Status
                </span>

                <span
                    class="result-value"
                    id="childStatus"
                >
                    -
                </span>

            </div>


            <!-- Child Officer Actions -->
            <div class="result-actions">

                <a
                    href="#"
                    id="editChildBtn"
                    class="action-btn primary"
                >
                    Edit Child Information
                </a>


                <a
                    href="#"
                    id="addInfoBtn"
                    class="action-btn secondary"
                >
                    Add New Information
                </a>


                <a
                    href="#"
                    id="viewChildBtn"
                    class="action-btn outline"
                >
                    View Full Child Profile
                </a>

            </div>

        </div>

    </div>

</main>
</div>
</div>


<script src="{{ asset('js/offline-child-cache.js') }}"></script>

<script
    src="{{ asset('js/html5-qrcode.min.js') }}"
    type="text/javascript"
></script>

<script>

    let scanner = null;
    let scannerRunning = false;
    let processingQR = false;

    const statusBox =
        document.getElementById('scannerStatus');

    const resultCard =
        document.getElementById('resultCard');


    /*
    |--------------------------------------------------------------------------
    | Update Scanner Status
    |--------------------------------------------------------------------------
    */

    function updateStatus(message) {

        statusBox.textContent = message;

    }


    /*
    |--------------------------------------------------------------------------
    | Lookup QR Code
    |--------------------------------------------------------------------------
    */

    async function lookupQRCode(decodedText) {

        if (processingQR) {
            return;
        }

        processingQR = true;

        updateStatus(
            "QR code detected. Verifying..."
        );

        // Check if QR contains self-contained JSON (Offline QR code)
        let offlineChild = null;
        let childCode = '';
        try {
            const trimmed = decodedText.trim();
            if (trimmed.startsWith('{') && trimmed.endsWith('}')) {
                const parsed = JSON.parse(trimmed);
                if (parsed.code || parsed.name) {
                    childCode = parsed.code || '';
                    offlineChild = {
                        id: parsed.id || 0,
                        child_code: parsed.code || 'N/A',
                        full_name: parsed.name || 'N/A',
                        gender: parsed.gender || 'N/A',
                        date_of_birth: parsed.dob || 'N/A',
                        school_name: parsed.school || 'N/A',
                        grade: parsed.grade || 'N/A',
                        gn_division: parsed.gn || 'N/A',
                        district: parsed.district || 'N/A',
                        status: parsed.status || 'Active'
                    };
                }
            } else if (trimmed && !trimmed.includes('/qr/scan/')) {
                childCode = trimmed;
            }
        } catch (e) {
            // Not JSON, continue to server lookup
        }

        if (offlineChild) {
            displayChildData(offlineChild, true);
            await stopScanner();
            processingQR = false;
            return;
        }

        const findSavedChild = async () => {
            if (!childCode || !window.KFHIChildOfflineCache) return null;
            return window.KFHIChildOfflineCache.findByCode(childCode);
        };

        if (!navigator.onLine) {
            try {
                const savedChild = await findSavedChild();
                if (savedChild) {
                    displayChildData(savedChild, true);
                    await stopScanner();
                    processingQR = false;
                    return;
                }
            } catch (error) {}

            updateStatus('This child is not saved on this device. Connect and sync the child first.');
            processingQR = false;
            return;
        }

        try {

            const response = await fetch(
                "{{ route('child_officer.qr.lookup') }}",
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN":
                            "{{ csrf_token() }}"
                    },

                    body: JSON.stringify({
                        token: decodedText
                    })
                }
            );


            const data = await response.json();


            /*
            |--------------------------------------------------------------------------
            | Invalid QR
            |--------------------------------------------------------------------------
            */

            if (!response.ok || !data.success) {

                resultCard.style.display = "none";

                updateStatus(
                    data.message ||
                    "Invalid or inactive QR code."
                );

                processingQR = false;

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Child Data
            |--------------------------------------------------------------------------
            */

            displayChildData(data.child, false);
            await stopScanner();

        } catch (error) {

            console.error(error);
            try {
                const savedChild = await findSavedChild();
                if (savedChild) {
                    displayChildData(savedChild, true);
                    await stopScanner();
                    return;
                }
            } catch (localError) {}

            updateStatus('Unable to contact the server, and this child is not saved on this device.');

        } finally {

            processingQR = false;

        }

    }

    function displayChildData(child, isOffline = false) {
        const editChildUrl = @json(auth()->user()->isAdmin()
            ? route('children.edit', ['child' => '__CHILD_ID__'])
            : route('child_officer.children.edit', ['child' => '__CHILD_ID__']));

        const addInfoUrl = @json(route('child_officer.child_information.create', ['child' => '__CHILD_ID__']));

        const viewChildUrl = @json(auth()->user()->isAdmin()
            ? route('children.show', ['child' => '__CHILD_ID__'])
            : route('child_officer.children.show', ['child' => '__CHILD_ID__']));

        if (child.id && child.id > 0) {
            document.getElementById('editChildBtn').href =
                editChildUrl.replace('__CHILD_ID__', child.id);
            document.getElementById('editChildBtn').style.display = 'inline-flex';

            document.getElementById('addInfoBtn').href =
                addInfoUrl.replace('__CHILD_ID__', child.id);
            document.getElementById('addInfoBtn').style.display = @json(auth()->user()->isAdmin() ? 'none' : 'inline-flex');

            document.getElementById('viewChildBtn').href =
                viewChildUrl.replace('__CHILD_ID__', child.id);
            document.getElementById('viewChildBtn').style.display = 'inline-flex';
        } else {
            document.getElementById('editChildBtn').style.display = 'none';
            document.getElementById('addInfoBtn').style.display = 'none';
            document.getElementById('viewChildBtn').style.display = 'none';
        }

        document.getElementById('childCode').textContent = child.child_code || '-';
        document.getElementById('childName').textContent = child.full_name || '-';
        document.getElementById('childGender').textContent = child.gender || '-';
        document.getElementById('childDob').textContent = child.date_of_birth || '-';
        document.getElementById('childSchool').textContent = child.school_name || '-';
        document.getElementById('childGrade').textContent = child.grade || '-';
        document.getElementById('childGn').textContent = child.gn_division || '-';
        document.getElementById('childDistrict').textContent = child.district || '-';
        document.getElementById('childStatus').textContent = child.status || '-';

        resultCard.style.display = "block";

        updateStatus(
            isOffline
                ? "Child identified successfully (Offline QR Code - No Network Needed)."
                : "Child identified successfully."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Start Scanner
    |--------------------------------------------------------------------------
    */

    function startScanner() {

        if (scannerRunning) {
            return;
        }


        resultCard.style.display = "none";


        scanner =
            new Html5Qrcode("reader");


        scanner.start(
            {
                facingMode: "environment"
            },
            {
                fps: 10,

                qrbox: {
                    width: 250,
                    height: 250
                }
            },

            function(decodedText) {

                lookupQRCode(decodedText);

            },

            function(errorMessage) {

                // Normal scanning errors are ignored.

            }

        )
        .then(function() {

            scannerRunning = true;

            updateStatus(
                "Scanner started. Point the camera at the QR code."
            );

        })
        .catch(function(error) {

            console.error(error);

            scannerRunning = false;

            updateStatus(
                "Unable to access the camera. Please allow camera permission."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Stop Scanner
    |--------------------------------------------------------------------------
    */

    async function stopScanner() {

        if (!scanner || !scannerRunning) {
            return;
        }


        try {

            await scanner.stop();

            scanner.clear();

            scannerRunning = false;

            updateStatus(
                "Scanner stopped."
            );

        } catch (error) {

            console.error(error);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Button Events
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('startScanner')
        .addEventListener(
            'click',
            startScanner
        );


    document
        .getElementById('stopScanner')
        .addEventListener(
            'click',
            stopScanner
        );

</script>

</body>
</html>
