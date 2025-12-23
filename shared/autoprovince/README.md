# AutoProvince - eDonation Edition

ระบบเลือกจังหวัด อำเภอ ตำบล แบบ Cascading Dropdown

## 📦 Database Tables

Tables ใช้ prefix `edonation_`:
- `edonation_provinces` - 77 จังหวัด
- `edonation_districts` - อำเภอ/เขต
- `edonation_subdistricts` - ตำบล/แขวง พร้อมรหัสไปรษณีย์

## 🚀 การใช้งาน

### 1. ใน Web (PHP)

```php
<?php
// Include database config
require_once __DIR__ . '/../config/database.php';

// Include AutoProvince class
require_once __DIR__ . '/../../shared/autoprovince/AutoProvince.php';

$pdo = Database::getInstance();
$autoProv = new AutoProvince($pdo);

// Get provinces as HTML options
echo $autoProv->getProvinceOptions($selectedProvinceId);

// Or get as array
$provinces = $autoProv->getProvinces();
```

### 2. ใน JavaScript

```html
<!-- Include CSS -->
<link rel="stylesheet" href="/shared/autoprovince/assets/autoprovince.css">

<!-- Select2 (optional but recommended) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

<!-- HTML Form -->
<div class="address-section">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">จังหวัด</label>
            <select id="province" class="form-select"></select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">อำเภอ/เขต</label>
            <select id="district" class="form-select" disabled></select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">ตำบล/แขวง</label>
            <select id="subdistrict" class="form-select" disabled></select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">รหัสไปรษณีย์</label>
            <input type="text" id="postcode" class="form-control" readonly>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="/shared/autoprovince/assets/autoprovince.js"></script>

<script>
// Initialize
AutoProvince.init({
    apiPath: '/appdev/edonation/shared/autoprovince/api.php',
    useSelect2: true,
    onAddressComplete: function(address) {
        console.log('Address selected:', address);
        console.log('Formatted:', AutoProvince.formatAddress());
    }
});
</script>
```

### 3. API Endpoints

| Action | Method | Parameters | Description |
|--------|--------|------------|-------------|
| `get_provinces` | GET | - | รายการจังหวัดทั้งหมด |
| `get_districts` | POST | `province_id` | รายการอำเภอตามจังหวัด |
| `get_subdistricts` | POST | `district_id` | รายการตำบลตามอำเภอ |
| `get_address` | POST | `subdistrict_id` | ข้อมูลที่อยู่แบบเต็ม |
| `search` | GET | `q`, `limit` | ค้นหาที่อยู่ |

**Example API Calls:**
```javascript
// Get provinces
fetch('/shared/autoprovince/api.php?action=get_provinces')

// Get districts
fetch('/shared/autoprovince/api.php?action=get_districts', {
    method: 'POST',
    body: new URLSearchParams({ province_id: 1 })
})

// Search
fetch('/shared/autoprovince/api.php?action=search&q=เชียงใหม่&limit=20')
```

## ⚙️ Configuration Options

```javascript
AutoProvince.init({
    apiPath: '/shared/autoprovince/api.php',  // API endpoint path
    provinceSelector: '#province',             // Province select ID
    districtSelector: '#district',             // District select ID
    subdistrictSelector: '#subdistrict',       // Subdistrict select ID
    postcodeSelector: '#postcode',             // Postcode input ID
    loaderSelector: '#loader',                 // Loading overlay ID
    useSelect2: true,                          // Use Select2 plugin
    select2Theme: 'bootstrap-5',               // Select2 theme
    
    // Callbacks
    onProvinceChange: function(data) { },
    onDistrictChange: function(data) { },
    onSubdistrictChange: function(data) { },
    onAddressComplete: function(address) { }
});
```

## 📝 Public Methods

```javascript
// Get selected address object
const address = AutoProvince.getAddress();
// Returns: { province: {id, name}, district: {id, name}, subdistrict: {id, name}, postcode }

// Get formatted address string
const formatted = AutoProvince.formatAddress();
// Returns: "ต.สุเทพ อ.เมืองเชียงใหม่ จ.เชียงใหม่ 50200"

// Set values programmatically
AutoProvince.setValues({
    provinceId: 1,
    districtId: 101,
    subdistrictId: 10101
});

// Search addresses
AutoProvince.search('เชียงใหม่').then(response => {
    console.log(response.data);
});

// Reload provinces
AutoProvince.reload();
```

## 🎨 CSS Classes

| Class | Description |
|-------|-------------|
| `.address-section` | Container for address form |
| `.address-row` | Grid layout for address fields |
| `.autoprovince-container` | Container with loading overlay |
| `.autoprovince-loader` | Loading overlay |
| `.autoprovince-compact` | Compact mode styling |

## 📜 License

MIT License
