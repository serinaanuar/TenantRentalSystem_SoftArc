<?php

namespace App\Patterns;

interface MaintenanceObserver
{
    /**
     * Update the observer with the new status
     * 
     * @param string $status
     * @param array $data
     * @return void
     */
    public function update(string $status, array $data = []): void;
}
