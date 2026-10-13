<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DocumentRequestController extends Controller
{
    /**
     * List document requests.
     * Residents can only see their own requests.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = DocumentRequest::with([
            'resident',
            'documentType',
            'processedBy:id,name,email',
        ]);

        if ($user->role !== 'admin') {
            $resident = Resident::where('user_id', $user->id)->first();

            if (!$resident) {
                return response()->json([
                    'message' => 'No resident profile is linked to this account.',
                ], 403);
            }

            $query->where('resident_id', $resident->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->latest('created_at')->paginate(15)
        );
    }

    /**
     * Submit a document request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => [
                'required',
                'integer',
                Rule::exists('document_types', 'id')
                    ->where('is_active', true),
            ],
            'purpose' => ['required', 'string', 'max:2000'],
        ]);

        $resident = Resident::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$resident) {
            return response()->json([
                'message' => 'Please complete your resident profile before requesting a document.',
            ], 403);
        }

        $documentRequest = DocumentRequest::create([
            'resident_id' => $resident->id,
            'document_type_id' => $validated['document_type_id'],
            'purpose' => $validated['purpose'],
            'status' => 'Pending',
            'requested_at' => now(),
        ]);

        return response()->json([
            'message' => 'Document request submitted successfully.',
            'data' => $documentRequest->load([
                'resident',
                'documentType',
            ]),
        ], 201);
    }

    /**
     * View one document request.
     */
    public function show(Request $request, DocumentRequest $documentRequest)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            $resident = Resident::where('user_id', $user->id)->first();

            if (
                !$resident ||
                $documentRequest->resident_id !== $resident->id
            ) {
                return response()->json([
                    'message' => 'You are not authorized to view this request.',
                ], 403);
            }
        }

        return response()->json([
            'data' => $documentRequest->load([
                'resident',
                'documentType',
                'processedBy:id,name,email',
            ]),
        ]);
    }

    /**
     * Admin updates request status and remarks.
     */
    public function update(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'status' => [
                'sometimes',
                'required',
                Rule::in([
                    'Pending',
                    'Processing',
                    'Approved',
                    'Rejected',
                    'Released',
                ]),
            ],
            'remarks' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        if (array_key_exists('status', $validated)) {
            $documentRequest->status = $validated['status'];

            if ($validated['status'] === 'Released') {
                $documentRequest->released_at = now();
            }
        }

        if (array_key_exists('remarks', $validated)) {
            $documentRequest->remarks = $validated['remarks'];
        }

        if ($request->user()->role === 'admin') {
            $documentRequest->processed_by = $request->user()->id;
        }

        $documentRequest->save();

        return response()->json([
            'message' => 'Document request updated successfully.',
            'data' => $documentRequest->fresh()->load([
                'resident',
                'documentType',
                'processedBy:id,name,email',
            ]),
        ]);
    }

    /**
     * Delete a document request.
     */
    public function destroy(Request $request, DocumentRequest $documentRequest)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            $resident = Resident::where('user_id', $user->id)->first();

            if (
                !$resident ||
                $documentRequest->resident_id !== $resident->id
            ) {
                return response()->json([
                    'message' => 'You are not authorized to delete this request.',
                ], 403);
            }

            if ($documentRequest->status !== 'Pending') {
                return response()->json([
                    'message' => 'Only pending requests can be cancelled.',
                ], 422);
            }
        }

        $documentRequest->delete();

        return response()->json([
            'message' => 'Document request deleted successfully.',
        ]);
    }
}