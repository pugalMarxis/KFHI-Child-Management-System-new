<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit User - KFHI CMS</title>

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

        input:focus,
        select:focus {
            border-color: #4c9b66;
            box-shadow: 0 0 0 3px rgba(76, 155, 102, 0.10);
        }

        .hint {
            margin-top: 6px;
            font-size: 12px;
            color: #849088;
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

            <h1>Edit User</h1>

            <p class="subtitle">
                Update the account information and access role for this user.
            </p>

        </div>


        <!-- Form -->
        <div class="card">

        <form
    action="{{ route('users.update', $user) }}"
    method="POST"
    enctype="multipart/form-data"
>

                @csrf
                @method('PUT')

                <div class="form-grid">

                    <!-- Information -->
                    <div class="info-box">

                        <strong>Account Information</strong><br>

                        Update the user's information below.
                        Leave the password fields empty if you do not want to
                        change the current password.

                    </div>
<!-- Profile Picture -->
<div class="form-group full">

    <label>
        Profile Picture
    </label>

    <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">

        @if($user->profile_photo)

            <img
                src="{{ asset('storage/' . $user->profile_photo) }}"
                alt="{{ $user->name }}"
                style="
                    width: 85px;
                    height: 85px;
                    object-fit: cover;
                    border-radius: 50%;
                    border: 3px solid #dcebe1;
                    background: #eef7f1;
                "
            >

        @else

            <div style="
                width: 85px;
                height: 85px;
                border-radius: 50%;
                background: #e3f1e7;
                border: 3px solid #d4e7da;
                color: #2e7d4f;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 25px;
                font-weight: bold;
            ">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>

        @endif

        <div style="flex: 1; min-width: 220px;">

            <input
                type="file"
                name="profile_photo"
                accept="image/jpeg,image/png,image/jpg,image/webp"
            >

            <div class="hint">
                Select a new profile picture to replace the current one.
                JPG, JPEG, PNG or WEBP. Maximum size: 2MB.
            </div>

            @error('profile_photo')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror

        </div>

    </div>

</div>

                    <!-- Full Name -->
                    <div class="form-group">

                        <label>
                            Full Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
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
                            value="{{ old('username', $user->username) }}"
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
                            value="{{ old('email', $user->email) }}"
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
                            value="{{ old('phone', $user->phone) }}"
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

                            <option
                                value="admin"
                                {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}
                            >
                                Admin
                            </option>

                            <option
                                value="child_officer"
                                {{ old('role', $user->role) === 'child_officer' ? 'selected' : '' }}
                            >
                                Child Officer
                            </option>

                            <option
                                value="field_officer"
                                {{ old('role', $user->role) === 'field_officer' ? 'selected' : '' }}
                            >
                                Field Officer
                            </option>

                            <option
                                value="enumerator"
                                {{ old('role', $user->role) === 'enumerator' ? 'selected' : '' }}
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
                                {{ old('status', $user->status) === 'active' ? 'selected' : '' }}
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}
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


                    <!-- New Password -->
                    <div class="form-group">

                        <label>
                            New Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter new password"
                        >

                        <div class="hint">
                            Leave empty to keep the current password.
                        </div>

                        @error('password')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Confirm Password -->
                    <div class="form-group">

                        <label>
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm new password"
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
                        Update User
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>