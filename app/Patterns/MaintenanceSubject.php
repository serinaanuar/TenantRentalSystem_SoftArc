<?php

namespace App\Patterns;

interface MaintenanceSubject
{
    /**
     * Attach an observer to the subject
     * 
     * @param MaintenanceObserver $observer
     * @return void
     */
    public function attach(MaintenanceObserver $observer): void;

    /**
     * Detach an observer from the subject
     * 
     * @param MaintenanceObserver $observer
     * @return void
     */
    public function detach(MaintenanceObserver $observer): void;

    /**
     * Notify all observers about state changes
     * 
     * @return void
     */
    public function notifyObservers(): void;
}
