<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Add New Gown
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Add a gown to your rental inventory.
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
                        action="{{ route('owner.gowns.store') }}"
                        enctype="multipart/form-data"
                    >

                        @csrf


                        {{-- BASIC INFORMATION --}}

                        <div class="mb-8">

                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-5">
                                Basic Information
                            </h3>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


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
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}
                                            >
                                                {{ $category->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- Gown Code --}}

                                <div>

                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Gown Code
                                    </label>

                                    <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-500">
                                        Automatically generated
                                    </div>

                                    <p class="text-xs text-gray-500 mt-1">
                                        The system will automatically assign a unique gown code.
                                    </p>

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
                                        value="{{ old('name') }}"
                                        placeholder="Example: Elegant Princess Gown"
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
                                        value="{{ old('size') }}"
                                        placeholder="Example: Medium"
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
                                        value="{{ old('color') }}"
                                        placeholder="Example: Navy Blue"
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
                                        value="{{ old('style') }}"
                                        placeholder="Example: Ball Gown"
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
                                        value="{{ old('rental_price') }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
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
                                        value="{{ old('purchase_price') }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>

                            </div>

                        </div>


                        {{-- DESCRIPTION --}}

                        <div class="mb-8">

                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-5">
                                Description & Measurements
                            </h3>


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
                                    placeholder="Describe the gown..."
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                >{{ old('description') }}</textarea>

                            </div>


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
                                    placeholder="Example: Bust: 34 in, Waist: 28 in, Length: 58 in"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                >{{ old('measurements') }}</textarea>

                            </div>

                        </div>


                        {{-- CONDITION AND STATUS --}}

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

                                        <option value="">
                                            Select Condition
                                        </option>

                                        <option
                                            value="excellent"
                                            {{ old('condition') === 'excellent' ? 'selected' : '' }}
                                        >
                                            Excellent
                                        </option>

                                        <option
                                            value="good"
                                            {{ old('condition') === 'good' ? 'selected' : '' }}
                                        >
                                            Good
                                        </option>

                                        <option
                                            value="fair"
                                            {{ old('condition') === 'fair' ? 'selected' : '' }}
                                        >
                                            Fair
                                        </option>

                                        <option
                                            value="damaged"
                                            {{ old('condition') === 'damaged' ? 'selected' : '' }}
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
                                            value="under_maintenance"
                                            {{ old('status') === 'under_maintenance' ? 'selected' : '' }}
                                        >
                                            Under Maintenance
                                        </option>

                                        <option
                                            value="damaged"
                                            {{ old('status') === 'damaged' ? 'selected' : '' }}
                                        >
                                            Damaged
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
                                        value="{{ old('date_purchased') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    >

                                </div>


                                {{-- Image --}}

                                <div>

                                    <label
                                        for="image"
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Gown Image
                                    </label>

                                    <input
                                        type="file"
                                        id="image"
                                        name="image"
                                        accept=".jpg,.jpeg,.png,.webp"
                                        class="w-full border border-gray-300 rounded-md p-2 bg-white"
                                    >

                                    <p class="text-xs text-gray-500 mt-1">
                                        JPG, JPEG, PNG, or WEBP. Maximum 5MB.
                                    </p>

                                </div>

                            </div>

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
                                Save Gown
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
