<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Gown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GownController extends Controller
{
    public function index()
    {
        $gowns = Gown::with('category')
            ->latest()
            ->get();

        return view('gowns.index', compact('gowns'));
    }


    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('gowns.create', compact('categories'));
    }


    private function generateGownCode(): string
    {
        $lastGown = Gown::orderByDesc('id')->first();

        if (!$lastGown) {
            return 'GWN-0001';
        }

        $lastNumber = (int) str_replace(
            'GWN-',
            '',
            $lastGown->gown_code
        );

        $nextNumber = $lastNumber + 1;

        return 'GWN-' . str_pad(
            $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id'
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'size' => [
                'required',
                'string',
                'max:50'
            ],

            'color' => [
                'required',
                'string',
                'max:100'
            ],

            'style' => [
                'nullable',
                'string',
                'max:255'
            ],

            'rental_price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'purchase_price' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'measurements' => [
                'nullable',
                'string'
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120'
            ],

            'condition' => [
                'required',
                Rule::in([
                    'excellent',
                    'good',
                    'fair',
                    'damaged'
                ])
            ],

            'status' => [
                'required',
                Rule::in([
                    'available',
                    'reserved',
                    'rented',
                    'for_cleaning',
                    'under_maintenance',
                    'damaged',
                    'unavailable',
                    'retired'
                ])
            ],

            'date_purchased' => [
                'nullable',
                'date'
            ],
        ]);


        $validated['gown_code'] =
            $this->generateGownCode();


        if ($request->hasFile('image')) {

            $validated['image'] =
                $request
                    ->file('image')
                    ->store('gowns', 'public');
        }


        Gown::create($validated);


        return redirect()
            ->route('owner.gowns.index')
            ->with(
                'success',
                'Gown added successfully.'
            );
    }


    public function show(Gown $gown)
    {
        $gown->load([
            'category',
            'accessories',
            'reservationItems',
        ]);

        return view(
            'gowns.show',
            compact('gown')
        );
    }


    public function edit(Gown $gown)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'gowns.edit',
            compact(
                'gown',
                'categories'
            )
        );
    }


    public function update(
        Request $request,
        Gown $gown
    ) {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id'
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'size' => [
                'required',
                'string',
                'max:50'
            ],

            'color' => [
                'required',
                'string',
                'max:100'
            ],

            'style' => [
                'nullable',
                'string',
                'max:255'
            ],

            'rental_price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'purchase_price' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'measurements' => [
                'nullable',
                'string'
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120'
            ],

            'condition' => [
                'required',
                Rule::in([
                    'excellent',
                    'good',
                    'fair',
                    'damaged'
                ])
            ],

            'status' => [
                'required',
                Rule::in([
                    'available',
                    'reserved',
                    'rented',
                    'for_cleaning',
                    'under_maintenance',
                    'damaged',
                    'unavailable',
                    'retired'
                ])
            ],

            'date_purchased' => [
                'nullable',
                'date'
            ],
        ]);


        if ($request->hasFile('image')) {

            if ($gown->image) {

                Storage::disk('public')
                    ->delete($gown->image);
            }


            $validated['image'] =
                $request
                    ->file('image')
                    ->store('gowns', 'public');
        }


        $gown->update($validated);


        return redirect()
            ->route('owner.gowns.index')
            ->with(
                'success',
                'Gown updated successfully.'
            );
    }


    public function destroy(Gown $gown)
    {
        $gown->update([
            'status' => 'retired',
        ]);


        return redirect()
            ->route('owner.gowns.index')
            ->with(
                'success',
                'Gown retired successfully.'
            );
    }
}
