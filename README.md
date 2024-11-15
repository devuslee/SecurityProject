## Restaurant POS and Website
**System Features:**
* **Customer Side (customerSide Folder):** Stores the website and allows customers to:
    * Make reservations
    * Register for accounts
    * View profile points
* **Staff Side (adminSide Folder):** Stores the panels and allows staff to:
    * Take orders
    * Send orders to the kitchen
    * Process payments
    * Print receipts
    * Manage CRUD operations
    * View user preferences
    * Download reports
    * View charts and graph

**Security Features Implemented for 6005CEM:**
 - Input Validation and Password Hashing
 - Error Handling
 - Data Privacy Policy
 - Session Management for Staff Module
 - Role-Based Access Control
 - Secure Communication (SSL Certification)
 - SQL Injection
 - Cross-Site Scripting (XSS) Protection
 - Logging

## *User Manual*
**Steps to run the project locally for Netbeans Manually:**

1. Open XAMPP, start Apache and MySQL.
2. Create a new project in Netbeans named `RestaurantProject`.
3. Clone the repository into "RestaurantProject'.
4. Go to your database and create a new database called "restaurantdb".
5. Click import and select the file "restaurantdb.sql". 
6. Go to localhost/RestaurantProjects
7. Run the project.

## Example accounts

| Role | Email | Password |
|---|---|---|
| Waiter | 41 | Strongpass123! |
| Chef | 54 | Strongpass123! |
| Manager | 55 | Strongpass123! |
| Admin | 99999 | 12345 |
