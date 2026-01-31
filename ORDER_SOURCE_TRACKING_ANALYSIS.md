# Order Source Tracking - Complete Analysis & Implementation Plan

## 📋 Current State Analysis

### 1. **Order Model Structure** (`app/Models/Order.php`)
- **Location:** `/app/Models/Order.php`
- **Current Fillable Fields:** 31 fields (order_number, user_id, shipping details, amounts, status, payment info, timestamps)
- **Missing Field:** `source` ❌
- **Relationships:** 
  - `belongsTo(User)` 
  - `hasMany(OrderItem)`

### 2. **Orders Table Schema** (`database/migrations/2025_10_11_124501_create_orders_table.php`)
- **Current Columns:** order_number, user_id, shipping_* (7 fields), subtotal, shipping_cost, tax, discount, total, status, payment_status, payment_method, payment_reference, invoice_path, customer_notes, admin_notes, paid_at, shipped_at, delivered_at, timestamps
- **Missing Column:** `source` ❌
- **No Index:** Needed for filtering by source

### 3. **Order Creation Flow** (`app/Http/Controllers/OrderController.php`)
- **Route:** `POST /api/orders/create` (line 75 in routes/api.php)
- **Middleware:** `auth:sanctum` (users only)
- **Method:** `createOrder()` (starts at line 120)
- **Current Process:**
  1. Validates shipping & payment info
  2. Creates Order with 18 fields passed to `Order::create()`
  3. Does NOT read HTTP headers
  4. Does NOT differentiate between web and mobile requests

### 4. **Flutter ApiService** (`frontend/lib/services/api_service.dart`)
- **Methods:** `get()`, `post()`, `put()`, `delete()`, `postMultipart()`
- **Header Management:** Uses `ApiConfig.headers(token: token)` method
- **Current Headers:** Content-Type, Accept, Authorization
- **Gap:** No custom headers being injected (X-App-Source)

### 5. **ApiConfig** (`frontend/lib/config/api_config.dart`)
- **Location:** `frontend/lib/config/api_config.dart`
- **Method:** `headers()` at line 95+
- **Current Implementation:** Returns Map with Content-Type, Accept, Authorization headers only
- **Needs:** Addition of 'X-App-Source: mobile' header for mobile requests

---

## 🎯 Implementation Plan

### Step 1: Database Migration ✅ TODO
**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_source_to_orders_table.php`
**Purpose:** Add `source` column to `orders` table without breaking existing data

**Key Points:**
- Use nullable column with default 'web' for backwards compatibility
- Add index for filtering by source
- Safe rollback support

**SQL Logic:**
```sql
-- up()
ALTER TABLE orders ADD COLUMN source VARCHAR(50) DEFAULT 'web' NULLABLE;
CREATE INDEX idx_orders_source ON orders(source);

-- down()
DROP INDEX idx_orders_source ON orders;
ALTER TABLE orders DROP COLUMN source;
```

---

### Step 2: Update Order Model ✅ TODO
**File:** `app/Models/Order.php`
**Lines to Modify:** $fillable array (lines 13-39)

**Current:**
```php
protected $fillable = [
    'order_number',
    'user_id',
    // ... 29 more fields
    'paid_at',
    'shipped_at',
    'delivered_at'
    // Missing: 'source'
];
```

**New:**
```php
protected $fillable = [
    'order_number',
    'user_id',
    // ... 29 existing fields (unchanged)
    'paid_at',
    'shipped_at',
    'delivered_at',
    'source'  // ← ADD THIS
];
```

---

### Step 3: Update OrderController ✅ TODO
**File:** `app/Http/Controllers/OrderController.php`
**Method:** `createOrder()` (starts at line 120)
**Lines to Modify:** Order creation block (around line 217)

**Current Code (line 217-238):**
```php
$order = Order::create([
    'order_number' => Order::generateOrderNumber(),
    'user_id' => $user->id,
    'shipping_name' => $request->shipping_name,
    // ... other fields
    'customer_notes' => $request->customer_notes
]);
```

**New Code:**
```php
// Read source from X-App-Source header (default to 'web' for backwards compatibility)
$source = $request->header('X-App-Source', 'web');

$order = Order::create([
    'order_number' => Order::generateOrderNumber(),
    'user_id' => $user->id,
    'source' => $source,  // ← ADD THIS
    'shipping_name' => $request->shipping_name,
    // ... other fields (unchanged)
    'customer_notes' => $request->customer_notes
]);
```

**Addition:** Add at top of method or in a helper:
```php
// Valid sources
$validSources = ['web', 'mobile', 'admin'];
if (!in_array($source, $validSources)) {
    $source = 'web'; // fallback to web for unknown sources
}
```

---

### Step 4: Update Flutter ApiConfig ✅ TODO
**File:** `frontend/lib/config/api_config.dart`
**Method:** `headers()` (around line 95)

**Current Code:**
```dart
static Map<String, String> headers({String? token}) {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };
}
```

**New Code:**
```dart
static Map<String, String> headers({String? token}) {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-App-Source': 'mobile',  // ← ADD THIS
    if (token != null) 'Authorization': 'Bearer $token',
  };
}
```

---

### Step 5: Run Migration ✅ TODO
**Command:**
```bash
cd /Users/israa/Desktop/kazaria_marketplace
php artisan migrate
```

**Expected Output:**
```
Migrating: YYYY_MM_DD_HHMMSS_add_source_to_orders_table
Migrated: YYYY_MM_DD_HHMMSS_add_source_to_orders_table
```

---

## 📊 Expected Behavior After Implementation

### Web Orders (from web.kazaria-ci.com)
- **Source Header:** Not set (defaults to 'web' in Laravel)
- **Database Value:** `source = 'web'`
- **SQL Query:** `SELECT * FROM orders WHERE source = 'web'`

### Mobile Orders (from Flutter app)
- **Source Header:** `X-App-Source: mobile`
- **Database Value:** `source = 'mobile'`
- **SQL Query:** `SELECT * FROM orders WHERE source = 'mobile'`

### Existing Orders
- **Migration Backfill:** All existing orders get `source = 'web'` (default value)
- **No Data Loss:** Migration is non-breaking

---

## ✅ Verification Checklist

After implementation, test:
- [ ] Migration runs without errors
- [ ] `orders` table has `source` column with default 'web'
- [ ] Web orders created show `source = 'web'`
- [ ] Mobile orders created show `source = 'mobile'`
- [ ] Can filter orders by source: `WHERE source = 'mobile'`
- [ ] OrderController accepts X-App-Source header
- [ ] Flutter app injects X-App-Source: mobile for all requests

---

## 📍 File Locations Summary

| Component | File Path | Action |
|-----------|-----------|--------|
| Migration | `database/migrations/YYYY_MM_DD_add_source_to_orders_table.php` | CREATE NEW |
| Model | `app/Models/Order.php` | MODIFY (line 13-39) |
| Controller | `app/Http/Controllers/OrderController.php` | MODIFY (line 217) |
| ApiConfig | `frontend/lib/config/api_config.dart` | MODIFY (line 95) |

---

## 🚀 Advantages of This Approach

✅ **Non-Breaking:** Defaults to 'web' for existing orders  
✅ **Simple:** Only 4 files, minimal changes  
✅ **Scalable:** Can extend to 'mobile:ios', 'mobile:android', 'admin', etc.  
✅ **Queryable:** Can filter, group, and analyze orders by source  
✅ **Reversible:** Easy rollback with down() migration  

---

## Next Steps

1. Create migration file
2. Update Order model fillable array
3. Update OrderController createOrder() method
4. Update ApiConfig headers() method
5. Run migration: `php artisan migrate`
6. Test on physical device with local server
7. Verify orders show correct source in database

