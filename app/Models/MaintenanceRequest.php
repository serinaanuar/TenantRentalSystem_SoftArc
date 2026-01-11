<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Patterns\MaintenanceSubject;
use App\Patterns\MaintenanceObserver;
use App\Models\User;
use App\Models\Property;

/**
 * MaintenanceRequest - Concrete Subject
 * 
 * This class implements the MaintenanceSubject interface and manages
 * the state of maintenance requests. When the status changes, it
 * notifies all attached observers.
 */
class MaintenanceRequest extends Model implements MaintenanceSubject
{
    use HasFactory;

    /**
     * Status constants
     */
    const STATUS_REQUESTED = 'REQUESTED';
    const STATUS_REVIEWED = 'REVIEWED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'property_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'completed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Array of observers
     * 
     * @var array
     */
    protected static $observers = [];

    /**
     * Boot method to register observers
     */
    protected static function boot()
    {
        parent::boot();

        // Reset observers array for each instance
        static::$observers = [];
    }

    /**
     * Attach an observer
     * 
     * @param MaintenanceObserver $observer
     * @return void
     */
    public function attach(MaintenanceObserver $observer): void
    {
        $observerClass = get_class($observer);
        
        if (!isset(self::$observers[$observerClass])) {
            self::$observers[$observerClass] = $observer;
        }
    }

    /**
     * Detach an observer
     * 
     * @param MaintenanceObserver $observer
     * @return void
     */
    public function detach(MaintenanceObserver $observer): void
    {
        $observerClass = get_class($observer);
        
        if (isset(self::$observers[$observerClass])) {
            unset(self::$observers[$observerClass]);
        }
    }

    /**
     * Notify all observers about status changes
     * 
     * @return void
     */
    public function notifyObservers(): void
    {
        $data = [
            'request_id' => $this->id,
            'user_id' => $this->user_id,
            'property_id' => $this->property_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'completed_at' => $this->completed_at,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        foreach (self::$observers as $observer) {
            $observer->update($this->status, $data);
        }
    }

    /**
     * Set the status and notify observers
     * 
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->save();
        
        // Notify all observers about the status change
        $this->notifyObservers();
    }

    /**
     * Get the user who created the maintenance request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the property associated with the maintenance request
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Get the seller/owner of the property
     */
    public function seller()
    {
        return $this->hasOneThrough(
            User::class,
            Property::class,
            'id', // Foreign key on properties table
            'id', // Foreign key on users table
            'property_id', // Local key on maintenance_requests table
            'user_id' // Local key on properties table
        );
    }

    /**
     * Get available status options
     * 
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_REQUESTED,
            self::STATUS_REVIEWED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
        ];
    }
}
