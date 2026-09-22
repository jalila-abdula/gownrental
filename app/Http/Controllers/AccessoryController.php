<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccessoryController extends Controller
{
    public function index()
    {
        $accessories = Accessory::withCount('gowns')
            ->latest()
            ->get();

        return view('accessories.index', compact('accessories'));
    }

    public function create()
    {
        return view('accessories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'replacement_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                Rule::in([
                    'available',
                    'unavailable',
                    'damaged',
                    'retired',
                ]),
            ],
        ]);

        Accessory::create($validated);

        return redirect()
            ->route('owner.accessories.index')
            ->with('success', 'Accessory added successfully.');
    }

    public function show(Accessory $accessory)
    {
        $accessory->load([
            'gowns.category',
        ]);

        return view('accessories.show', compact('accessory'));
    }

    public function edit(Accessory $accessory)
    {
        return view('accessories.edit', compact('accessory'));
    }

    public function update(Request $request, Accessory $accessory)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'replacement_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                Rule::in([
                    'available',
                    'unavailable',
                    'damaged',
                    'retired',
                ]),
            ],
        ]);

        $accessory->update($validated);

        return redirect()
            ->route('owner.accessories.index')
            ->with('success', 'Accessory updated successfully.');
    }

    public function destroy(Accessory $accessory)
    {
        $accessory->update([
            'status' => 'retired',
        ]);

        return redirect()
            ->route('owner.accessories.index')
            ->with('success', 'Accessory retired successfully.');
    }
}
