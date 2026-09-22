<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $accessory->name }}</title>

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
            max-width: 1050px;
            margin: 40px auto;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #607d8b;
            text-decoration: none;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 30px;
        }

        .header p {
            margin-top: 8px;
            color: #78909c;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            border: none;
            color: white;
            font-size: 13px;
            cursor: pointer;
        }

        .edit {
            background: #64a3c2;
        }

        .card {
            background: white;
            border: 1px solid #e0e6eb;
            padding: 25px;
            margin-bottom: 20px;
        }

        .card h2 {
            margin-top: 0;
            font-size: 19px;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .detail {
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 12px;
        }

        .label {
            display: block;
            font-size: 12px;
            color: #90a4ae;
            margin-bottom: 5px;
        }

        .value {
            font-size: 15px;
            color: #263238;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .available {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .unavailable {
            background: #fff3e0;
            color: #ef6c00;
        }

        .damaged {
            background: #ffebee;
            color: #c62828;
        }

        .retired {
            background: #eceff1;
            color: #546e7a;
        }

        .description {
            line-height: 1.7;
            color: #546e7a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #edf4f8;
            text-align: left;
            padding: 12px;
            font-size: 13px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eeeeee;
        }

        .empty {
            color: #78909c;
            padding: 15px 0;
        }

        @media (max-width: 700px) {
            .header {
                flex-direction: column;
            }

            .details {
                grid-template-columns: 1fr;
            }
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

        <div>

            <h1>
                {{ $accessory->name }}
            </h1>

            <p>
                Accessory Details
            </p>

        </div>

        <div class="actions">

            <a
                href="{{ route('owner.accessories.edit', $accessory) }}"
                class="button edit"
            >
                Edit Accessory
            </a>

        </div>

    </div>

    <div class="card">

        <h2>Accessory Information</h2>

        <div class="details">

            <div class="detail">

                <span class="label">
                    Name
                </span>

                <span class="value">
                    {{ $accessory->name }}
                </span>

            </div>

            <div class="detail">

                <span class="label">
                    Quantity
                </span>

                <span class="value">
                    {{ $accessory->quantity }}
                </span>

            </div>

            <div class="detail">

                <span class="label">
                    Replacement Cost
                </span>

                <span class="value">
                    ₱{{ number_format((float) $accessory->replacement_cost, 2) }}
                </span>

            </div>

            <div class="detail">

                <span class="label">
                    Status
                </span>

                <span class="status {{ $accessory->status }}">
                    {{ str_replace('_', ' ', $accessory->status) }}
                </span>

            </div>

        </div>

    </div>

    <div class="card">

        <h2>Description</h2>

        @if($accessory->description)

            <div class="description">
                {{ $accessory->description }}
            </div>

        @else

            <div class="empty">
                No description has been provided.
            </div>

        @endif

    </div>

    <div class="card">

        <h2>Gowns Using This Accessory</h2>

        @if($accessory->gowns->count())

            <table>

                <thead>

                    <tr>
                        <th>Gown Code</th>
                        <th>Gown Name</th>
                        <th>Category</th>
                        <th>Quantity Assigned</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($accessory->gowns as $gown)

                        <tr>

                            <td>
                                {{ $gown->gown_code }}
                            </td>

                            <td>
                                {{ $gown->name }}
                            </td>

                            <td>
                                {{ $gown->category->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $gown->pivot->quantity }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="empty">
                This accessory is not assigned to any gown yet.
            </div>

        @endif

    </div>

</div>

</body>
</html>
