<?php

namespace App\Observers;

use App\Patterns\MaintenanceObserver;
use App\Events\MaintenanceStatusUpdated;
use Illuminate\Support\Facades\Log;

/**
 * Concrete Observer that broadcasts maintenance status updates
 * This observer is responsible for real-time notifications to both
 * MaintenancePage (user view) and OwnerMaintenancePage (seller view)
 */
class MaintenanceStatusObserver implements MaintenanceObserver
{
    /**
     * Update method called when maintenance status changes
     * Broadcasts the update to all connected clients via WebSocket
     * 
     * @param string $status
     * @param array $data
     * @return void
     */
    public function update(string $status, array $data = []): void
    {
        // Log the status update
        Log::info('Maintenance status updated', [
            'status' => $status,
            'request_id' => $data['request_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'property_id' => $data['property_id'] ?? null,
        ]);

        // Broadcast the status update event for real-time notifications
        // This will update both MaintenancePage and OwnerMaintenancePage
        broadcast(new MaintenanceStatusUpdated($status, $data))->toOthers();
    }
}
