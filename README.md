## Restaurant POS and Website
**Features:**
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



**Steps to run the project locally for Netbeans Manually:**

1. Open XAMPP, start Apache and MySQL.
2. Create a new project in Netbeans named `RestaurantProject`.
3. Under categories, select PHP, the PHP Application under Projects.
4. In Run Configuration, the "Run As" should be Local Web Site. (If your using Xampp).
5. Then Finish.
6. Delete the `setup_completed.flag` file in the RestaurantProject-main. (Extracted version)
7. Copy all the folders and files (adminSide, customerSide, index.php, and restaurantDB.txt) from the RestaurantProject-main into the `Source Files` directory.
8. Make sure there is no database named `restaurantdb`.
9. Run the project.

## Example accounts

| Role | Email | Password |
|---|---|---|
| Waiter | 41 | Strongpass123! |
| Chef | 54 | Strongpass123! |
| Manager | 55 | Strongpass123! |
| Admin | 99999 | 12345 |
