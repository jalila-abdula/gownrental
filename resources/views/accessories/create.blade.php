<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Accessory</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f9fc;
            color: #263238;
        }

        .container {
            width: 92%;
            max-width: 850px;
            margin: 40px auto;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin-top: 7px;
            color: #78909c;
        }

        .back {
            display: inline-block;
            margin-bottom: 18px;
            color: #607d8b;
            text-decoration: none;
            font-size: 14px;
        }

        .card {
            background: white;
            border: 1px solid #e0e6eb;
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #455a64;
        }

        .required {
            color: #d9534f;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #cfd8dc;
            font-family: Arial, sans-serif;
            font-size: 14px;
            background: white;
            color: #263238;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #6fa8dc;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .help {
            margin-top: 6px;
            font-size: 12px;
            color: #90a4ae;
        }

        .error-box {
            margin-bottom: 22px;
            padding: 15px 18px;
            background: #ffebee;
            border-left: 4px solid #d9534f;
            color: #c62828;
        }

        .error-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .field-error {
            margin-top: 6px;
            font-size: 12px;
            color: #d9534f;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 28px;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .button-primary {
            background: #263238;
            color: white;
        }

        .button-secondary {
            background: #90a4ae;
            color: white;
        }
    </style>
</head>

<body>

<div class="container">

    <a
        href="{{ route('owner.accessories.index') }}"
        class="back"
    >
        ← Back to Accessories
    </a>

    <div class="header">

        <h1>Add Accessory</h1>

        <p>
            Add an accessory that can be used with your gowns.
        </p>

    </div>

    <div class="card">

        @if($errors->any())

            <div class="error-box">

                <strong>
                    Please correct the following:
                </strong>

                <ul>

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif

        <form
            action="{{ route('owner.accessories.store') }}"
            method="POST"
        >

            @csrf

            <div class="form-group">

                <label for="name">
                    Accessory Name
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Example: Pearl Necklace"
                    required
                >

                @error('name')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Describe the accessory..."
                >{{ old('description') }}</textarea>

                @error('description')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="form-group">

                <label for="quantity">
                    Quantity
                    <span class="required">*</span>
                </label>

                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    value="{{ old('quantity', 0) }}"
                    min="0"
                    required
                >

                @error('quantity')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="form-group">

                <label for="replacement_cost">
                    Replacement Cost
                </label>

                <input
                    type="number"
                    id="replacement_cost"
                    name="replacement_cost"
                    value="{{ old('replacement_cost') }}"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                >

                @error('replacement_cost')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="form-group">

                <label for="status">
                    Status
                    <span class="required">*</span>
                </label>

                <select
                    id="status"
                    name="status"
                    required
                >

                    <option
                        value="available"
                        {{ old('status', 'available') === 'available' ? 'selected' : '' }}
                    >
                        Available
                    </option>

                    <option
                        value="unavailable"
                        {{ old('status') === 'unavailable' ? 'selected' : '' }}
                    >
                        Unavailable
                    </option>

                    <option
                        value="damaged"
                        {{ old('status') === 'damaged' ? 'selected' : '' }}
                    >
                        Damaged
                    </option>

                    <option
                        value="retired"
                        {{ old('status') === 'retired' ? 'selected' : '' }}
                    >
                        Retired
                    </option>

                </select>

                @error('status')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="actions">

                <button
                    type="submit"
                    class="button button-primary"
                >
                    Save Accessory
                </button>

                <a
                    href="{{ route('owner.accessories.index') }}"
                    class="button button-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
