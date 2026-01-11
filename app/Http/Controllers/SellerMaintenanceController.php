<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Observers\MaintenanceStatusObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SellerMaintenanceController extends Controller
{
    /**
     * Display the seller's maintenance management page
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        $seller = Auth::user();
        
        // Get all maintenance requests for properties owned by the seller
        $maintenanceRequests = MaintenanceRequest::with(['property', 'user'])
            ->whereHas('property', function($query) use ($seller) {
                $query->where('user_id', $seller->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $statusOptions = MaintenanceRequest::getStatusOptions();

        return Inertia::render('Maintenance/OwnerMaintenancePage', [
            'maintenanceRequests' => $maintenanceRequests,
            'statusOptions' => $statusOptions,
        ]);
    }

    /**
     * Update the status of a maintenance request
     * 
     * @param Request $request
     * @param MaintenanceRequest $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $seller = Auth::user();

        // Verify the seller owns the property
        $property = $maintenanceRequest->property;
        if ($property->user_id !== $seller->id) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the request
        $validated = $request->validate([
            'status' => 'required|in:REQUESTED,REVIEWED,IN_PROGRESS,COMPLETED',
            'notes' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Attach observer before updating status
        $observer = new MaintenanceStatusObserver();
        $maintenanceRequest->attach($observer);

        // Update the maintenance request
        if (isset($validated['notes'])) {
            $maintenanceRequest->notes = $validated['notes'];
        }

        if (isset($validated['assigned_to'])) {
            $maintenanceRequest->assigned_to = $validated['assigned_to'];
        }

        // Set completed_at timestamp if status is COMPLETED
        if ($validated['status'] === MaintenanceRequest::STATUS_COMPLETED && 
            $maintenanceRequest->status !== MaintenanceRequest::STATUS_COMPLETED) {
            $maintenanceRequest->completed_at = now();
        }

        // Use setStatus method to trigger observer notification
        $maintenanceRequest->setStatus($validated['status']);

        return redirect()->route('seller.maintenance.index')
            ->with('success', 'Maintenance request status updated successfully.');
    }

    /**
     * Get all maintenance requests for the seller's properties (API)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSellerRequests()
    {
        $seller = Auth::user();
        
        $maintenanceRequests = MaintenanceRequest::with(['property', 'user'])
            ->whereHas('property', function($query) use ($seller) {
                $query->where('user_id', $seller->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($maintenanceRequests);
    }

    /**
     * Show a specific maintenance request
     * 
     * @param MaintenanceRequest $maintenanceRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $seller = Auth::user();

        // Verify the seller owns the property
        $property = $maintenanceRequest->property;
        if ($property->user_id !== $seller->id) {
            abort(403, 'Unauthorized action.');
        }

        $maintenanceRequest->load(['property', 'user']);

        return response()->json($maintenanceRequest);
    }

    /**
     * Get statistics for seller's maintenance requests
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        $seller = Auth::user();
        
        $statistics = [
            'total' => MaintenanceRequest::whereHas('property', function($query) use ($seller) {
                $query->where('user_id', $seller->id);
            })->count(),
            
            'requested' => MaintenanceRequest::whereHas('property', function($query) use ($seller) {
                $query->where('user_id', $seller->id);
            })->where('status', MaintenanceRequest::STATUS_REQUESTED)->count(),
            
            'reviewed' => MaintenanceRequest::whereHas('property', function($query) use ($seller) {
                $query->where('user_id', $seller->id);
            })->where('status', MaintenanceRequest::STATUS_REVIEWED)->count(),
            
            'in_progress' => MaintenanceRequest::whereHas('property', function($query) use ($seller) {
                $query->where('user_id', $seller->id);
            })->where('status', MaintenanceRequest::STATUS_IN_PROGRESS)->count(),
            
            'completed' => MaintenanceRequest::whereHas('property', function($query) use ($seller) {
                $query->where('user_id', $seller->id);
            })->where('status', MaintenanceRequest::STATUS_COMPLETED)->count(),
        ];

        return response()->json($statistics);
    }
}
