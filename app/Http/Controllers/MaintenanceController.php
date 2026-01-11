<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Observers\MaintenanceStatusObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    /**
     * Display the maintenance page with user's maintenance requests
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all maintenance requests for the authenticated user
        $maintenanceRequests = MaintenanceRequest::with(['property', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get user's properties for the dropdown
        // Include properties where user is the buyer (purchased/renting) OR the owner
        $userProperties = Property::where(function($query) use ($user) {
                $query->where('buyer_id', $user->id)
                      ->orWhere('user_id', $user->id);
            })
            ->whereIn('status', ['sold', 'available', 'pending'])
            ->get(['id', 'property_name', 'property_address_line_1', 'city', 'status']);

        return Inertia::render('Maintenance/MaintenancePage', [
            'maintenanceRequests' => $maintenanceRequests,
            'userProperties' => $userProperties,
        ]);
    }

    /**
     * Store a newly created maintenance request
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'sometimes|in:LOW,MEDIUM,HIGH,URGENT',
        ], [
            'description.required' => 'The description field is required.',
            'description.min' => 'The description must be at least 10 characters.',
            'property_id.required' => 'Please select a property.',
            'property_id.exists' => 'The selected property is invalid.',
        ]);

        $user = Auth::user();

        // Verify user owns or rents the property
        $property = Property::where('id', $validated['property_id'])
            ->where(function($query) use ($user) {
                $query->where('buyer_id', $user->id)
                      ->orWhere('user_id', $user->id);
            })
            ->firstOrFail();

        // Create maintenance request
        $maintenanceRequest = MaintenanceRequest::create([
            'user_id' => $user->id,
            'property_id' => $validated['property_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'MEDIUM',
            'status' => MaintenanceRequest::STATUS_REQUESTED,
        ]);

        // Attach observer and notify
        $observer = new MaintenanceStatusObserver();
        $maintenanceRequest->attach($observer);
        $maintenanceRequest->notifyObservers();

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance request submitted successfully.');
    }

    /**
     * Display the specified maintenance request
     * 
     * @param MaintenanceRequest $maintenanceRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        // Ensure user can only view their own requests
        if ($maintenanceRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $maintenanceRequest->load(['property', 'user']);

        return response()->json($maintenanceRequest);
    }

    /**
     * Get all maintenance requests for the authenticated user (API)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserRequests()
    {
        $user = Auth::user();
        
        $maintenanceRequests = MaintenanceRequest::with(['property', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($maintenanceRequests);
    }
}
