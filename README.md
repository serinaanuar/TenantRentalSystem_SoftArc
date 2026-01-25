
# Tenant Rental System - Real Estate Services Platform

A comprehensive web-based real estate management system built with **Laravel 10**, **React 18**, and **Inertia.js** that enables seamless property transactions, tenant management, and maintenance request handling.

## 📋 Table of Contents

- [Project Overview](#project-overview)
- [Key Features](#key-features)
- [System Architecture](#system-architecture)
- [Core Modules](#core-modules)
- [Recent Enhancements](#recent-enhancements)
- [Technology Stack](#technology-stack)
- [Installation & Setup](#installation--setup)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Usage Guidelines](#usage-guidelines)
- [Design Patterns](#design-patterns)
- [Contributing](#contributing)
- [License](#license)

---

## 🏢 Project Overview

The **Tenant Rental System** is a modern SaaS platform designed to revolutionize real estate management by connecting property sellers/landlords with potential buyers/renters. The system facilitates property listings, transactions, and ongoing maintenance management through an intuitive user interface powered by React and a robust backend powered by Laravel.

### Primary Use Cases:
- **Property Listings**: Sellers can list properties for sale or rent with detailed information
- **Property Transactions**: Buyers can purchase or rent properties through the platform
- **Maintenance Requests**: Tenants can submit maintenance requests, and property owners can manage them
- **Real-time Communications**: Chat system for direct communication between buyers and sellers
- **Payment Processing**: Secure payment handling for property transactions
- **Analytics Dashboard**: Comprehensive insights into system usage and transactions

---

## ✨ Key Features

### 👥 User Management
- Role-based access control (Admin, Seller, Buyer/Tenant, User)
- User authentication and authorization
- Profile management with verification
- User dashboard and activity tracking

### 🏠 Property Management
- **Add/Edit/Delete Properties**: Full CRUD operations for property listings
- **Property Categories**: Residential, Commercial, Land, Condominiums
- **Transaction Types**: For Sale, For Rent, New Launch
- **Property Details**: Address, price, amenities, furnishings, parking, unit information
- **Status Tracking**: Available, Sold, Rented, Pending
- **Property Images**: Upload and manage property photos

### 💰 Transaction System
- **Buy Property**: Complete purchase workflow with buyer association
- **Rent Property**: Rental agreement management
- **Property History**: Track all property transactions
- **Transaction Status**: Monitor transaction progress

### 🔧 Maintenance Management (NEW MODULE)
- **Maintenance Requests**: Tenants submit maintenance issues with priority levels
- **Observer Design Pattern**: Real-time status notifications using the Observer behavioral pattern
- **Request Tracking**: Track request status (REQUESTED → REVIEWED → IN_PROGRESS → COMPLETED)
- **Real-time Updates**: WebSocket integration for instant notifications
- **Status Management**: Sellers can update request status with notes
- **Statistics Dashboard**: View maintenance metrics and statistics

### 💳 Payment Processing (NEW MODULE)
- **Payment Gateway Integration**: Support for multiple payment methods
- **Invoice Management**: Generate and track payment invoices
- **Transaction History**: Complete payment audit trail
- **Payment Status Tracking**: Monitor payment completion
- **Secure Processing**: CSRF token protection and secure payment handling

### 📊 Analytics Dashboard (NEW MODULE)
- **System Metrics**: Real-time usage statistics
- **Transaction Analytics**: Revenue and transaction insights
- **User Activity Tracking**: User engagement metrics
- **Performance Monitoring**: System performance analytics
- **Data Visualization**: Charts and graphs for easy interpretation
- **Reports Generation**: Generate custom analytics reports

### 💬 Communication
- **Chat System**: Real-time messaging between users
- **Notifications**: System notifications for important events
- **Message History**: Store and retrieve chat history
- **Notification Center**: Centralized notification management

---

## 🏗️ System Architecture

```
TenantRentalSystem_SoftArc/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── MaintenanceController.php
│   │   │   ├── SellerMaintenanceController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── AnalyticsController.php
│   │   │   └── [Other Controllers]
│   │   ├── Requests/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── MaintenanceRequest.php
│   │   ├── Payment.php
│   │   ├── Invoice.php
│   │   ├── Property.php
│   │   └── [Other Models]
│   ├── Observers/
│   │   ├── MaintenanceStatusObserver.php
│   │   └── [Other Observers]
│   ├── Patterns/
│   │   ├── MaintenanceSubject.php
│   │   └── MaintenanceObserver.php
│   └── Events/
│       └── MaintenanceStatusUpdated.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Maintenance/
│   │   │   ├── Payment/
│   │   │   ├── Analytics/
│   │   │   └── [Other Pages]
│   │   └── Components/
│   └── css/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
└── [Configuration Files]
```

---

## 📦 Core Modules

### 1. **Maintenance Management Module** ⭐ (NEW)

#### Overview
A complete maintenance request management system that allows tenants to report maintenance issues and property owners to manage and resolve them efficiently using the **Observer Design Pattern**.

#### Features
- **Request Submission**: Tenants submit maintenance issues with title, description, and priority
- **Status Workflow**: 4-stage workflow (REQUESTED → REVIEWED → IN_PROGRESS → COMPLETED)
- **Real-time Notifications**: WebSocket-based instant notifications via Laravel Echo
- **Observer Pattern**: Implements observer design pattern for decoupled status notifications
- **Seller Dashboard**: View all maintenance requests for owned properties
- **Status Management**: Update request status and add notes
- **Statistics**: View metrics on maintenance requests by status

#### Key Files
```
app/Http/Controllers/MaintenanceController.php
app/Http/Controllers/SellerMaintenanceController.php
app/Models/MaintenanceRequest.php
app/Patterns/MaintenanceSubject.php
app/Patterns/MaintenanceObserver.php
app/Observers/MaintenanceStatusObserver.php
app/Events/MaintenanceStatusUpdated.php
resources/js/Pages/Maintenance/MaintenancePage.jsx
resources/js/Pages/Maintenance/OwnerMaintenancePage.jsx
database/migrations/2025_01_11_000000_create_maintenance_requests_table.php
```

#### Database Schema
```sql
CREATE TABLE maintenance_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    property_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT'),
    status ENUM('REQUESTED', 'REVIEWED', 'IN_PROGRESS', 'COMPLETED'),
    notes LONGTEXT NULL,
    assigned_to BIGINT NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);
```

#### Usage Flow
1. **User submits maintenance request** on MaintenancePage
2. **Request is created** in database with REQUESTED status
3. **MaintenanceStatusObserver listens** for status changes
4. **Seller updates status** through OwnerMaintenancePage
5. **Observer notifies** all attached observers (e.g., email, notifications)
6. **Real-time update** sent via WebSocket to user
7. **User receives notification** of status change

#### API Endpoints
```
GET  /maintenance                              - List user's requests
POST /maintenance                              - Create new request
GET  /maintenance/{id}                         - Get request details
GET  /api/maintenance/user-requests            - Get requests (API)

GET  /seller/maintenance                       - List seller's requests
PUT  /seller/maintenance/{id}/status           - Update request status
GET  /seller/maintenance/{id}                  - Get request details
GET  /seller/api/maintenance/seller-requests   - Get requests (API)
GET  /seller/api/maintenance/statistics        - Get maintenance stats
```

---

### 2. **Payment Module** ⭐ (NEW)

#### Overview
Comprehensive payment processing system for property transactions with invoice generation, payment tracking, and secure gateway integration.

#### Features
- **Payment Gateway Integration**: Support for multiple payment methods
- **Invoice Generation**: Automatic invoice creation for transactions
- **Payment Status Tracking**: Monitor payment completion and status
- **Transaction History**: Complete audit trail of all payments
- **Payment Validation**: Validate payment amounts and reconciliation
- **Secure Processing**: CSRF token protection and encrypted payment data
- **Payment Reports**: Generate payment analytics and reports

#### Key Files
```
app/Http/Controllers/PaymentController.php
app/Models/Payment.php
app/Models/Invoice.php
resources/js/Pages/Payment/PaymentPage.jsx
database/migrations/2025_01_12_000000_create_payments_table.php
database/migrations/2025_01_12_000000_create_invoices_table.php
```

#### Database Schema
```sql
CREATE TABLE payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    transaction_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    property_id BIGINT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'REFUNDED'),
    transaction_reference VARCHAR(255),
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

CREATE TABLE invoices (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    payment_id BIGINT NOT NULL,
    invoice_number VARCHAR(255) UNIQUE,
    invoice_date TIMESTAMP,
    due_date TIMESTAMP,
    amount_due DECIMAL(12, 2),
    amount_paid DECIMAL(12, 2),
    status ENUM('DRAFT', 'ISSUED', 'PAID', 'OVERDUE', 'CANCELLED'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id)
);
```

#### Usage Flow
1. **User initiates transaction** (Buy/Rent property)
2. **Payment record created** with PENDING status
3. **Payment form displayed** with amount and methods
4. **User submits payment** through secure gateway
5. **Payment processed** and status updated
6. **Invoice generated** automatically
7. **Confirmation sent** to user and seller

#### API Endpoints
```
POST /api/payment/process                      - Process payment
GET  /api/payment/history                      - Get payment history
GET  /api/payment/{id}                         - Get payment details
GET  /api/invoice/{id}                         - Get invoice details
POST /api/invoice/generate                     - Generate invoice
GET  /api/invoice/list                         - List invoices
```

---

### 3. **Analytics Dashboard Module** ⭐ (NEW)

#### Overview
Comprehensive analytics and reporting system that provides insights into system usage, transactions, user activity, and performance metrics.

#### Features
- **Real-time Metrics**: Live system statistics and activity data
- **Transaction Analytics**: Revenue, transaction counts, average values
- **User Activity Tracking**: Active users, registration trends, engagement
- **Property Analytics**: Listings, availability, price trends
- **Maintenance Metrics**: Request volume, resolution time, status distribution
- **Payment Analytics**: Payment volume, success rates, revenue trends
- **Data Visualization**: Charts, graphs, and interactive dashboards
- **Report Generation**: Custom reports with filtering and date ranges
- **Export Functionality**: Export data to CSV/PDF for external analysis
- **Performance Monitoring**: System health and performance metrics

#### Key Features
- Dashboard with KPIs (Key Performance Indicators)
- Transaction trends and forecasting
- User demographic analysis
- Property market insights
- Maintenance SLA monitoring
- Payment success rate tracking
- Revenue attribution analysis
- System performance monitoring

#### Key Files
```
app/Http/Controllers/AnalyticsController.php
resources/js/Pages/Analytics/AnalyticsDashboard.jsx
resources/js/Pages/Analytics/ReportGenerator.jsx
database/migrations/2025_01_13_000000_create_analytics_tables.php
```

#### Database Schema
```sql
CREATE TABLE analytics_metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(255) NOT NULL,
    metric_value LONGTEXT,
    metric_date DATE,
    created_at TIMESTAMP
);

CREATE TABLE user_activity_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    action VARCHAR(255),
    resource_type VARCHAR(100),
    resource_id BIGINT,
    ip_address VARCHAR(45),
    user_agent LONGTEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### API Endpoints
```
GET  /api/analytics/dashboard                  - Get dashboard metrics
GET  /api/analytics/transactions               - Get transaction analytics
GET  /api/analytics/users                      - Get user analytics
GET  /api/analytics/properties                 - Get property analytics
GET  /api/analytics/maintenance                - Get maintenance analytics
GET  /api/analytics/payments                   - Get payment analytics
GET  /api/analytics/report                     - Generate custom report
```

---

## 🔧 Technology Stack

### Backend
- **Framework**: Laravel 10.x
- **Language**: PHP 8.1+
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **Real-time**: Laravel Echo & WebSockets
- **API**: RESTful API with JSON

### Frontend
- **Library**: React 18.x
- **Router**: Inertia.js
- **Styling**: Tailwind CSS
- **Icons**: React Icons
- **HTTP Client**: Axios
- **State Management**: React Hooks
- **Build Tool**: Vite

### DevOps & Deployment
- **Server**: Apache/Nginx
- **Version Control**: Git & GitHub
- **Environment**: Laravel Artisan CLI
- **Package Manager**: Composer, npm/yarn

### Additional Libraries
- **Mail**: Laravel Mailable
- **Broadcasting**: WebSocket Server
- **Validation**: Laravel Validator
- **Testing**: PHPUnit, Jest
- **Documentation**: API Documentation (generated)

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm/yarn
- MySQL 8.0+
- Git

### Installation Steps

1. **Clone the Repository**
```bash
git clone https://github.com/serinaanuar/TenantRentalSystem_SoftArc.git
cd TenantRentalSystem_SoftArc
```

2. **Install PHP Dependencies**
```bash
composer install
```

3. **Install JavaScript Dependencies**
```bash
npm install
```

4. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Update .env File**
```env
DB_DATABASE=tenant_rental_system
DB_USERNAME=root
DB_PASSWORD=your_password

BROADCAST_DRIVER=websockets
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

6. **Database Setup**
```bash
php artisan migrate
php artisan db:seed
```

7. **Build Frontend Assets**
```bash
npm run build
# or for development with watch
npm run dev
```

8. **Start Development Server**
```bash
php artisan serve
# Server runs on http://127.0.0.1:8000
```

9. **Start WebSocket Server** (in separate terminal)
```bash
php artisan websockets:serve
```

### Test Accounts
```
Admin Account:
  Email: admin@example.com
  Password: admin123
  Role: Admin

Demo Seller:
  Email: seller@demo.com
  Password: password
  Role: Seller

Demo Buyer:
  Email: buyer@demo.com
  Password: password
  Role: User
```

---

## 📊 Database Schema

### Core Tables

#### Users
```sql
users (id, firstname, lastname, email, phone, ic_number, role, address_line_1, address_line_2, city, postal_code, state, created_at, updated_at)
```

#### Properties
```sql
properties (id, user_id, property_name, property_type, property_address_line_1, property_address_line_2, city, postal_code, state, purchase, price, status, buyer_id, created_at, updated_at)
```

#### Maintenance Requests
```sql
maintenance_requests (id, property_id, user_id, title, description, priority, status, notes, assigned_to, completed_at, created_at, updated_at)
```

#### Payments & Invoices
```sql
payments (id, transaction_id, user_id, property_id, amount, payment_method, payment_status, payment_date, created_at, updated_at)
invoices (id, payment_id, invoice_number, invoice_date, amount_due, amount_paid, status, created_at, updated_at)
```

#### Chat & Messages
```sql
chat_rooms (id, property_id, seller_id, buyer_id, created_at, updated_at)
chat_messages (id, chat_room_id, user_id, message, created_at, updated_at)
```

---

## 🔌 API Endpoints

### Authentication
```
POST   /api/auth/login                         - User login
POST   /api/auth/logout                        - User logout
POST   /api/auth/register                      - User registration
GET    /api/auth/me                            - Get current user
```

### Properties
```
GET    /api/properties                         - List all properties
POST   /api/properties                         - Create property
GET    /api/properties/{id}                    - Get property details
PUT    /api/properties/{id}                    - Update property
DELETE /api/properties/{id}                    - Delete property
POST   /api/properties/{id}/transaction        - Buy/Rent property
```

### Maintenance (Complete in documentation above)

### Payments (Complete in documentation above)

### Chat
```
GET    /api/chat/rooms                         - Get user's chat rooms
GET    /api/chat/rooms/{id}/messages           - Get messages in room
POST   /api/chat/messages                      - Send message
```

---

## 📖 Usage Guidelines

### For Sellers
1. **List a Property**: Navigate to "My Listing" → Add Property with all details
2. **Manage Maintenance**: Go to "Service & Maintenance" → "Manage Requests"
3. **Update Request Status**: Click on a request and update its status
4. **View Analytics**: Check your sales and maintenance metrics on dashboard

### For Buyers
1. **Browse Properties**: Visit "Buy" or "Rent" to browse available properties
2. **Submit Maintenance Request**: After renting, submit maintenance issues via "My Requests"
3. **Track Request Status**: Monitor your requests in real-time with notifications
4. **Payment History**: View all payments and invoices in "Billing & Invoice"

### For Admins
1. **User Management**: Manage all users, roles, and permissions
2. **Property Moderation**: Approve/reject property listings
3. **Analytics Dashboard**: Monitor system performance and metrics
4. **System Configuration**: Configure system settings and integrations

---

## 🎯 Design Patterns

### 1. Observer Pattern (Maintenance Module)
**Purpose**: Decouple maintenance status updates from notifications

**Implementation**:
- `MaintenanceSubject`: Interface defining observable contract
- `MaintenanceObserver`: Interface for observers
- `MaintenanceRequest`: Concrete subject maintaining observer list
- `MaintenanceStatusObserver`: Concrete observer handling status updates
- When status changes, all attached observers are notified

**Benefits**:
- Loose coupling between components
- Easy to add new observers without modifying existing code
- Real-time notification propagation
- Extensible notification system

**Code Location**: `app/Patterns/`

### 2. MVC Architecture
All modules follow Laravel's MVC pattern with clear separation of concerns:
- **Models**: Data layer and business logic
- **Views**: React components for UI rendering
- **Controllers**: Request handling and orchestration

### 3. RESTful API Design
All API endpoints follow REST conventions for consistency and clarity

### 4. Event-Driven Architecture
Laravel Events for asynchronous operations and decoupled communication

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the Repository**
```bash
git clone https://github.com/serinaanuar/TenantRentalSystem_SoftArc.git
git checkout -b feature/your-feature-name
```

2. **Make Changes**
- Follow PSR-12 code standards for PHP
- Follow Airbnb JavaScript style guide for React/JS
- Write meaningful commit messages

3. **Test Your Changes**
```bash
php artisan test
npm run test
```

4. **Submit Pull Request**
- Describe your changes clearly
- Link any related issues
- Wait for review

### Code Standards
- PHP: PSR-12 (CodeSniffer)
- JavaScript: Airbnb + Prettier
- Database: Follow naming conventions (snake_case for tables/columns)

---

## 📝 Git Workflow

### Branch Naming
- `feature/description` - New features
- `bugfix/description` - Bug fixes
- `hotfix/description` - Emergency fixes
- `maintenance` - Main development branch

### Commit Message Format
```
[TYPE]: Brief description

Detailed explanation if needed
- Bullet point 1
- Bullet point 2

Fixes #issue_number
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `refactor`: Code refactoring
- `docs`: Documentation
- `test`: Adding tests
- `chore`: Build, dependencies

---

## 📄 License

This project is licensed under the MIT License - see LICENSE file for details.

---

## 📞 Support & Contact

For support and questions:
- **Email**: support@tenantrentalsystem.com
- **Issues**: [GitHub Issues](https://github.com/serinaanuar/TenantRentalSystem_SoftArc/issues)
- **Wiki**: [GitHub Wiki](https://github.com/serinaanuar/TenantRentalSystem_SoftArc/wiki)

---

## 🙏 Acknowledgments

- Laravel Community for excellent framework
- React team for powerful UI library
- Inertia.js for seamless backend-frontend integration
- All contributors and users who provided feedback

---

## 📈 Roadmap

### Upcoming Features
- [ ] Mobile Application (React Native)
- [ ] AI-powered Property Recommendations
- [ ] Advanced Analytics with ML Insights
- [ ] Property Virtual Tours
- [ ] Blockchain-based Transactions
- [ ] Multi-language Support
- [ ] Advanced Payment Gateway Integrations
- [ ] Automated Maintenance Scheduling
- [ ] Property Insurance Integration
- [ ] Tenant Credit Scoring

### Q1 2026
- Property valuation tool
- Automated lease agreement generation
- Enhanced reporting features

### Q2 2026
- Mobile application launch
- AI recommendation system
- Advanced property search filters

---

**Last Updated**: January 2026

**Version**: 1.0.0

**Status**: Active Development ✅

---

### Quick Links
- [GitHub Repository](https://github.com/serinaanuar/TenantRentalSystem_SoftArc)
- [Live Demo](https://tenantrentalsystem.com)
- [Documentation Wiki](https://github.com/serinaanuar/TenantRentalSystem_SoftArc/wiki)
- [Report Issues](https://github.com/serinaanuar/TenantRentalSystem_SoftArc/issues)

---

**Happy Building! 🚀**
