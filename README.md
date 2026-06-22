# ☕ HADERO COFFEE - XAMPP (PHP + MySQL) SOLUTION

This directory contains the production-ready PHP & MySQL implementation of the Hadero Gourmet Coffee Menu & Administration panel, styled identically to your HTML design.

---

## 🚀 Easy Setup Guide (How to run locally on XAMPP)

Follow these simple steps to deploy and test the system locally on your laptop:

### 1. Copy Files to XAMPP Directory
1. Locate your local XAMPP installation directory (normally `C:\xampp` on Windows, or `/Applications/XAMPP` on macOS).
2. Open the subfolder called **`htdocs`**.
3. Create a new directory called **`hadero`** inside `htdocs`.
4. Copy all four files from this folder into your new `hadero` folder:
   - `db.php` (Connection settings)
   - `schema.sql` (MySQL database script)
   - `index.php` (Dynamic Menu page)
   - `admin.php` (Administration Portal)

### 2. Start XAMPP Servers
1. Open the **XAMPP Control Panel** applet.
2. Click **`Start`** next to **Apache**.
3. Click **`Start`** next to **MySQL**.

### 3. Setup the MySQL Database
1. Open your web browser and navigate to: **`http://localhost/phpmyadmin`**
2. Click on the **Database** tab near the top.
3. In the "Create database" field, type **`hadero_db`** and select `utf8mb4_unicode_ci` from the dropdown, then click **`Create`**.
4. Once the database is created, select `hadero` from the left sidebar.
5. Click on the **Import** tab on the top menu bar.
6. Click **`Choose File`** and select the **`schema.sql`** file you copied earlier.
7. Scroll to the bottom of the page and click **`Import`** (or **Go**).
*This creates the `menu_items` table and seeds it with your 9 gourmet items!*

### 4. Visit the Application!
Now open your web browser and load these URLs to interact with your live local site:
- **Customer Dynamic Menu:** **`http://localhost/hadero/index.php`**
- **Admin Dashboard Panel:** **`http://localhost/hadero/admin.php`**

---

## 🛠️ Integrated Capabilities Explained

1. **Durable Database CRUD Routing:** Uses PDO (PHP Data Objects) statements to interface with MySQL, completely preventing SQL injections.
2. **Dynamic Menu Loading:** Adding, editing or deleting items from `admin.php` is executed directly on the MySQL server, instantly updating your customer index.
3. **Take Web Photo (Camera):** Integrates high-speed browser-level navigator API streams. Clicking "Take Live Photo" activates your laptop webcam finder; taking a snapshot inputs it as a Base64-compressed secure string straight to the database.
4. **Device File Uploads:** Converts chosen graphics files into portable string blobs so that directories stay self-contained and immune from file reference breakage.
5. **Image Selection Reuse Gallery:** Scan existing entries in the database and groups them as rapid Selection Grid Thumbnails in the form. Simply tap any previous picture card to reuse it with zero reload latency!
