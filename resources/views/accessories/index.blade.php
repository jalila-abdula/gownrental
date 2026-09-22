<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Accessories</title>

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
            max-width: 1250px;
            margin: 40px auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin: 6px 0 0;
            color: #78909c;
        }

        .button {
            display: inline-block;
            padding: 11px 18px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            background: #263238;
            color: white;
        }

        .button:hover {
            background: #37474f;
        }

        .alert {
            padding: 14px 18px;
            margin-bottom: 20px;
            background: #e8f5e9;
            border-left: 4px solid #66bb6a;
            color: #2e7d32;
        }

        .card {
            background: white;
            border: 1px solid #e0e6eb;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: #edf4f8;
            padding: 14px;
            font-size: 13px;
            color: #455a64;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eeeeee;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .status-available {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-unavailable {
            background: #fff3e0;
            color: #ef6c00;
        }

        .status-damaged {
            background: #ffebee;
            color: #c62828;
        }

        .status-retired {
            background: #eceff1;
            color: #546e7a;
        }

        .actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .actions a,
        .actions button {
            padding: 8px 11px;
            font-size: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            color: white;
        }

        .view {
            background: #78909c;
        }

        .edit {
            background: #64a3c2;
        }

        .retire {
            background: #d9534f;
        }

        .empty {
            text-align: center;
            padding: 45px 20px;
            color: #78909c;
        }

        .description {
            max-width: 250px;
            color: #607d8b;
        }

        .money {
            font-weight: bold;
        }

        @media (max-width: 900px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .card {
                overflow-x: auto;
            }

            table {
                min-width: 900px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">

        <div>
            <h1>Accessories</h1>

            <p>
                Manage accessories used with your gowns.
            </p>
        </div>

        <a
            href="{{ route('owner.accessories.create') }}"
            class="button"
        >
            + Add Accessory
        </a>

    </div>

    @if(session('success'))

        <div class="alert">
            {{ session('success') }}
        </div>

    @endif

    <div class="card">

        @if($accessories->count())

            <table>

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Accessory</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Replacement Cost</th>
                        <th>Assigned Gowns</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($accessories as $accessory)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <strong>
                                    {{ $accessory->name }}
                                </strong>
                            </td>

                            <td>
                                <div class="description">
                                    {{ $accessory->description ?: 'No description' }}
                                </div>
                            </td>

                            <td>
                                {{ $accessory->quantity }}
                            </td>

                            <td class="money">
                                ₱{{ number_format((float) $accessory->replacement_cost, 2) }}
                            </td>

                            <td>
                                {{ $accessory->gowns_count }}
                            </td>

                            <td>

                                <span
                                    class="status status-{{ $accessory->status }}"
                                >
                                    {{ str_replace('_', ' ', $accessory->status) }}
                                </span>

                            </td>

                            <td>

                                <div class="actions">

                                    <a
                                        href="{{ route('owner.accessories.show', $accessory) }}"
                                        class="view"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ route('owner.accessories.edit', $accessory) }}"
                                        class="edit"
                                    >
                                        Edit
                                    </a>

                                    @if($accessory->status !== 'retired')

                                        <form
                                            action="{{ route('owner.accessories.destroy', $accessory) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to retire this accessory?');"
                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="retire"
                                            >
                                                Retire
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="empty">

                <h3>No accessories found</h3>

                <p>
                    You have not added any accessories yet.
                </p>

                <a
                    href="{{ route('owner.accessories.create') }}"
                    class="button"
                >
                    Add Your First Accessory
                </a>

            </div>

        @endif

    </div>

</div>

</body>
</html>
