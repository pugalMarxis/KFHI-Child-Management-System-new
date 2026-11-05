<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>KFHI-CMS | Admin Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #eef7f0;
            color: #294432;
        }

        .layout {
            min-height: 100vh;
            display: flex;
            flex-wrap: wrap;
        }

        /* Sidebar */

        .sidebar {
            width: 250px;
            background: #f4faf5;
            padding: 25px 18px;
            box-shadow: 5px 0 20px rgba(0, 70, 30, 0.08);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
        }

        .brand {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 1px solid #dce9df;
        }

        .brand img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .brand h2 {
            color: #236b3b;
            font-size: 19px;
        }

        .brand p {
            color: #7b897f;
            font-size: 11px;
            margin-top: 4px;
        }

        .nav {
            margin-top: 25px;
        }

        .nav-title {
            font-size: 11px;
            color: #8a978d;
            text-transform: uppercase;
            margin: 0 10px 10px;
            letter-spacing: 1px;
        }

        .nav a {
            display: block;
            text-decoration: none;
            color: #53665a;
            padding: 12px 14px;
            margin-bottom: 6px;
            border-radius: 10px;
            font-size: 14px;
        }

        .nav a:hover,
        .nav a.active {
            background: #e0f0e3;
            color: #28733a;
        }

        .logout-form {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 20px;
        }

        .logout-button {
            width: 100%;
            border: none;
            padding: 11px;
            border-radius: 10px;
            background: #fbeaea;
            color: #b44949;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-button:hover {
            background: #f5dcdc;
        }

        /* Main */

        .main {
            flex: 1;
            min-width: 0;
            padding: 30px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .topbar h1 {
            color: #236b3b;
            font-size: 28px;
        }

        .topbar p {
            color: #75837a;
            font-size: 13px;
            margin-top: 5px;
        }

        .user-info {
            background: #f4faf5;
            padding: 10px 16px;
            border-radius: 12px;
            box-shadow:
                4px 4px 10px rgba(0, 70, 30, 0.07),
                -4px -4px 10px rgba(255, 255, 255, 0.8);
        }

        .user-info strong {
            color: #2c693b;
            font-size: 14px;
        }

        .user-info span {
            display: block;
            color: #839087;
            font-size: 11px;
            margin-top: 3px;
        }

        /* Statistics */

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f4faf5;
            padding: 22px;
            border-radius: 18px;
            box-shadow:
                7px 7px 18px rgba(0, 70, 30, 0.08),
                -7px -7px 18px rgba(255, 255, 255, 0.9);
        }

        .stat-title {
            color: #77867b;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .stat-number {
            color: #246d38;
            font-size: 30px;
            font-weight: 700;
        }

        .stat-note {
            color: #8a978d;
            font-size: 11px;
            margin-top: 7px;
        }

        /* Content */

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: #f4faf5;
            border-radius: 18px;
            padding: 25px;
            box-shadow:
                7px 7px 18px rgba(0, 70, 30, 0.08),
                -7px -7px 18px rgba(255, 255, 255, 0.9);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h2 {
            color: #2c6339;
            font-size: 18px;
        }

        .card-header span {
            color: #89958d;
            font-size: 12px;
        }

        .activity {
            padding: 15px 0;
            border-bottom: 1px solid #e1ebe3;
        }

        .activity:last-child {
            border-bottom: none;
        }

        .activity strong {
            color: #41614b;
            font-size: 13px;
        }

        .activity p {
            color: #89958d;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Quick Actions */

        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .quick-action {
            padding: 18px 10px;
            text-align: center;
            text-decoration: none;
            background: #edf6ef;
            border-radius: 12px;
            color: #377345;
            font-size: 12px;
        }

        .quick-action:hover {
            background: #e1f0e4;
        }

        /* Responsive */

        @media (max-width: 1100px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 750px) {
            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 0;
                width: auto;
                flex: 1;
            }

            .topbar {
                display: block;
            }

            .user-info {
                margin-top: 15px;
                display: inline-block;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .topbar h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<div class="layout">

    @include('components.admin-sidebar')


    <!-- Main Content -->

    <main class="main">

        <div class="topbar">

            <div>
                <h1>Dashboard</h1>

                <p>
                    Welcome back. Here's an overview of KFHI-CMS.
                </p>
            </div>

            <div class="user-info">

                <strong>
                    {{ auth()->user()->name }}
                </strong>

                <span>
                    {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                </span>

            </div>

        </div>


        <!-- Statistics -->

        <section class="stats">

            <div class="stat-card">

                <div class="stat-title">
                    Total Children
                </div>

                <div class="stat-number">
                    0
                </div>

                <div class="stat-note">
                    Registered children
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Total Users
                </div>

                <div class="stat-number">
                    1
                </div>

                <div class="stat-note">
                    System users
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Total Events
                </div>

                <div class="stat-number">
                    0
                </div>

                <div class="stat-note">
                    Registered events
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Today's Attendance
                </div>

                <div class="stat-number">
                    0
                </div>

                <div class="stat-note">
                    Attendance records today
                </div>

            </div>

        </section>


        <!-- Dashboard Content -->

        <section class="content-grid">

            <div class="card">

                <div class="card-header">

                    <h2>Recent Activity</h2>

                    <span>
                        Latest system activities
                    </span>

                </div>

                <div class="activity">

                    <strong>
                        System Administrator account created
                    </strong>

                    <p>
                        Initial administrator account is active.
                    </p>

                </div>

                <div class="activity">

                    <strong>
                        KFHI-CMS system initialized
                    </strong>

                    <p>
                        Core database and authentication system are ready.
                    </p>

                </div>

            </div>


            <div class="card">

                <div class="card-header">

                    <h2>Quick Actions</h2>

                </div>

                <div class="quick-actions">

                    <a href="#" class="quick-action">
                        Add Child
                    </a>

                    <a href="#" class="quick-action">
                        Create Event
                    </a>

                    <a href="#" class="quick-action">
                        Add User
                    </a>

                    <a href="#" class="quick-action">
                        View Reports
                    </a>

                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>