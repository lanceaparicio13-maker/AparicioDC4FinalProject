<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentTypeController extends Controller
{
    /**
     * List document types.
     * Residents can see active types; admins can see all.
     */
    public function index(Request $request)
    {
        $query = DocumentType::query();

        if ($request->user()->role !== 'admin') {
            $query->where('is_active', true);
        }

        return response()->json(
            $query->orderBy('name')->paginate(15)
        );
    }

    /**
     * Create a document type (admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:document_types,name'],
            'description' => ['nullable', 'string'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $documentType = DocumentType::create($validated);

        return response()->json([
            'message' => 'Document type created successfully.',
            'data' => $documentType,
        ], 201);
    }

    /**
     * View one document type.
     */
    public function show(Request $request, DocumentType $documentType)
    {
        if (
            !$documentType->is_active &&
            $request->user()->role !== 'admin'
        ) {
            return response()->json([
                'message' => 'Document type not found.',
            ], 404);
        }

        return response()->json(['data' => $documentType]);
    }

    /**
     * Update a document type (admin only).
     */
    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('document_types', 'name')->ignore($documentType->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'fee' => ['sometimes', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $documentType->update($validated);

        return response()->json([
            'message' => 'Document type updated successfully.',
            'data' => $documentType->fresh(),
        ]);
    }

    /**
     * Delete a document type (admin only).
     */
    public function destroy(DocumentType $documentType)
    {
        if ($documentType->documentRequests()->exists()) {
            return response()->json([
                'message' => 'This document type has existing requests. Deactivate it instead of deleting it.',
            ], 422);
        }

        $documentType->delete();

        return response()->json([
            'message' => 'Document type deleted successfully.',
        ]);
    }
}