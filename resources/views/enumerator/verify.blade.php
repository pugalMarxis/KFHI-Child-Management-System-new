<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enumerator Identity Verification - KFHI Field System</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #eef7f1;
            color: #1f3d2b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 35px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 12px 35px rgba(46, 125, 50, 0.12);
            border-top: 6px solid #2e7d32;
        }

        .logo-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        h1 {
            color: #1b4b24;
            font-size: 22px;
            margin-bottom: 8px;
        }

        p {
            color: #5d7163;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 22px;
        }

        .notice-box {
            background: #f3faf5;
            border-left: 4px solid #2e7d32;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 13px;
            color: #3b5842;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #204928;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            border: 1px solid #cfe0d3;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 15px;
            outline: none;
            background: #fbfdfb;
            color: #1f3d2b;
            transition: border 0.15s ease;
        }

        input:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.10);
        }

        .skip-link {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .skip-link:focus {
            left: 16px;
            top: 16px;
            width: auto;
            height: auto;
            padding: 10px 14px;
            background: #1e4b26;
            color: #fff;
            border-radius: 8px;
            z-index: 1000;
        }

        .btn-verify {
            width: 100%;
            background: #2e7d32;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease;
            margin-top: 10px;
        }

        input:focus-visible,
        button:focus-visible,
        a:focus-visible {
            outline: 3px solid rgba(46, 125, 50, 0.35);
            outline-offset: 2px;
        }

        .btn-verify:hover {
            background: #236327;
        }

        .alert-error {
            background: #fdeaea;
            border: 1px solid #f5c6cb;
            color: #b71c1c;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .logout-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #7b8f81;
            text-decoration: none;
        }

        .logout-link:hover {
            color: #b71c1c;
        }
    </style>
</head>
<body>
<a href="#enumerator-form" class="skip-link">Skip to login form</a>

<main class="auth-card" aria-labelledby="verification-title">
    <span class="logo-badge">KFHI Field Enumerator Portal</span>
    <h1 id="verification-title">Field Enumerator Entry</h1>
    <p>
        Enter your <strong>Full Name</strong> and the <strong>Common Access Password</strong> provided by your Child Officer or Admin to start surveying.
    </p>

    <div id="entry-notice" class="notice-box">
        🔒 No individual account setup is needed! Every survey you submit will be automatically attributed to your name for monitoring and reporting.
    </div>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form id="enumerator-form" method="POST" action="{{ route('enumerator.verify.post') }}" aria-describedby="entry-notice">
        @csrf

        <div class="form-group">
            <label for="enumerator_name">Your Full Name <span style="color: #c44;">*</span></label>
            <input type="text"
                   id="enumerator_name"
                   name="enumerator_name"
                   value="{{ old('enumerator_name', old('enumerator_code')) }}"
                   placeholder="e.g. Mohamed Faizer"
                   required
                   autofocus>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number <span style="font-weight: normal; color: #788c7e;">(Optional)</span></label>
            <input type="tel"
                   id="phone"
                   name="phone"
                   value="{{ old('phone') }}"
                   placeholder="e.g. 077 123 4567">
        </div>

        <div class="form-group">
            <label for="pin_code">Common Access Password <span style="color: #c44;">*</span></label>
            <input type="password"
                   id="pin_code"
                   name="pin_code"
                   placeholder="Enter the team common password"
                   required>
        </div>

        <button type="submit" class="btn-verify">
            🚀 Open Enumerator Dashboard →
        </button>
    </form>

    <div style="margin-top: 25px; text-align: center; font-size: 13px; color: #6b8071;">
        <a href="{{ route('home') }}" style="color: #2e7d32; text-decoration: none; font-weight: 600;">
            ← Return to Homepage
        </a>
    </div>
</main>

</body>
</html>
