<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful - {{ $questionnaire->title }}</title>

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
            padding: 25px;
        }

        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 45px 35px;
            text-align: center;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.10);
            border-top: 6px solid #2e7d32;
        }

        .icon-circle {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            background: #e8f5e9;
            color: #2e7d32;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin-bottom: 20px;
        }

        h1 {
            color: #1b4b24;
            font-size: 24px;
            margin-bottom: 12px;
        }

        p {
            color: #5c7062;
            line-height: 1.6;
            font-size: 15px;
            margin-bottom: 20px;
        }

        .info-box {
            background: #f4fbf6;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 13px;
            color: #3b5842;
            margin-bottom: 25px;
            border: 1px solid #dceede;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon-circle">✓</div>
    <h1>Thank You!</h1>
    <p>Your responses for <strong>{{ $questionnaire->title }}</strong> have been submitted and safely recorded.</p>

    <div class="info-box">
        Submission Reference: Completed on {{ now()->format('d M Y, h:i A') }}
    </div>
</div>

</body>
</html>
