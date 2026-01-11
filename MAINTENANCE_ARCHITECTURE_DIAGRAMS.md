# Maintenance Module - Observer Pattern Architecture

## Class Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         OBSERVER PATTERN                            │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────────┐
│  <<interface>>           │
│  MaintenanceSubject      │
├──────────────────────────┤
│ + attach(observer)       │
│ + detach(observer)       │
│ + notifyObservers()      │
└──────────────────────────┘
           △
           │ implements
           │
┌──────────────────────────┐         ┌──────────────────────────┐
│  MaintenanceRequest      │────────>│  <<interface>>           │
│  (Concrete Subject)      │ uses    │  MaintenanceObserver     │
├──────────────────────────┤         ├──────────────────────────┤
│ - status: string         │         │ + update(status, data)   │
│ - observers: array       │         └──────────────────────────┘
├──────────────────────────┤                    △
│ + setStatus(status)      │                    │ implements
│ + attach(observer)       │                    │
│ + detach(observer)       │         ┌──────────┴───────────┐
│ + notifyObservers()      │         │                      │
└──────────────────────────┘         │                      │
                            ┌────────┴──────────┐ ┌────────┴──────────┐
                            │MaintenanceStatus  │ │  UI Components    │
                            │Observer           │ │  (Visual Observers)│
                            ├───────────────────┤ ├───────────────────┤
                            │+ update()         │ │- MaintenancePage  │
                            │- broadcast()      │ │- OwnerMaintenance │
                            │- log()            │ │  Page             │
                            └───────────────────┘ └───────────────────┘
```

## Sequence Diagram - Status Update Flow

```
User           Seller        Controller         MaintenanceRequest    Observer           WebSocket        User UI
 │                │                │                     │                │                 │               │
 │                │  1. Update      │                     │                │                 │               │
 │                │  Status         │                     │                │                 │               │
 │                ├────────────────>│                     │                │                 │               │
 │                │                 │ 2. Attach           │                │                 │               │
 │                │                 │    Observer         │                │                 │               │
 │                │                 ├────────────────────>│                │                 │               │
 │                │                 │                     │ 3. Attach      │                 │               │
 │                │                 │                     ├───────────────>│                 │               │
 │                │                 │                     │                │                 │               │
 │                │                 │ 4. setStatus()      │                │                 │               │
 │                │                 ├────────────────────>│                │                 │               │
 │                │                 │                     │ 5. notifyObservers()            │               │
 │                │                 │                     ├───────────────>│                 │               │
 │                │                 │                     │                │ 6. Broadcast    │               │
 │                │                 │                     │                ├────────────────>│               │
 │                │                 │                     │                │                 │ 7. Push Update│
 │                │                 │                     │                │                 ├──────────────>│
 │                │                 │                     │                │                 │               │
 │                │                 │ 8. Success          │                │                 │ 9. UI Update  │
 │                │<────────────────┤                     │                │                 │    (Real-time)│
 │                │                 │                     │                │                 │<──────────────┤
 │                │  10. Page       │                     │                │                 │               │
 │                │      Refreshes  │                     │                │                 │               │
 │                │<────────────────┘                     │                │                 │               │
```

## Component Interaction Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    MAINTENANCE MODULE FLOW                          │
└─────────────────────────────────────────────────────────────────────┘

   USER SUBMITS REQUEST                    SELLER UPDATES STATUS
   ─────────────────────                   ─────────────────────
           │                                        │
           ▼                                        ▼
   ┌──────────────┐                        ┌──────────────────┐
   │MaintenancePage│                       │OwnerMaintenance  │
   │  (User View) │                       │    Page          │
   └──────┬───────┘                        └────────┬─────────┘
          │                                         │
          │ POST /maintenance                       │ PUT /maintenance/{id}/status
          │                                         │
          ▼                                         ▼
   ┌─────────────────┐                     ┌──────────────────────┐
   │ Maintenance     │                     │ SellerMaintenance    │
   │ Controller      │                     │ Controller           │
   └────────┬────────┘                     └──────────┬───────────┘
            │                                         │
            │ 1. Create Request                       │ 1. Attach Observer
            │ 2. Attach Observer                      │ 2. Call setStatus()
            │ 3. Notify                               │
            │                                         │
            ▼                                         ▼
   ┌────────────────────────────────────────────────────────────┐
   │              MaintenanceRequest (Subject)                  │
   │  - Manages state (status)                                  │
   │  - Maintains list of observers                             │
   │  - Notifies on status change                               │
   └────────────────────┬───────────────────────────────────────┘
                        │
                        │ notifyObservers()
                        │
                        ▼
   ┌────────────────────────────────────────────────────────────┐
   │         MaintenanceStatusObserver (Concrete Observer)      │
   │  - Logs status changes                                     │
   │  - Broadcasts to WebSocket                                 │
   └────────────────────┬───────────────────────────────────────┘
                        │
                        │ broadcast()
                        │
                        ▼
   ┌────────────────────────────────────────────────────────────┐
   │              Laravel Echo / WebSocket Server               │
   └────────┬───────────────────────────────────┬───────────────┘
            │                                   │
            │ Push to:                          │ Push to:
            │ maintenance.user.{userId}         │ maintenance.property.{propertyId}
            │                                   │
            ▼                                   ▼
   ┌──────────────┐                     ┌──────────────────┐
   │MaintenancePage│ ←── Real-time ───→ │OwnerMaintenance  │
   │  (Updates UI)│                     │    Page          │
   └──────────────┘                     └──────────────────┘
```

## Status State Machine

```
   ┌──────────┐
   │ REQUESTED│  ← Initial state when user submits
   └─────┬────┘
         │
         │ Seller reviews
         ▼
   ┌──────────┐
   │ REVIEWED │  ← Seller has seen and acknowledged
   └─────┬────┘
         │
         │ Work begins
         ▼
   ┌──────────┐
   │IN_PROGRESS│ ← Actively being worked on
   └─────┬────┘
         │
         │ Work finished
         ▼
   ┌──────────┐
   │ COMPLETED│  ← Final state
   └──────────┘
```

## Data Flow - Real-time Synchronization

```
┌─────────────────────────────────────────────────────────────────────┐
│         REAL-TIME BIDIRECTIONAL STATUS SYNCHRONIZATION              │
└─────────────────────────────────────────────────────────────────────┘

   Seller's Browser                    Server                    User's Browser
   ────────────────                    ──────                    ──────────────
         │                                │                              │
         │ 1. User clicks "Update         │                              │
         │    Status" button              │                              │
         │───────────────────────────────>│                              │
         │                                │                              │
         │                         2. Attach Observer                    │
         │                         3. setStatus()                        │
         │                         4. notifyObservers()                  │
         │                         5. Broadcast Event                    │
         │                                │                              │
         │ 6. WebSocket Push              │ 7. WebSocket Push            │
         │<───────────────────────────────┼─────────────────────────────>│
         │                                │                              │
         │ 8. OwnerMaintenancePage        │ 9. MaintenancePage           │
         │    updates (statistics +       │    updates (status badge +   │
         │    request list)               │    shows notification)       │
         │                                │                              │
         ▼                                ▼                              ▼
   [UI Auto-Refresh]              [Observer Pattern]           [UI Auto-Refresh]
   No page reload!                  in Action!                  No page reload!
```

## File Structure

```
TenantRentalSystem_SoftArc/
│
├── app/
│   ├── Events/
│   │   └── MaintenanceStatusUpdated.php          # Broadcast event
│   │
│   ├── Http/Controllers/
│   │   ├── MaintenanceController.php             # User controller
│   │   └── SellerMaintenanceController.php       # Seller controller
│   │
│   ├── Models/
│   │   └── MaintenanceRequest.php                # Concrete Subject
│   │
│   ├── Observers/
│   │   └── MaintenanceStatusObserver.php         # Concrete Observer
│   │
│   └── Patterns/
│       ├── MaintenanceSubject.php                # Subject Interface
│       └── MaintenanceObserver.php               # Observer Interface
│
├── database/
│   └── migrations/
│       └── 2025_01_11_000000_create_maintenance_requests_table.php
│
├── resources/
│   └── js/
│       └── Pages/
│           └── Maintenance/
│               ├── MaintenancePage.jsx           # User UI (Visual Observer)
│               └── OwnerMaintenancePage.jsx      # Seller UI (Visual Observer)
│
└── routes/
    ├── web.php                                   # HTTP routes
    └── channels.php                              # WebSocket channels
```

## Observer Pattern Benefits in This Implementation

1. **Loose Coupling**
   - UI components don't directly depend on each other
   - MaintenancePage doesn't know about OwnerMaintenancePage
   - Easy to add/remove observers without changing Subject

2. **Real-time Updates**
   - Status changes propagate instantly
   - No manual refresh needed
   - Better user experience

3. **Scalability**
   - Easy to add new observers:
     * Email notifications
     * SMS alerts
     * Push notifications
     * Slack/Teams integration
     * Analytics tracking

4. **Single Responsibility**
   - MaintenanceRequest: Manages state
   - MaintenanceStatusObserver: Handles notifications
   - Controllers: Handle HTTP requests
   - UI Components: Display and interact

5. **Open/Closed Principle**
   - Open for extension (add observers)
   - Closed for modification (Subject code unchanged)
