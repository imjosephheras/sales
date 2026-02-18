# Contract Generator - Integration Implementation Guide

## 📋 Overview

This implementation connects the **Form Contract** (8-section form) with the **Contract Generator** (3-column editor with live preview).

### Flow Diagram

```
┌─────────────────────────┐
│   Form Contract         │
│   (8 Sections)          │
│                         │
│   1. Request Info       │
│   2. Client Info        │
│   3. Operational        │
│   4. Economic           │
│   5. Contract Info      │
│   6. Observations       │
│   7. Scope of Work      │
│   8. Photos             │
│                         │
│   [Submit] ────────────┐│
└─────────────────────────┘│
                           │
                           ▼
┌─────────────────────────────────────────┐
│   enviar_correo.php                     │
│                                         │
│   1. ✅ Save to database (requests)     │
│   2. 📧 Send email with PDF             │
│   3. 🔄 Redirect to Contract Generator  │
└─────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────┐
│   Contract Generator                    │
│   ┌──────────┬──────────┬──────────┐   │
│   │  Inbox   │  Editor  │ Preview  │   │
│   │  📥      │   ✏️     │   👁️    │   │
│   ├──────────┼──────────┼──────────┤   │
│   │ Request1 │ [Fields] │ [Live    │   │
│   │ Request2 │ Service  │  Preview]│   │
│   │ Request3 │ Client   │          │   │
│   │ Task1    │ Price    │ [PDF]    │   │
│   │ Task2    │ ...      │          │   │
│   └──────────┴──────────┴──────────┘   │
└─────────────────────────────────────────┘
```

---

## 🗄️ Database Schema

### Table: `requests`

```sql
CREATE TABLE IF NOT EXISTS `requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,

  -- Section 1: Request Information
  `Service_Type` VARCHAR(100),
  `Request_Type` VARCHAR(100),
  `Priority` VARCHAR(50),
  `Requested_Service` VARCHAR(200),

  -- Section 2: Client Information
  `client_name` VARCHAR(200),
  `Client_Title` VARCHAR(100),
  `Email` VARCHAR(200),
  `Number_Phone` VARCHAR(50),
  `Company_Name` VARCHAR(200),
  `Company_Address` TEXT,
  `Is_New_Client` VARCHAR(10),

  -- Section 3: Operational Details
  `Site_Visit_Conducted` VARCHAR(10),
  `frequency_period` VARCHAR(50),
  `week_days` TEXT,              -- JSON array
  `one_time` VARCHAR(100),
  `Invoice_Frequency` VARCHAR(50),
  `Contract_Duration` VARCHAR(100),

  -- Section 4: Economic Information
  `Seller` VARCHAR(100),
  `PriceInput` VARCHAR(100),
  `Prime_Quoted_Price` VARCHAR(100),

  -- Janitorial Services (Section 18)
  `includeJanitorial` VARCHAR(10),
  `type18` TEXT,                 -- JSON array
  `write18` TEXT,
  `time18` TEXT,
  `freq18` TEXT,
  `desc18` TEXT,
  `subtotal18` TEXT,
  `total18` VARCHAR(50),
  `taxes18` VARCHAR(50),
  `grand18` VARCHAR(50),

  -- Kitchen Cleaning (Section 19)
  `includeKitchen` VARCHAR(10),
  `type19` TEXT,                 -- JSON array
  `time19` TEXT,
  `freq19` TEXT,
  `desc19` TEXT,
  `subtotal19` TEXT,
  `total19` VARCHAR(50),
  `taxes19` VARCHAR(50),
  `grand19` VARCHAR(50),

  -- Staff (Section 20)
  `includeStaff` VARCHAR(10),
  `base_staff` TEXT,             -- JSON object
  `increase_staff` TEXT,
  `bill_staff` TEXT,

  -- Section 5: Contract Information
  `inflationAdjustment` VARCHAR(50),
  `totalArea` VARCHAR(100),
  `buildingsIncluded` TEXT,
  `startDateServices` DATE,

  -- Section 6: Observations
  `Site_Observation` TEXT,
  `Additional_Comments` TEXT,
  `Email_Information_Sent` TEXT,

  -- Section 7: Scope of Work
  `Scope_Of_Work` TEXT,          -- JSON array

  -- Section 8: Photos
  `photos` TEXT,                 -- JSON array of paths

  -- Status & Metadata
  `status` VARCHAR(50) DEFAULT 'pending',
  `document_type` VARCHAR(50),
  `document_number` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_status` (`status`),
  INDEX `idx_company` (`Company_Name`),
  INDEX `idx_service_type` (`Service_Type`)
);
```

---

## 📁 Files Modified/Created

### 1. Form Contract (`/form_contract/`)

#### Modified Files:
- **`enviar_correo.php`**
  - ✅ Added database save functionality
  - ✅ Saves all form data to `requests` table
  - ✅ Updated confirmation page with link to Contract Generator
  - ✅ Includes request_id in redirect URL

### 2. Contract Generator (`/contract_generator/`)

#### Created Files:
- **`controllers/get_pending_requests.php`**
  - Endpoint to fetch pending requests from `requests` table
  - Returns formatted data for inbox display
  - Includes priority badges and category colors

#### Modified Files:
- **`js/inbox.js`**
  - ✅ Loads both tasks and requests in parallel
  - ✅ Combines and sorts items by date
  - ✅ Handles both 'task' and 'request' types
  - ✅ Auto-loads request when clicked from form submission

- **`index.php`**
  - ✅ Added auto-load script for `request_id` URL parameter
  - ✅ Dispatches event to load specific request on page load

### 3. Database Scripts

#### Created Files:
- **`create_requests_table.sql`**
  - Complete schema for `requests` table
  - Includes all 8 sections from form
  - JSON fields for arrays and complex data

---

## 🚀 Setup Instructions

### Step 1: Create Database Table

Run the SQL script to create the `requests` table:

```bash
mysql -u root -p form < /home/user/sales/create_requests_table.sql
```

Or execute manually:

```bash
cd /home/user/sales
mysql -u root -p
```

```sql
source create_requests_table.sql;
```

### Step 2: Verify Database Connection

Ensure both applications can connect to MySQL:

```bash
# Check if MySQL is running
sudo systemctl status mysql

# Start MySQL if needed
sudo systemctl start mysql
```

### Step 3: Test the Flow

1. **Submit a Form**
   - Go to: `http://localhost/sales/form_contract/`
   - Fill out the 8-section form
   - Click "Submit" → Preview → "Confirm and Send"

2. **Verify Database**
   ```sql
   SELECT id, Company_Name, Service_Type, status, created_at
   FROM requests
   ORDER BY created_at DESC
   LIMIT 5;
   ```

3. **Open Contract Generator**
   - Click "Go to Contract Generator" button on confirmation page
   - OR navigate to: `http://localhost/sales/contract_generator/`
   - Verify request appears in left sidebar (Inbox)

4. **Edit & Generate Contract**
   - Click on request in inbox
   - Edit fields in center panel
   - See live preview in right panel
   - Generate PDF or mark as ready

---

## 🔄 Data Flow

### 1. Form Submission Flow

```php
// form_contract/enviar_correo.php

1. Capture $_POST data from all 8 sections
2. Process uploaded photos
3. Save to database:
   - Connect: getDBConnection()
   - Prepare: JSON encode arrays
   - Execute: INSERT INTO requests
   - Get: $request_id = $pdo->lastInsertId()
4. Generate PDF
5. Send email
6. Redirect to Contract Generator with request_id
```

### 2. Contract Generator Load Flow

```javascript
// contract_generator/js/inbox.js

1. Page loads → loadPendingTasks()
2. Parallel fetch:
   - get_pending_tasks.php (calendar tasks)
   - get_pending_requests.php (form submissions)
3. Combine & sort by date
4. Render in inbox with badges
5. On click → load request data
6. Populate editor & preview
```

### 3. Status Workflow

```
pending → in_progress → completed
   ↓           ↓            ↓
 (New)     (Editing)    (Done/PDF)
```

---

## 🎨 UI Components

### Inbox Panel (Left)

```
┌─────────────────────────┐
│ 📥 Pending (12)         │
├─────────────────────────┤
│ [High] 🏨 Hospitality   │
│ ABC Corp - Contract     │
│ John Doe                │
│ 📅 Jan 23, 2026         │
├─────────────────────────┤
│ [Urgent] 🧹 Janitorial  │
│ XYZ Inc - Quote         │
│ Jane Smith              │
│ 📅 Jan 22, 2026         │
└─────────────────────────┘
```

### Editor Panel (Center)

```
┌─────────────────────────┐
│ Request Information     │
├─────────────────────────┤
│ Service Type: [____]    │
│ Request Type: [____]    │
│ Priority:     [____]    │
│                         │
│ Client Information      │
│ Company:      [____]    │
│ Client Name:  [____]    │
│ Email:        [____]    │
│                         │
│ [Save Draft] [Generate] │
└─────────────────────────┘
```

### Preview Panel (Right)

```
┌─────────────────────────┐
│ 👁️ Live Preview        │
├─────────────────────────┤
│ [PRIME FACILITY LOGO]   │
│                         │
│ CONTRACT AGREEMENT      │
│                         │
│ Company: ABC Corp       │
│ Service: Janitorial     │
│ Price: $5,000/month     │
│                         │
│ [View PDF] [Download]   │
└─────────────────────────┘
```

---

## 🐛 Troubleshooting

### Issue: Requests not showing in inbox

**Check:**
1. MySQL is running: `sudo systemctl status mysql`
2. Database exists: `SHOW DATABASES LIKE 'form';`
3. Table exists: `SHOW TABLES FROM form;`
4. Data exists: `SELECT COUNT(*) FROM requests;`
5. Browser console for errors

**Fix:**
```bash
# Create database if missing
mysql -u root -e "CREATE DATABASE IF NOT EXISTS form;"

# Import schema
mysql -u root form < create_requests_table.sql
```

### Issue: Database connection error

**Check:**
- `form_contract/db_config.php` settings
- `contract_generator/config/db_config.php` settings

Both should use:
```php
DB_HOST: localhost
DB_NAME: form
DB_USER: root
DB_PASS: (empty)
```

### Issue: Request doesn't auto-load from URL

**Check:**
1. URL includes `?request_id=123`
2. Browser console shows: `📋 Auto-loading request ID: 123`
3. Request exists in database: `SELECT * FROM requests WHERE id = 123;`

---

## 📊 Status Indicators

### Priority Badges

| Priority | Color   | Display |
|----------|---------|---------|
| Urgent   | #dc3545 | Red     |
| High     | #fd7e14 | Orange  |
| Normal   | #007bff | Blue    |
| Low      | #6c757d | Gray    |

### Service Type Badges

| Type        | Color   | Icon |
|-------------|---------|------|
| Janitorial  | #28a745 | 🧹   |
| Hospitality | #17a2b8 | 🏨   |
| Other       | #6c757d | 📋   |

---

## ✅ Testing Checklist

- [ ] Form submission saves to database
- [ ] Email sent with PDF attachment
- [ ] Confirmation page shows request ID
- [ ] "Go to Contract Generator" button works
- [ ] Request appears in inbox
- [ ] Request auto-loads when URL has request_id
- [ ] Editor shows all form data
- [ ] Preview updates in real-time
- [ ] PDF generation works
- [ ] Status changes save correctly

---

## 🔮 Future Enhancements

1. **Real-time Sync**
   - WebSocket for live inbox updates
   - Notify when new requests arrive

2. **Advanced Filtering**
   - Filter by status, priority, service type
   - Search by company name or client

3. **Collaborative Editing**
   - Lock requests being edited
   - Show who's viewing/editing

4. **Templates**
   - Pre-fill common contract types
   - Save custom templates

5. **Audit Trail**
   - Track all changes
   - Show revision history

---

## 📝 Notes

- All array data (week_days, Scope_Of_Work, photos, etc.) is stored as JSON TEXT
- Photos are stored as file paths, not base64
- Status workflow: pending → in_progress → completed
- Request ID is auto-incremented from database
- Time zone is set to America/Chicago

---

## 🎯 Summary

This implementation successfully connects the Form Contract (form_contract) with the Contract Generator (contract_generator) by:

1. ✅ Saving form submissions to the `requests` table
2. ✅ Loading requests in the Contract Generator inbox
3. ✅ Allowing seamless transition from form → editor
4. ✅ Providing 3-column interface (Inbox | Editor | Preview)
5. ✅ Supporting both calendar tasks and form requests

The system is now ready for contract generation with live preview and PDF export capabilities.
