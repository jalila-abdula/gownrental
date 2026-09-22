<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gown Inventory
            </h2>

            <a
                href="{{ route('owner.gowns.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md"
            >
                + Add Gown
            </a>

        </div>

    </x-slot>


    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))

                <div class="mb-6 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">

                    {{ session('success') }}

                </div>

            @endif


            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <div class="mb-6">

                        <h3 class="text-lg font-semibold text-gray-800">
                            Gowns
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Manage gown inventory, rental prices,
                            conditions, and availability.
                        </p>

                    </div>


                    @if($gowns->count())

                        <div class="overflow-x-auto">

                            <table class="w-full border-collapse">

                                <thead>

                                    <tr class="border-b bg-gray-50">

                                        <th class="text-left px-4 py-3">
                                            Code
                                        </th>

                                        <th class="text-left px-4 py-3">
                                            Gown
                                        </th>

                                        <th class="text-left px-4 py-3">
                                            Category
                                        </th>

                                        <th class="text-left px-4 py-3">
                                            Size
                                        </th>

                                        <th class="text-left px-4 py-3">
                                            Rental Price
                                        </th>

                                        <th class="text-left px-4 py-3">
                                            Condition
                                        </th>

                                        <th class="text-left px-4 py-3">
                                            Status
                                        </th>

                                        <th class="text-right px-4 py-3">
                                            Actions
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach($gowns as $gown)

                                        <tr class="border-b">

                                            <td class="px-4 py-4 font-medium">
                                                {{ $gown->gown_code }}
                                            </td>


                                            <td class="px-4 py-4">

                                                <div class="font-medium text-gray-900">
                                                    {{ $gown->name }}
                                                </div>

                                                <div class="text-sm text-gray-500">
                                                    {{ $gown->color }}
                                                </div>

                                            </td>


                                            <td class="px-4 py-4">
                                                {{ $gown->category->name ?? 'No Category' }}
                                            </td>


                                            <td class="px-4 py-4">
                                                {{ $gown->size }}
                                            </td>


                                            <td class="px-4 py-4">
                                                ₱{{ number_format($gown->rental_price, 2) }}
                                            </td>


                                            <td class="px-4 py-4 capitalize">
                                                {{ str_replace('_', ' ', $gown->condition) }}
                                            </td>


                                            <td class="px-4 py-4 capitalize">
                                                {{ str_replace('_', ' ', $gown->status) }}
                                            </td>


                                            <td class="px-4 py-4 text-right whitespace-nowrap">

                                                <a
                                                    href="{{ route('owner.gowns.show', $gown) }}"
                                                    class="text-blue-600 hover:underline mr-3"
                                                >
                                                    View
                                                </a>


                                                <a
                                                    href="{{ route('owner.gowns.edit', $gown) }}"
                                                    class="text-gray-700 hover:underline mr-3"
                                                >
                                                    Edit
                                                </a>


                                                @if($gown->status !== 'retired')

                                                    <form
                                                        action="{{ route('owner.gowns.destroy', $gown) }}"
                                                        method="POST"
                                                        class="inline"
                                                        onsubmit="return confirm('Are you sure you want to retire this gown?');"
                                                    >

                                                        @csrf

                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="text-red-600 hover:underline"
                                                        >
                                                            Retire
                                                        </button>

                                                    </form>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="text-center py-12">

                            <p class="text-gray-500 text-lg">
                                No gowns found.
                            </p>

                            <p class="text-gray-400 mt-2">
                                Add your first gown to begin managing
                                your inventory.
                            </p>

                            <a
                                href="{{ route('owner.gowns.create') }}"
                                class="inline-block mt-5 px-4 py-2 bg-gray-800 text-white rounded-md"
                            >
                                + Add First Gown
                            </a>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
