# Maintenance Module Implementation

## Overview
This module implements a comprehensive maintenance request system for the Tenant Rental System using the **Observer Behavioral Design Pattern**. It allows users to submit maintenance requests and sellers to manage and update the status of these requests with real-time notifications.

## Architecture

### Observer Pattern Implementation

The maintenance module follows the Observer design pattern to enable real-time notifications when maintenance request statuses change.

#### Components:

1. **MaintenanceSubject** (Subject Interface)
   - Location: `app/Patterns/MaintenanceSubject.php`
   - Defines the contract for observable objects
   - Methods:
     - `attach(MaintenanceObserver $observer)`: Register an observer
     - `detach(MaintenanceObserver $observer)`: Unregister an observer
     - `notifyObservers()`: Notify all registered observers

2. **MaintenanceObserver** (Observer Interface)
   - Location: `app/Patterns/MaintenanceObserver.php`
   - Defines how observers receive updates
   - Methods:
     - `update(string $status, array $data)`: Receive status updates

3. **MaintenanceRequest** (Concrete Subject)
   - Location: `app/Models/MaintenanceRequest.php`
   - Implements MaintenanceSubject interface
   - Manages maintenance request state
   - Notifies observers when status changes through `setStatus()` method
   - Status flow: REQUESTED → REVIEWED → IN_PROGRESS → COMPLETED

4. **MaintenanceStatusObserver** (Concrete Observer)
   - Location: `app/Observers/MaintenanceStatusObserver.php`
   - Implements MaintenanceObserver interface
   - Broadcasts status updates via Laravel Echo (WebSocket)
   - Logs status changes for auditing

5. **MaintenancePage** (User Interface - Concrete Observer)
   - Location: `resources/js/Pages/Maintenance/MaintenancePage.jsx`
   - User-facing interface for submitting and viewing maintenance requests
   - Listens to real-time status updates via WebSocket
   - Automatically updates UI when seller changes status

6. **OwnerMaintenancePage** (Seller Interface - Concrete Observer)
   - Location: `resources/js/Pages/Maintenance/OwnerMaintenancePage.jsx`
   - Seller-facing interface for managing maintenance requests
   - Updates maintenance status and triggers notifications
   - Displays statistics dashboard

## Use Cases

### Use Case 1: Request Maintenance
**Actor:** User (Tenant/Buyer)

**Preconditions:**
- User is logged into the system
- User has access to at least one property

**Normal Flow:**
1. User navigates to `/maintenance`
2. System displays MaintenancePage with existing requests
3. User clicks "New Request" button
4. System displays maintenance request form
5. User fills in:
   - Property (dropdown)
   - Title
   - Description (minimum 10 characters)
   - Priority (LOW, MEDIUM, HIGH, URGENT)
6. User submits the form
7. System validates the input
8. System creates a maintenance request with status "REQUESTED"
9. System attaches MaintenanceStatusObserver
10. System notifies observers (broadcasts to seller)
11. System displays success message

**Alternative Flow:**
- 6a. Empty or invalid description:
  - System displays error message
  - Request is not submitted
  - User can correct and resubmit

**Postcondition:**
- Maintenance request is created in database
- Status is set to "REQUESTED"
- Seller can view the request in OwnerMaintenancePage

### Use Case 2: Update Maintenance Status
**Actor:** Seller (Property Owner)

**Preconditions:**
- Seller is logged into the system
- At least one maintenance request exists for seller's properties

**Normal Flow:**
1. Seller navigates to `/seller/maintenance`
2. System displays OwnerMaintenancePage with:
   - Statistics dashboard
   - List of maintenance requests
3. Seller selects a maintenance request
4. Seller clicks "Update Status" button
5. System displays update modal with:
   - Request details
   - Status dropdown (REQUESTED, REVIEWED, IN_PROGRESS, COMPLETED)
   - Notes textarea
6. Seller updates the status and adds notes
7. Seller submits the update
8. System validates the input
9. System calls `MaintenanceRequest->setStatus()`
10. MaintenanceRequest notifies all attached observers
11. MaintenanceStatusObserver broadcasts the update via WebSocket
12. System updates the request in database
13. System displays updated maintenance list
14. User's MaintenancePage receives real-time update and refreshes automatically

**Alternative Flow:**
- 2a. No maintenance requests available:
  - System displays "No maintenance requests" message
  - Seller can wait for new requests

**Postcondition:**
- Maintenance request status is updated in database
- User receives real-time notification
- Both MaintenancePage and OwnerMaintenancePage show updated status

## Database Schema

### Table: `maintenance_requests`

```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id             BIGINT UNSIGNED (FK to users.id) ON DELETE CASCADE
property_id         BIGINT UNSIGNED (FK to properties.id) ON DELETE CASCADE
title               VARCHAR(255)
description         TEXT
status              ENUM('REQUESTED', 'REVIEWED', 'IN_PROGRESS', 'COMPLETED') DEFAULT 'REQUESTED'
priority            ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM'
assigned_to         BIGINT UNSIGNED (FK to users.id) ON DELETE SET NULL (nullable)
completed_at        TIMESTAMP (nullable)
notes               TEXT (nullable)
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
- user_id
- property_id
- status
```

## API Endpoints

### User Routes (Authenticated)
- `GET /maintenance` - Display maintenance page
- `POST /maintenance` - Submit new maintenance request
- `GET /maintenance/{id}` - View specific request
- `GET /api/maintenance/user-requests` - Get all user's requests (API)

### Seller Routes (Authenticated, Seller Role)
- `GET /seller/maintenance` - Display maintenance management page
- `PUT /seller/maintenance/{id}/status` - Update request status
- `GET /seller/maintenance/{id}` - View specific request
- `GET /api/maintenance/seller-requests` - Get all seller's requests (API)
- `GET /api/maintenance/statistics` - Get statistics dashboard data

## WebSocket Channels

### Broadcasting Channels
1. **Private Channel:** `maintenance.user.{userId}`
   - User-specific updates
   - Authorization: Only the user can subscribe

2. **Private Channel:** `maintenance.property.{propertyId}`
   - Property-specific updates
   - Authorization: Property owner or buyer can subscribe

3. **Public Channel:** `maintenance.updates`
   - General maintenance updates
   - All authenticated users can subscribe

### Event: `MaintenanceStatusUpdated`
- Event Class: `App\Events\MaintenanceStatusUpdated`
- Broadcast As: `maintenance.status.updated`
- Payload:
  ```javascript
  {
    status: string,
    request_id: number,
    user_id: number,
    property_id: number,
    title: string,
    description: string,
    priority: string,
    updated_at: timestamp
  }
  ```

## Real-time Notification Flow

1. **Seller updates status:**
   - SellerMaintenanceController receives update request
   - Controller attaches MaintenanceStatusObserver to MaintenanceRequest
   - Controller calls `MaintenanceRequest->setStatus(newStatus)`

2. **MaintenanceRequest (Subject) notifies observers:**
   - Iterates through all attached observers
   - Calls `observer->update(status, data)` for each

3. **MaintenanceStatusObserver broadcasts:**
   - Logs the status change
   - Broadcasts `MaintenanceStatusUpdated` event to WebSocket channels

4. **Clients receive updates:**
   - MaintenancePage (User): Receives update via WebSocket, updates UI, shows alert
   - OwnerMaintenancePage (Seller): Receives update, refreshes statistics and list

## Key Features

### 1. Observer Pattern Benefits
- **Loose Coupling:** UI components don't directly depend on the model
- **Real-time Updates:** Automatic notification without page refresh
- **Scalability:** Easy to add more observers (e.g., email notifications, SMS)
- **Maintainability:** Clear separation of concerns

### 2. Validation
- Form validation on both client and server side
- Description must be at least 10 characters
- Property selection required and validated
- Status transitions validated

### 3. User Experience
- Real-time status updates without page refresh
- Visual feedback with color-coded status badges
- Priority indicators (LOW, MEDIUM, HIGH, URGENT)
- Statistics dashboard for sellers
- Responsive design for mobile devices

### 4. Security
- Authentication required for all routes
- Authorization checks ensure users only see their own requests
- Sellers only see requests for their properties
- CSRF protection on all forms
- Private WebSocket channels with authorization

## Installation & Setup

1. **Run Migration:**
   ```bash
   php artisan migrate
   ```

2. **Configure Broadcasting:**
   - Ensure Laravel Echo is configured in `resources/js/bootstrap.js`
   - Configure Pusher or Laravel WebSockets in `.env`

3. **Start Queue Worker (if using queued broadcasting):**
   ```bash
   php artisan queue:work
   ```

4. **Compile Frontend Assets:**
   ```bash
   npm run dev
   ```

## Usage Examples

### Submitting a Maintenance Request (User)
```javascript
// Navigate to /maintenance
// Click "New Request"
// Fill form:
{
  property_id: 1,
  title: "Leaking faucet in kitchen",
  description: "The kitchen faucet has been leaking for 2 days...",
  priority: "HIGH"
}
// Submit
```

### Updating Status (Seller)
```javascript
// Navigate to /seller/maintenance
// Click "Update Status" on a request
// Change status to "IN_PROGRESS"
// Add notes: "Plumber scheduled for tomorrow at 10 AM"
// Submit
// User receives real-time notification
```

## Testing WebSocket Connection

```javascript
// In browser console:
Echo.private('maintenance.user.1')
    .listen('.maintenance.status.updated', (e) => {
        console.log('Status updated:', e);
    });
```

## Future Enhancements

1. Email notifications when status changes
2. SMS notifications for urgent requests
3. File upload for maintenance request images
4. Maintenance history and reports
5. Automated assignment to maintenance staff
6. Mobile app support
7. In-app notifications panel
8. Maintenance request comments/chat

## Design Pattern Justification

### Why Observer Pattern?

**Problem:**
- Original implementation required manual page refresh to see status updates
- Tight coupling between UI and backend
- Poor user experience with delayed status visibility
- Difficult to add new notification channels

**Solution:**
The Observer pattern provides:
1. **Automatic Updates:** When seller changes status, all observers are notified automatically
2. **Loose Coupling:** MaintenancePage and OwnerMaintenancePage don't need to know about each other
3. **Extensibility:** Easy to add new observers (email, SMS, push notifications)
4. **Real-time Experience:** WebSocket integration provides instant updates
5. **Single Responsibility:** Each component has a clear, focused purpose

**Implementation Highlights:**
- MaintenanceRequest (Subject) owns the state (status)
- MaintenanceStatusObserver (Observer) handles broadcasting
- UI components (MaintenancePage, OwnerMaintenancePage) act as visual observers
- State changes trigger automatic notifications to all interested parties

## Conclusion

This maintenance module demonstrates a robust implementation of the Observer pattern, providing:
- Real-time status synchronization
- Clean architecture with separation of concerns
- Excellent user experience without page refreshes
- Scalable foundation for future enhancements
- Production-ready code with proper validation and security
