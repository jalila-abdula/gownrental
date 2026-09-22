<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Category
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <form
                        method="POST"
                        action="{{ route('owner.categories.update', $category) }}"
                    >

                        @csrf
                        @method('PUT')

                        {{-- Name --}}
                        <div>

                            <label
                                for="name"
                                class="block font-medium text-sm text-gray-700"
                            >
                                Category Name
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $category->name) }}"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >

                            @error('name')
                                <p class="text-red-600 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- Description --}}
                        <div class="mt-4">

                            <label
                                for="description"
                                class="block font-medium text-sm text-gray-700"
                            >
                                Description
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >{{ old('description', $category->description) }}</textarea>

                            @error('description')
                                <p class="text-red-600 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- Active --}}
                        <div class="mt-4">

                            <label class="inline-flex items-center">

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ $category->is_active ? 'checked' : '' }}
                                    class="rounded border-gray-300"
                                >

                                <span class="ms-2 text-sm text-gray-600">
                                    Active Category
                                </span>

                            </label>

                        </div>

                        {{-- Buttons --}}
                        <div class="mt-6 flex gap-3">

                            <button
                                type="submit"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md"
                            >
                                Update Category
                            </button>

                            <a
                                href="{{ route('owner.categories.index') }}"
                                class="px-4 py-2 border border-gray-300 rounded-md"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
