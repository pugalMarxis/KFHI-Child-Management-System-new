<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Child - KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f8f4;
            color: #26352c;
        }

        .page {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            color: #245c3a;
            font-size: 28px;
        }

        .header p {
            margin-top: 7px;
            color: #718078;
            font-size: 14px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 5px 20px rgba(39, 83, 57, 0.08);
        }

        .section-title {
            margin: 0 0 20px;
            color: #2e7049;
            font-size: 18px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #46564d;
        }

        .required {
            color: #c64b4b;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d7e4db;
            border-radius: 9px;
            background: #fbfdfc;
            font-size: 14px;
            color: #26352c;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #62a477;
            box-shadow: 0 0 0 3px rgba(98, 164, 119, 0.12);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .error {
            color: #b33a3a;
            font-size: 12px;
            margin-top: 5px;
        }

        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e5eee8;
        }

        .btn {
            border: none;
            border-radius: 9px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .cancel-btn {
            background: #eef3ef;
            color: #526158;
        }

        .save-btn {
            background: #2e7d4f;
            color: white;
        }

        .save-btn:hover {
            background: #24663f;
        }

        @media (max-width: 700px) {
            .page {
                padding: 18px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body class="admin-layout-body">

@include('components.admin-sidebar')

<div class="page">

    <div class="header">
        <h1>Add New Child</h1>
        <p>Register a new child in the KFHI Child Management System.</p>
    </div>

    <div class="card">

    <form
    method="POST"
    action="{{ route('children.store') }}"
    enctype="multipart/form-data"
>

            @csrf

            <h2 class="section-title">Child Information</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label>
                        Child Code <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="child_code"
                        value="{{ old('child_code') }}"
                        placeholder="Example: KFHI-CH-0001"
                    >

                    @error('child_code')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
    <label>Child Photo</label>

    <input
        type="file"
        name="profile_photo"
        accept="image/jpeg,image/png,image/webp"
    >

    <small style="margin-top:6px; color:#7a8981;">
        JPG, PNG or WebP. Maximum size: 2 MB.
    </small>

    @error('profile_photo')
        <div class="error">{{ $message }}</div>
    @enderror
</div>
                <div class="form-group">
                    <label>
                        First Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="first_name"
                        value="{{ old('first_name') }}"
                        placeholder="Enter first name"
                    >

                    @error('first_name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Middle Name</label>

                    <input
                        type="text"
                        name="middle_name"
                        value="{{ old('middle_name') }}"
                        placeholder="Enter middle name"
                    >
                </div>

                <div class="form-group">
                    <label>
                        Last Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="last_name"
                        value="{{ old('last_name') }}"
                        placeholder="Enter last name"
                    >

                    @error('last_name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>
                        Date of Birth <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="date_of_birth"
                        value="{{ old('date_of_birth') }}"
                    >

                    @error('date_of_birth')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>
                        Gender <span class="required">*</span>
                    </label>

                    <select name="gender">

                        <option value="">Select gender</option>

                        <option value="male"
                            {{ old('gender') === 'male' ? 'selected' : '' }}>
                            Male
                        </option>

                        <option value="female"
                            {{ old('gender') === 'female' ? 'selected' : '' }}>
                            Female
                        </option>

                        <option value="other"
                            {{ old('gender') === 'other' ? 'selected' : '' }}>
                            Other
                        </option>

                    </select>

                    @error('gender')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>School Name</label>

                    <input
                        type="text"
                        name="school_name"
                        value="{{ old('school_name') }}"
                        placeholder="Enter school name"
                    >
                </div>

                <div class="form-group">
                    <label>Grade</label>

                    <input
                        type="text"
                        name="grade"
                        value="{{ old('grade') }}"
                        placeholder="Example: Grade 8"
                    >
                </div>

                <div class="form-group">
                    <label>District</label>

                    <input
                        type="text"
                        name="district"
                        value="{{ old('district') }}"
                        placeholder="Enter district"
                    >
                </div>
                <div class="form-group">
    <label>GN Division</label>

    <input
        type="text"
        name="gn_division"
        value="{{ old('gn_division') }}"
        placeholder="Enter GN Division"
    >

    @error('gn_division')
        <div class="error">{{ $message }}</div>
    @enderror
</div>
                <div class="form-group full-width">
                    <label>Address</label>

                    <textarea
                        name="address"
                        placeholder="Enter child's address"
                    >{{ old('address') }}</textarea>
                </div>

                <div class="form-group full-width">
                    <label>Notes</label>

                    <textarea
                        name="notes"
                        placeholder="Additional information about the child"
                    >{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="buttons">

                <a
                    href="{{ route('children.index') }}"
                    class="btn cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn save-btn"
                >
                    Save Child
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>