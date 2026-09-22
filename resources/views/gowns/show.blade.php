<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gown Details
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    View complete information about this gown.
                </p>
            </div>

            <a
                href="{{ route('owner.gowns.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent
                       rounded-md font-semibold text-xs text-white uppercase tracking-widest
                       hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800
                       focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
                       transition ease-in-out duration-150"
            >
                ← Back to Gowns
            </a>

        </div>
    </x-slot>


    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))

                <div class="mb-6 bg-green-100 border border-green-300 text-green-800
                            px-4 py-3 rounded-md">

                    {{ session('success') }}

                </div>

            @endif


            {{-- VALIDATION / ERROR MESSAGE --}}
            @if($errors->any())

                <div class="mb-6 bg-red-100 border border-red-300 text-red-800
                            px-4 py-3 rounded-md">

                    <ul class="list-disc list-inside">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6">


                    {{-- ===================================================== --}}
                    {{-- TOP SECTION --}}
                    {{-- ===================================================== --}}

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">


                        {{-- ================================================= --}}
                        {{-- GOWN IMAGE --}}
                        {{-- ================================================= --}}

                        <div class="lg:col-span-1">

                            <div class="border rounded-lg overflow-hidden bg-gray-100">

                                @if($gown->image)

                                    <img
                                        src="{{ asset('storage/' . $gown->image) }}"
                                        alt="{{ $gown->name }}"
                                        class="w-full h-96 object-cover"
                                    >

                                @else

                                    <div class="w-full h-96 flex items-center justify-center">

                                        <div class="text-center text-gray-400">

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="mx-auto h-20 w-20 mb-3"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>

                                            <p class="text-sm">
                                                No image available
                                            </p>

                                        </div>

                                    </div>

                                @endif

                            </div>


                            {{-- ACTION BUTTONS --}}

                            <div class="grid grid-cols-2 gap-3 mt-4">

                                <a
                                    href="{{ route('owner.gowns.edit', $gown) }}"
                                    class="inline-flex justify-center items-center px-4 py-2
                                           bg-indigo-600 border border-transparent rounded-md
                                           font-semibold text-xs text-white uppercase
                                           tracking-widest hover:bg-indigo-700
                                           focus:bg-indigo-700 active:bg-indigo-800
                                           focus:outline-none focus:ring-2
                                           focus:ring-indigo-500 focus:ring-offset-2
                                           transition ease-in-out duration-150"
                                >
                                    Edit Gown
                                </a>


                                <form
                                    action="{{ route('owner.gowns.destroy', $gown) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to retire this gown?');"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="w-full inline-flex justify-center items-center
                                               px-4 py-2 bg-red-600 border border-transparent
                                               rounded-md font-semibold text-xs text-white
                                               uppercase tracking-widest hover:bg-red-700
                                               focus:bg-red-700 active:bg-red-800
                                               focus:outline-none focus:ring-2
                                               focus:ring-red-500 focus:ring-offset-2
                                               transition ease-in-out duration-150"
                                    >
                                        Retire Gown
                                    </button>

                                </form>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- BASIC GOWN INFORMATION --}}
                        {{-- ================================================= --}}

                        <div class="lg:col-span-2">

                            <div class="flex items-start justify-between mb-6">

                                <div>

                                    <p class="text-sm text-gray-500 uppercase tracking-wide">
                                        Gown Code
                                    </p>

                                    <h1 class="text-3xl font-bold text-gray-800">
                                        {{ $gown->gown_code }}
                                    </h1>

                                    <p class="text-xl text-gray-600 mt-1">
                                        {{ $gown->name }}
                                    </p>

                                </div>


                                {{-- STATUS --}}

                                @php

                                    $statusClasses = [
                                        'available' => 'bg-green-100 text-green-800',
                                        'reserved' => 'bg-yellow-100 text-yellow-800',
                                        'rented' => 'bg-blue-100 text-blue-800',
                                        'for_cleaning' => 'bg-purple-100 text-purple-800',
                                        'under_maintenance' => 'bg-orange-100 text-orange-800',
                                        'damaged' => 'bg-red-100 text-red-800',
                                        'unavailable' => 'bg-gray-100 text-gray-800',
                                        'retired' => 'bg-gray-200 text-gray-600',
                                    ];

                                    $statusClass =
                                        $statusClasses[$gown->status]
                                        ?? 'bg-gray-100 text-gray-800';

                                @endphp


                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full
                                           text-sm font-semibold {{ $statusClass }}"
                                >
                                    {{ ucwords(str_replace('_', ' ', $gown->status)) }}
                                </span>

                            </div>


                            {{-- INFORMATION GRID --}}

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                                {{-- CATEGORY --}}

                                <div class="border rounded-lg p-4">

                                    <p class="text-sm text-gray-500">
                                        Category
                                    </p>

                                    <p class="text-base font-semibold text-gray-800 mt-1">

                                        {{ $gown->category?->name ?? 'No Category' }}

                                    </p>

                                </div>


                                {{-- SIZE --}}

                                <div class="border rounded-lg p-4">

                                    <p class="text-sm text-gray-500">
                                        Size
                                    </p>

                                    <p class="text-base font-semibold text-gray-800 mt-1">

                                        {{ $gown->size }}

                                    </p>

                                </div>


                                {{-- COLOR --}}

                                <div class="border rounded-lg p-4">

                                    <p class="text-sm text-gray-500">
                                        Color
                                    </p>

                                    <p class="text-base font-semibold text-gray-800 mt-1">

                                        {{ $gown->color }}

                                    </p>

                                </div>


                                {{-- STYLE --}}

                                <div class="border rounded-lg p-4">

                                    <p class="text-sm text-gray-500">
                                        Style
                                    </p>

                                    <p class="text-base font-semibold text-gray-800 mt-1">

                                        {{ $gown->style ?: 'Not specified' }}

                                    </p>

                                </div>


                                {{-- CONDITION --}}

                                <div class="border rounded-lg p-4">

                                    <p class="text-sm text-gray-500">
                                        Condition
                                    </p>

                                    <p class="text-base font-semibold text-gray-800 mt-1">

                                        {{ ucfirst($gown->condition) }}

                                    </p>

                                </div>


                                {{-- DATE PURCHASED --}}

                                <div class="border rounded-lg p-4">

                                    <p class="text-sm text-gray-500">
                                        Date Purchased
                                    </p>

                                    <p class="text-base font-semibold text-gray-800 mt-1">

                                        {{ $gown->date_purchased
                                            ? $gown->date_purchased->format('F d, Y')
                                            : 'Not specified'
                                        }}

                                    </p>

                                </div>

                            </div>


                            {{-- ================================================= --}}
                            {{-- PRICING --}}
                            {{-- ================================================= --}}

                            <div class="mt-6">

                                <h3 class="text-lg font-semibold text-gray-800
                                           border-b pb-3 mb-4">

                                    Pricing

                                </h3>


                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                                    {{-- RENTAL PRICE --}}

                                    <div class="border rounded-lg p-4 bg-green-50">

                                        <p class="text-sm text-gray-500">
                                            Rental Price
                                        </p>

                                        <p class="text-2xl font-bold text-green-700 mt-1">

                                            ₱{{ number_format($gown->rental_price, 2) }}

                                        </p>

                                    </div>


                                    {{-- PURCHASE PRICE --}}

                                    <div class="border rounded-lg p-4 bg-blue-50">

                                        <p class="text-sm text-gray-500">
                                            Purchase Price
                                        </p>

                                        <p class="text-2xl font-bold text-blue-700 mt-1">

                                            @if($gown->purchase_price !== null)

                                                ₱{{ number_format($gown->purchase_price, 2) }}

                                            @else

                                                Not specified

                                            @endif

                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- DESCRIPTION --}}
                    {{-- ===================================================== --}}

                    <div class="mt-8">

                        <h3 class="text-lg font-semibold text-gray-800
                                   border-b pb-3 mb-4">

                            Description

                        </h3>

                        <div class="border rounded-lg p-5 bg-gray-50">

                            @if($gown->description)

                                <p class="text-gray-700 whitespace-pre-line">
                                    {{ $gown->description }}
                                </p>

                            @else

                                <p class="text-gray-400">
                                    No description provided.
                                </p>

                            @endif

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- MEASUREMENTS --}}
                    {{-- ===================================================== --}}

                    <div class="mt-6">

                        <h3 class="text-lg font-semibold text-gray-800
                                   border-b pb-3 mb-4">

                            Measurements

                        </h3>

                        <div class="border rounded-lg p-5 bg-gray-50">

                            @if($gown->measurements)

                                <p class="text-gray-700 whitespace-pre-line">
                                    {{ $gown->measurements }}
                                </p>

                            @else

                                <p class="text-gray-400">
                                    No measurements provided.
                                </p>

                            @endif

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- ACCESSORIES --}}
                    {{-- ===================================================== --}}

                    <div class="mt-8">

                        <div class="flex items-center justify-between
                                    border-b pb-3 mb-4">

                            <h3 class="text-lg font-semibold text-gray-800">

                                Accessories

                            </h3>

                            <span class="text-sm text-gray-500">

                                {{ $gown->accessories->count() }}
                                {{ $gown->accessories->count() === 1 ? 'item' : 'items' }}

                            </span>

                        </div>


                        @if($gown->accessories->count())

                            <div class="overflow-x-auto">

                                <table class="min-w-full divide-y divide-gray-200">

                                    <thead class="bg-gray-50">

                                        <tr>

                                            <th
                                                class="px-6 py-3 text-left text-xs
                                                       font-medium text-gray-500
                                                       uppercase tracking-wider"
                                            >
                                                Accessory
                                            </th>

                                            <th
                                                class="px-6 py-3 text-left text-xs
                                                       font-medium text-gray-500
                                                       uppercase tracking-wider"
                                            >
                                                Description
                                            </th>

                                            <th
                                                class="px-6 py-3 text-center text-xs
                                                       font-medium text-gray-500
                                                       uppercase tracking-wider"
                                            >
                                                Quantity
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody class="bg-white divide-y divide-gray-200">

                                        @foreach($gown->accessories as $accessory)

                                            <tr>

                                                <td class="px-6 py-4 whitespace-nowrap">

                                                    <div class="font-semibold text-gray-800">

                                                        {{ $accessory->name }}

                                                    </div>

                                                </td>


                                                <td class="px-6 py-4">

                                                    <div class="text-sm text-gray-600">

                                                        {{ $accessory->description ?? 'No description' }}

                                                    </div>

                                                </td>


                                                <td class="px-6 py-4 text-center">

                                                    <span
                                                        class="inline-flex items-center
                                                               px-3 py-1 rounded-full
                                                               bg-gray-100 text-gray-800
                                                               text-sm font-semibold"
                                                    >

                                                        {{ $accessory->pivot->quantity ?? 1 }}

                                                    </span>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        @else

                            <div class="border rounded-lg p-6 text-center bg-gray-50">

                                <p class="text-gray-500">
                                    No accessories are currently assigned to this gown.
                                </p>

                            </div>

                        @endif

                    </div>


                    {{-- ===================================================== --}}
                    {{-- RESERVATION HISTORY --}}
                    {{-- ===================================================== --}}

                    <div class="mt-8">

                        <div class="flex items-center justify-between
                                    border-b pb-3 mb-4">

                            <h3 class="text-lg font-semibold text-gray-800">

                                Reservation History

                            </h3>

                            <span class="text-sm text-gray-500">

                                {{ $gown->reservationItems->count() }}
                                {{ $gown->reservationItems->count() === 1 ? 'record' : 'records' }}

                            </span>

                        </div>


                        @if($gown->reservationItems->count())

                            <div class="overflow-x-auto">

                                <table class="min-w-full divide-y divide-gray-200">

                                    <thead class="bg-gray-50">

                                        <tr>

                                            <th
                                                class="px-6 py-3 text-left text-xs
                                                       font-medium text-gray-500
                                                       uppercase tracking-wider"
                                            >
                                                Reservation ID
                                            </th>

                                            <th
                                                class="px-6 py-3 text-center text-xs
                                                       font-medium text-gray-500
                                                       uppercase tracking-wider"
                                            >
                                                Quantity
                                            </th>

                                            <th
                                                class="px-6 py-3 text-right text-xs
                                                       font-medium text-gray-500
                                                       uppercase tracking-wider"
                                            >
                                                Price
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody class="bg-white divide-y divide-gray-200">

                                        @foreach($gown->reservationItems as $item)

                                            <tr>

                                                <td class="px-6 py-4">

                                                    <span class="font-medium text-gray-800">

                                                        #{{ $item->reservation_id }}

                                                    </span>

                                                </td>


                                                <td class="px-6 py-4 text-center">

                                                    {{ $item->quantity ?? 1 }}

                                                </td>


                                                <td class="px-6 py-4 text-right">

                                                    ₱{{ number_format($item->price ?? 0, 2) }}

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        @else

                            <div class="border rounded-lg p-6 text-center bg-gray-50">

                                <p class="text-gray-500">
                                    This gown has not been included in any reservations yet.
                                </p>

                            </div>

                        @endif

                    </div>


                    {{-- ===================================================== --}}
                    {{-- SYSTEM INFORMATION --}}
                    {{-- ===================================================== --}}

                    <div class="mt-8">

                        <h3 class="text-lg font-semibold text-gray-800
                                   border-b pb-3 mb-4">

                            System Information

                        </h3>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                            <div class="border rounded-lg p-4">

                                <p class="text-sm text-gray-500">
                                    Gown ID
                                </p>

                                <p class="font-semibold text-gray-800 mt-1">

                                    {{ $gown->id }}

                                </p>

                            </div>


                            <div class="border rounded-lg p-4">

                                <p class="text-sm text-gray-500">
                                    Created At
                                </p>

                                <p class="font-semibold text-gray-800 mt-1">

                                    {{ $gown->created_at
                                        ? $gown->created_at->format('F d, Y h:i A')
                                        : 'N/A'
                                    }}

                                </p>

                            </div>


                            <div class="border rounded-lg p-4">

                                <p class="text-sm text-gray-500">
                                    Last Updated
                                </p>

                                <p class="font-semibold text-gray-800 mt-1">

                                    {{ $gown->updated_at
                                        ? $gown->updated_at->format('F d, Y h:i A')
                                        : 'N/A'
                                    }}

                                </p>

                            </div>


                            <div class="border rounded-lg p-4">

                                <p class="text-sm text-gray-500">
                                    Current Status
                                </p>

                                <p class="font-semibold text-gray-800 mt-1">

                                    {{ ucwords(str_replace('_', ' ', $gown->status)) }}

                                </p>

                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>

</x-app-layout>
