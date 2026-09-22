<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Category Details
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <div class="mb-6">

                        <h3 class="text-2xl font-bold text-gray-800">
                            {{ $category->name }}
                        </h3>

                        <p class="mt-2 text-gray-600">
                            {{ $category->description ?? 'No description provided.' }}
                        </p>

                    </div>

                    <div class="border-t pt-4 space-y-3">

                        <div>
                            <strong>Status:</strong>

                            @if($category->is_active)
                                <span class="text-green-700">
                                    Active
                                </span>
                            @else
                                <span class="text-red-700">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <div>
                            <strong>Created:</strong>
                            {{ $category->created_at->format('F d, Y h:i A') }}
                        </div>

                        <div>
                            <strong>Last Updated:</strong>
                            {{ $category->updated_at->format('F d, Y h:i A') }}
                        </div>

                    </div>

                    <div class="mt-6 flex gap-3">

                        <a
                            href="{{ route('owner.categories.edit', $category) }}"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md"
                        >
                            Edit
                        </a>

                        <a
                            href="{{ route('owner.categories.index') }}"
                            class="px-4 py-2 border border-gray-300 rounded-md"
                        >
                            Back
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
