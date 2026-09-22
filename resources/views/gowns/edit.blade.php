<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Gown
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Update the gown's inventory information.
                </p>

            </div>

            <a
                href="{{ route('owner.gowns.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md"
            >
                ← Back to Gowns
            </a>

        </div>

    </x-slot>


    <div class="py-12">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">


                    {{-- Validation Errors --}}

                    @if ($errors->any())

                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-4 rounded">

                            <div class="font-semibold mb-2">
                                Please correct the following errors:
                            </div>

                            <ul class="list-disc list-inside text-sm">

                                @foreach ($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    <form
                        method="POST"
                        action="{{ route('owner.gowns.update', $gown) }}"
                        enctype="multipart/form-data"
                    >

                        @csrf

                        @method('PUT')


                        {{-- BASIC INFORMATION --}}

                        <div class="mb-8">

                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-5">
                                Basic Information
                            </h3>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                                {{-- Gown Code --}}

                                <div>

                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Gown Code
                                    </label>

                                    <div
                                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-600"
                                    >
                                        {{ $gown->gown_code }}
                                    </div>

                                    <p class="text-xs text-gray-500 mt-1">
                                        Gown codes are automatically generated and cannot be changed.
                                    </p>

                                </div>


                                {{-- Category --}}

                                <div>

                                    <label
                                        for="category_id"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Category
                                    </label>

                                    <select
                                        id="category_id"
                                        name="category_id"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                        <option value="">
                                            Select Category
                                        </option>

                                        @foreach($categories as $category)

                                            <option
                                                value="{{ $category->id }}"
                                                {{ old('category_id', $gown->category_id) == $category->id ? 'selected' : '' }}
                                            >
                                                {{ $category->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- Gown Name --}}

                                <div>

                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Gown Name
                                    </label>

                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name', $gown->name) }}"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>


                                {{-- Size --}}

                                <div>

                                    <label
                                        for="size"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Size
                                    </label>

                                    <input
                                        type="text"
                                        id="size"
                                        name="size"
                                        value="{{ old('size', $gown->size) }}"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>


                                {{-- Color --}}

                                <div>

                                    <label
                                        for="color"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Color
                                    </label>

                                    <input
                                        type="text"
                                        id="color"
                                        name="color"
                                        value="{{ old('color', $gown->color) }}"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>


                                {{-- Style --}}

                                <div>

                                    <label
                                        for="style"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Style
                                    </label>

                                    <input
                                        type="text"
                                        id="style"
                                        name="style"
                                        value="{{ old('style', $gown->style) }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>

                            </div>

                        </div>


                        {{-- PRICING --}}

                        <div class="mb-8">

                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-5">
                                Rental & Purchase Information
                            </h3>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                                {{-- Rental Price --}}

                                <div>

                                    <label
                                        for="rental_price"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Rental Price
                                    </label>

                                    <input
                                        type="number"
                                        id="rental_price"
                                        name="rental_price"
                                        value="{{ old('rental_price', $gown->rental_price) }}"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>


                                {{-- Purchase Price --}}

                                <div>

                                    <label
                                        for="purchase_price"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Purchase Price
                                    </label>

                                    <input
                                        type="number"
                                        id="purchase_price"
                                        name="purchase_price"
                                        value="{{ old('purchase_price', $gown->purchase_price) }}"
                                        min="0"
                                        step="0.01"
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>

                            </div>

                        </div>


                        {{-- DESCRIPTION & MEASUREMENTS --}}

                        <div class="mb-8">

                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-5">
                                Description & Measurements
                            </h3>


                            {{-- Description --}}

                            <div class="mb-6">

                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Description
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                >{{ old('description', $gown->description) }}</textarea>

                            </div>


                            {{-- Measurements --}}

                            <div>

                                <label
                                    for="measurements"
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Measurements
                                </label>

                                <textarea
                                    id="measurements"
                                    name="measurements"
                                    rows="4"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                >{{ old('measurements', $gown->measurements) }}</textarea>

                            </div>

                        </div>


                        {{-- CONDITION & STATUS --}}

                        <div class="mb-8">

                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-5">
                                Condition & Availability
                            </h3>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                                {{-- Condition --}}

                                <div>

                                    <label
                                        for="condition"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Condition
                                    </label>

                                    <select
                                        id="condition"
                                        name="condition"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                        <option
                                            value="excellent"
                                            {{ old('condition', $gown->condition) === 'excellent' ? 'selected' : '' }}
                                        >
                                            Excellent
                                        </option>

                                        <option
                                            value="good"
                                            {{ old('condition', $gown->condition) === 'good' ? 'selected' : '' }}
                                        >
                                            Good
                                        </option>

                                        <option
                                            value="fair"
                                            {{ old('condition', $gown->condition) === 'fair' ? 'selected' : '' }}
                                        >
                                            Fair
                                        </option>

                                        <option
                                            value="damaged"
                                            {{ old('condition', $gown->condition) === 'damaged' ? 'selected' : '' }}
                                        >
                                            Damaged
                                        </option>

                                    </select>

                                </div>


                                {{-- Status --}}

                                <div>

                                    <label
                                        for="status"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Status
                                    </label>

                                    <select
                                        id="status"
                                        name="status"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                        <option
                                            value="available"
                                            {{ old('status', $gown->status) === 'available' ? 'selected' : '' }}
                                        >
                                            Available
                                        </option>

                                        <option
                                            value="reserved"
                                            {{ old('status', $gown->status) === 'reserved' ? 'selected' : '' }}
                                        >
                                            Reserved
                                        </option>

                                        <option
                                            value="rented"
                                            {{ old('status', $gown->status) === 'rented' ? 'selected' : '' }}
                                        >
                                            Rented
                                        </option>

                                        <option
                                            value="for_cleaning"
                                            {{ old('status', $gown->status) === 'for_cleaning' ? 'selected' : '' }}
                                        >
                                            For Cleaning
                                        </option>

                                        <option
                                            value="under_maintenance"
                                            {{ old('status', $gown->status) === 'under_maintenance' ? 'selected' : '' }}
                                        >
                                            Under Maintenance
                                        </option>

                                        <option
                                            value="damaged"
                                            {{ old('status', $gown->status) === 'damaged' ? 'selected' : '' }}
                                        >
                                            Damaged
                                        </option>

                                        <option
                                            value="unavailable"
                                            {{ old('status', $gown->status) === 'unavailable' ? 'selected' : '' }}
                                        >
                                            Unavailable
                                        </option>

                                        <option
                                            value="retired"
                                            {{ old('status', $gown->status) === 'retired' ? 'selected' : '' }}
                                        >
                                            Retired
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>


                        {{-- PURCHASE INFORMATION --}}

                        <div class="mb-8">

                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-5">
                                Purchase Information
                            </h3>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                                {{-- Date Purchased --}}

                                <div>

                                    <label
                                        for="date_purchased"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Date Purchased
                                    </label>

                                    <input
                                        type="date"
                                        id="date_purchased"
                                        name="date_purchased"
                                        value="{{ old('date_purchased', optional($gown->date_purchased)->format('Y-m-d')) }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>


                                {{-- New Image --}}

                                <div>

                                    <label
                                        for="image"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Replace Gown Image
                                    </label>

                                    <input
                                        type="file"
                                        id="image"
                                        name="image"
                                        accept=".jpg,.jpeg,.png,.webp"
                                        class="w-full border border-gray-300 rounded-md p-2 bg-white"
                                    >

                                    <p class="text-xs text-gray-500 mt-1">
                                        Leave empty to keep the current image.
                                    </p>

                                </div>

                            </div>


                            {{-- Current Image --}}

                            @if($gown->image)

                                <div class="mt-6">

                                    <p class="text-sm font-medium text-gray-700 mb-2">
                                        Current Gown Image
                                    </p>

                                    <div class="w-48 h-64 border rounded-md overflow-hidden bg-gray-100">

                                        <img
                                            src="{{ asset('storage/' . $gown->image) }}"
                                            alt="{{ $gown->name }}"
                                            class="w-full h-full object-cover"
                                        >

                                    </div>

                                </div>

                            @endif

                        </div>


                        {{-- BUTTONS --}}

                        <div class="flex items-center justify-end gap-3 border-t pt-6">

                            <a
                                href="{{ route('owner.gowns.index') }}"
                                class="px-5 py-2 bg-gray-200 text-gray-700 rounded-md"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="px-5 py-2 bg-gray-800 text-white rounded-md"
                            >
                                Update Gown
                            </button>

                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
