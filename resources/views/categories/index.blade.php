<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Category Management
            </h2>

            <a
                href="{{ route('owner.categories.create') }}"
                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
            >
                + Add Category
            </a>
        </div>
    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Categories Table --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">

                <div class="p-6">

                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Categories
                    </h3>

                    @if($categories->count())

                        <div class="overflow-x-auto">

                            <table class="w-full border-collapse">

                                <thead>
                                    <tr class="border-b bg-gray-50">

                                        <th class="text-left px-4 py-3">
                                            Name
                                        </th>

                                        <th class="text-left px-4 py-3">
                                            Description
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

                                    @foreach($categories as $category)

                                        <tr class="border-b">

                                            <td class="px-4 py-3 font-medium">
                                                {{ $category->name }}
                                            </td>

                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $category->description ?? '—' }}
                                            </td>

                                            <td class="px-4 py-3">

                                                @if($category->is_active)

                                                    <span class="text-green-700">
                                                        Active
                                                    </span>

                                                @else

                                                    <span class="text-red-700">
                                                        Inactive
                                                    </span>

                                                @endif

                                            </td>

                                            <td class="px-4 py-3 text-right">

                                                <a
                                                    href="{{ route('owner.categories.show', $category) }}"
                                                    class="text-blue-600 hover:underline mr-3"
                                                >
                                                    View
                                                </a>

                                                <a
                                                    href="{{ route('owner.categories.edit', $category) }}"
                                                    class="text-gray-700 hover:underline mr-3"
                                                >
                                                    Edit
                                                </a>

                                                @if($category->is_active)

                                                    <form
                                                        action="{{ route('owner.categories.destroy', $category) }}"
                                                        method="POST"
                                                        class="inline"
                                                        onsubmit="return confirm('Are you sure you want to deactivate this category?');"
                                                    >

                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="text-red-600 hover:underline"
                                                        >
                                                            Deactivate
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

                        <div class="text-center py-10 text-gray-500">

                            <p class="mb-4">
                                No categories found.
                            </p>

                            <a
                                href="{{ route('owner.categories.create') }}"
                                class="text-blue-600 hover:underline"
                            >
                                Create your first category
                            </a>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
