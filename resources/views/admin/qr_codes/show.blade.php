<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Child ID Card - {{ $child->child_code }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            font-family: Arial, Helvetica, sans-serif;
            background: #eef5f0;
            color: #244332;
        }

        .page-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .page-title h1 {
            margin: 0;
            color: #246b43;
            font-size: 25px;
        }

        .page-title p {
            margin: 7px 0 0;
            color: #718078;
            font-size: 13px;
        }

        /*
        ==========================================
        ID-1 STANDARD CARD
        85.60mm × 54.00mm
        ==========================================
        */

        .id-card {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            margin: 0 auto;

            background: #ffffff;

            border: 1px solid #cfe0d4;
            border-radius: 3mm;

            box-shadow:
                0 12px 30px rgba(30, 70, 45, 0.15);

            padding: 4mm;
        }

        /*
        ==========================================
        CARD TOP
        ==========================================
        */

        .card-header {
            height: 9mm;

            display: flex;
            align-items: center;
            justify-content: space-between;

            border-bottom: 0.4mm solid #dce9df;
            padding-bottom: 1.5mm;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 2mm;
        }

        .brand-logo {
            width: 11mm;
            height: 7mm;
            object-fit: contain;
        }

        .brand-text {
            line-height: 1.1;
        }

        .brand-name {
            font-size: 3.2mm;
            font-weight: 700;
            color: #246b43;
        }

        .brand-subtitle {
            margin-top: 0.8mm;
            font-size: 1.8mm;
            color: #718078;
        }

        .card-type {
            font-size: 2.1mm;
            font-weight: 700;
            color: #246b43;
            text-align: right;
        }

        .card-type span {
            display: block;
            margin-top: 0.8mm;
            font-size: 1.7mm;
            font-weight: 500;
            color: #7a8981;
        }

        /*
        ==========================================
        CARD CONTENT
        ==========================================
        */

        .card-content {
            height: 36mm;

            display: flex;
            gap: 3mm;

            padding-top: 3mm;
        }

        /*
        PHOTO
        */

        .photo-area {
            width: 18mm;
            flex-shrink: 0;

            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .child-photo {
            width: 18mm;
            height: 22mm;

            object-fit: cover;

            border-radius: 1.5mm;

            border: 0.5mm solid #cde2d3;

            background: #eef6f0;
        }

        .photo-placeholder {
            width: 18mm;
            height: 22mm;

            border-radius: 1.5mm;

            border: 0.5mm solid #cde2d3;

            background: #eef6f0;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #2e7d4f;

            font-size: 7mm;
            font-weight: 700;
        }

        /*
        INFORMATION
        */

        .child-details {
            flex: 1;
            min-width: 0;
        }

        .child-id-label {
            font-size: 1.7mm;
            color: #819087;
            text-transform: uppercase;
            letter-spacing: 0.2mm;
        }

        .child-id {
            margin-top: 0.5mm;

            font-size: 3.3mm;
            font-weight: 800;

            color: #246b43;
            letter-spacing: 0.2mm;
        }

        .child-name {
            margin-top: 2.5mm;

            font-size: 3.2mm;
            font-weight: 700;

            color: #263f31;

            line-height: 1.15;

            max-width: 39mm;
        }

        .detail-row {
            margin-top: 2.2mm;
        }

        .detail-label {
            display: block;

            font-size: 1.6mm;
            color: #819087;

            margin-bottom: 0.4mm;
        }

        .detail-value {
            display: block;

            font-size: 2.1mm;
            font-weight: 600;

            color: #30483a;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            max-width: 39mm;
        }

        /*
        ==========================================
        QR CODE
        ==========================================
        */

        .qr-area {
            width: 22mm;

            flex-shrink: 0;

            display: flex;
            flex-direction: column;

            align-items: center;
            justify-content: center;
        }

        .qr-image {
            width: 19mm;
            height: 19mm;

            display: block;

            background: #ffffff;
        }

        .qr-image canvas,
        .qr-image img {
            display: block;
            width: 100%;
            height: 100%;
        }

        .qr-label {
            margin-top: 1.2mm;

            font-size: 1.55mm;
            color: #6d7d73;

            text-align: center;
        }

        /*
        ==========================================
        CARD FOOTER
        ==========================================
        */

        .card-footer {
            position: absolute;

            left: 4mm;
            right: 4mm;
            bottom: 2.5mm;

            padding-top: 1.2mm;

            border-top: 0.3mm solid #e1ebe4;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left {
            font-size: 1.55mm;
            color: #718078;
        }

        .footer-right {
            font-size: 1.55mm;
            font-weight: 600;
            color: #246b43;
        }

        /*
        ==========================================
        ACTION BUTTONS
        ==========================================
        */

        .actions {
            margin-top: 30px;

            display: flex;
            justify-content: center;
            align-items: center;

            gap: 10px;

            flex-wrap: wrap;
        }

        .btn {
            border: none;

            padding: 11px 18px;

            border-radius: 9px;

            font-size: 13px;
            font-weight: 600;

            cursor: pointer;

            text-decoration: none;

            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: #2e7d4f;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #24683f;
        }

        .btn-download {
            background: #e3f2e8;
            color: #246b43;
        }

        .btn-download:hover {
            background: #d5eadc;
        }

        .btn-secondary {
            background: #e8efeb;
            color: #526158;
        }

        .btn-secondary:hover {
            background: #dce7e0;
        }

        /*
        ==========================================
        MOBILE PREVIEW
        ==========================================
        */

        @media (max-width: 500px) {

            body {
                padding: 25px 10px;
            }

            .id-card {
                transform-origin: top center;
            }

            .actions {
                margin-top: 20px;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }
        }

        /*
        ==========================================
        PRINT
        ==========================================
        */

        @page {
            size: 85.6mm 54mm;
            margin: 0;
        }

        @media print {

            html,
            body {
                width: 85.6mm;
                height: 54mm;

                margin: 0;
                padding: 0;

                background: #ffffff;
            }

            .page-title,
            .actions {
                display: none !important;
            }

            .id-card {
                width: 85.6mm;
                height: 54mm;

                margin: 0;

                border: none;
                border-radius: 0;

                box-shadow: none;
            }
        }
    </style>
</head>

<body>

<div class="page-title">

    <h1>Child ID Card</h1>

    <p>
        Secure KFHI child identification card
    </p>

</div>


@if($qrCode && $qrCode->qr_image_path)

    <!-- ==========================================
         ID CARD
    =========================================== -->

    <div
        class="id-card"
        id="child-id-card"
    >

        <!-- CARD HEADER -->

        <div class="card-header">

            <div class="brand">

                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="KFHI Logo"
                    class="brand-logo"
                >

                <div class="brand-text">

                    <div class="brand-name">
                        KFHI
                    </div>

                    <div class="brand-subtitle">
                        Friends of Hope International
                    </div>

                </div>

            </div>


            <div class="card-type">

                CHILD ID CARD

                <span>
                    KFHI-CMS
                </span>

            </div>

        </div>


        <!-- CARD CONTENT -->

        <div class="card-content">

            <!-- CHILD PHOTO -->

            <div class="photo-area">

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

            </div>


            <!-- CHILD INFORMATION -->

            <div class="child-details">

                <div class="child-id-label">
                    Child ID
                </div>

                <div class="child-id">
                    {{ $child->child_code }}
                </div>


                <div class="child-name">

                    {{ $child->first_name }}

                    @if($child->middle_name)
                        {{ $child->middle_name }}
                    @endif

                    {{ $child->last_name }}

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        GN Division
                    </span>

                    <span class="detail-value">
                        {{ $child->gn_division ?: 'Not Provided' }}
                    </span>

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        District
                    </span>

                    <span class="detail-value">
                        {{ $child->district ?: 'Not Provided' }}
                    </span>

                </div>

            </div>


            <!-- QR CODE -->

            <div class="qr-area">

                <div id="child-qr" class="qr-image" role="img" aria-label="QR code for {{ $child->child_code }}"></div>

                <div class="qr-label">
                    Scan for secure identification
                </div>

            </div>

        </div>


        <!-- CARD FOOTER -->

        <div class="card-footer">

            <div class="footer-left">
                Secure Child Identification
            </div>

            <div class="footer-right">
                KFHI-CMS
            </div>

        </div>

    </div>


    <!-- ==========================================
         ACTION BUTTONS
    =========================================== -->

    <div class="actions">

        <button
            type="button"
            onclick="printCard()"
            class="btn btn-primary"
        >
            🖨 Print ID Card
        </button>


        <button
            type="button"
            onclick="downloadCard()"
            class="btn btn-download"
        >
            ↓ Download PNG
        </button>

        <a
            href="{{ route('children.show', $child) }}"
            class="btn btn-secondary"
        >
            ← Back to Child
        </a>

    </div>

@else

    <div style="
        max-width:600px;
        margin:40px auto;
        padding:30px;
        text-align:center;
        background:#ffffff;
        border-radius:15px;
        color:#718078;
    ">

        QR code has not been generated for this child.


    </div>

@endif


<!-- html2canvas (local offline bundle) -->
<script src="{{ asset('js/html2canvas.min.js') }}"></script>
<script src="{{ asset('js/qrcode.min.js') }}?v=2"></script>


<script>

    const qrTarget = document.getElementById('child-qr');
    if (qrTarget && typeof window.QRCode === 'function') {
        new window.QRCode(qrTarget, {
            text: @json($child->child_code),
            width: 128,
            height: 128,
            correctLevel: window.QRCode.CorrectLevel.M,
        });
    }

    function printCard() {

        window.print();

    }


    async function downloadCard() {

        const card = document.getElementById('child-id-card');

        if (!card) {
            alert('ID card is not available.');
            return;
        }

        try {

            const canvas = await html2canvas(card, {

                scale: 4,

                useCORS: true,

                backgroundColor: '#ffffff',

                logging: false,

                imageTimeout: 15000

            });


            const link = document.createElement('a');

            link.download = @json($child->child_code . '.png');

            link.href = canvas.toDataURL('image/png');

            link.click();

        } catch (error) {

            console.error(error);

            alert(
                'Unable to download the ID card. Please try again.'
            );

        }

    }

</script>

</body>
</html>
