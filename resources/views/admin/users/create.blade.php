<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add User - KFHI CMS</title>

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
            background: #f1f7f3;
            color: #26352b;
        }

        .page {
            flex: 1;
            min-width: 0;
            min-height: 100vh;
            padding: 30px;
        }

        .container {
            max-width: 850px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 25px;
        }

        .back {
            display: inline-block;
            text-decoration: none;
            color: #2f7d4a;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .back:hover {
            color: #205b34;
        }

        h1 {
            color: #1f5d3a;
            font-size: 28px;
            margin-bottom: 6px;
        }

        .subtitle {
            color: #718078;
            font-size: 14px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(40, 80, 55, 0.08);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: bold;
            color: #385b43;
            margin-bottom: 8px;
        }

        .required {
            color: #c44a4a;
        }

        input,
        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d9e6dc;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            background: #fbfdfb;
            color: #26352b;
        }

        input::placeholder {
            color: #9aa69e;
        }

        input:focus,
        select:focus {
            border-color: #4c9b66;
            box-shadow: 0 0 0 3px rgba(76, 155, 102, 0.10);
        }

        select {
            cursor: pointer;
        }

        .error {
            margin-top: 6px;
            color: #b33a3a;
            font-size: 12px;
        }

        .info-box {
            grid-column: 1 / -1;
            background: #f0f8f2;
            border: 1px solid #dcecdf;
            border-radius: 10px;
            padding: 13px 15px;
            color: #52705c;
            font-size: 13px;
            line-height: 1.5;
        }

        .info-box strong {
            color: #2f7045;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #edf1ee;
        }

        .btn {
            border: none;
            text-decoration: none;
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            display: inline-block;
        }

        .cancel {
            background: #eef2ef;
            color: #536159;
        }

        .cancel:hover {
            background: #e1e8e3;
        }

        .save {
            background: #2f7d4a;
            color: white;
        }

        .save:hover {
            background: #25663b;
        }

        @media (max-width: 650px) {

            .page {
                padding: 18px;
            }

            .card {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

@include('components.admin-sidebar')

<div class="page">

    <div class="container">

        <!-- Header -->
        <div class="header">

            <a href="{{ route('users.index') }}" class="back">
                ← Back to User Management
            </a>

            <h1>Add New User</h1>

            <p class="subtitle">
                Create a new KFHI CMS user account and assign the appropriate role.
            </p>

        </div>


        <!-- Form Card -->
        <div class="card">

        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">

                    <!-- Information -->
                    <div class="info-box">

                        <strong>Account Information</strong><br>

                        Create an individual account for each KFHI staff member.
                        The assigned role will determine what features and information
                        the user can access.

                    </div>
                    <div class="form-group">
    <label for="profile_photo">Profile Picture</label>

    <input
        type="file"
        id="profile_photo"
        name="profile_photo"
        accept="image/jpeg,image/png,image/jpg,image/webp"
    >

    @error('profile_photo')
        <div class="error">{{ $message }}</div>
    @enderror

    <small>
        JPG, JPEG, PNG or WEBP. Maximum size: 2MB.
    </small>
</div>

                    <!-- Full Name -->
                    <div class="form-group">

                        <label>
                            Full Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter full name"
                            required
                        >

                        @error('name')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Username -->
                    <div class="form-group">

                        <label>
                            Username <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Enter username"
                            required
                        >

                        @error('username')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Email -->
                    <div class="form-group">

                        <label>
                            Email Address <span class="required">*</span>
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter email address"
                            required
                        >

                        @error('email')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Phone -->
                    <div class="form-group">

                        <label>
                            Phone Number
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="Enter phone number"
                        >

                        @error('phone')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Role -->
                    <div class="form-group">

                        <label>
                            Role <span class="required">*</span>
                        </label>

                        <select name="role" required>

                            <option value="">
                                Select role
                            </option>

                            <option
                                value="admin"
                                {{ old('role') === 'admin' ? 'selected' : '' }}
                            >
                                Admin
                            </option>

                            <option
                                value="child_officer"
                                {{ old('role') === 'child_officer' ? 'selected' : '' }}
                            >
                                Child Officer
                            </option>

                            <option
                                value="field_officer"
                                {{ old('role') === 'field_officer' ? 'selected' : '' }}
                            >
                                Field Officer
                            </option>

                            <option
                                value="enumerator"
                                {{ old('role') === 'enumerator' ? 'selected' : '' }}
                            >
                                Enumerator
                            </option>

                        </select>

                        @error('role')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Status -->
                    <div class="form-group">

                        <label>
                            Status <span class="required">*</span>
                        </label>

                        <select name="status" required>

                            <option
                                value="active"
                                {{ old('status', 'active') === 'active' ? 'selected' : '' }}
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                {{ old('status') === 'inactive' ? 'selected' : '' }}
                            >
                                Inactive
                            </option>

                        </select>

                        @error('status')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Password -->
                    <div class="form-group">

                        <label>
                            Password <span class="required">*</span>
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter password"
                            required
                        >

                        @error('password')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Confirm Password -->
                    <div class="form-group">

                        <label>
                            Confirm Password <span class="required">*</span>
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                            required
                        >

                    </div>

                </div>


                <!-- Buttons -->
                <div class="actions">

                    <a
                        href="{{ route('users.index') }}"
                        class="btn cancel"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn save"
                    >
                        Create User
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>