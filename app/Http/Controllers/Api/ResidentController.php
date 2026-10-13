<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Resident::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('residency_status')) {
            $query->where(
                'residency_status',
                $request->input('residency_status')
            );
        }

        return response()->json(
            $query->orderBy('last_name')
                ->paginate(10)
        );
    }

    public function show(Resident $resident)
    {
        return response()->json($resident);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'sex' => ['nullable', 'string', 'max:50'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'purok' => ['nullable', 'string', 'max:255'],
            'residency_status' => [
                'sometimes',
                Rule::in(['Active', 'Inactive']),
            ],
        ]);

        $resident = Resident::create($validated);

        return response()->json([
            'message' => 'Resident created successfully.',
            'resident' => $resident,
        ], 201);
    }

    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'sex' => ['sometimes', 'nullable', 'string', 'max:50'],
            'civil_status' => ['sometimes', 'nullable', 'string', 'max:50'],
            'contact_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'required', 'string'],
            'purok' => ['sometimes', 'nullable', 'string', 'max:255'],
            'residency_status' => [
                'sometimes',
                Rule::in(['Active', 'Inactive']),
            ],
        ]);

        $resident->update($validated);

        return response()->json([
            'message' => 'Resident updated successfully.',
            'resident' => $resident->fresh(),
        ]);
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json([
            'message' => 'Resident deleted successfully.',
        ]);
    }
}