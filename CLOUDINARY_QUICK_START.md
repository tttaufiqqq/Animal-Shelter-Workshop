# 🚀 Cloudinary Quick Start

## ⚡ 3-Minute Setup

### Step 1: Fix SSL Certificate (REQUIRED)

```bash
# Find your php.ini
php --ini
```

Open php.ini and find:
```ini
curl.cainfo = C:web-appsCharity-Izzstoragecert.pem
```

**Change to:**
```ini
curl.cainfo = "C:\web-apps\Animal-Shelter-Workshop\storage\cacert.pem"
```

Save and **restart your terminal**.

---

### Step 2: Test Connection

```bash
php test-cloudinary.php
```

**Expected:** `SUCCESS!` with a Cloudinary URL

---

### Step 3: Seed Database

```bash
php artisan db:fresh-all --seed
```

**Watch for:** "Uploading seed images to Cloudinary..."

---

### Step 4: Start Server

```bash
composer dev
```

---

## ✅ Verification Checklist

- [ ] SSL certificate fixed in php.ini
- [ ] `php test-cloudinary.php` shows SUCCESS
- [ ] Seeders complete without errors
- [ ] Images visible in Cloudinary Media Library
- [ ] Can upload new reports/animals with images
- [ ] Images display correctly in browser

---

## 🆘 Quick Troubleshooting

**"cURL error 77"** → Fix php.ini (see Step 1)

**"Invalid configuration"** → Run `php artisan config:clear`

**Images not showing** → Check Cloudinary credentials in .env

**Seeder fails** → Check internet connection, try again

---

## 📱 Share With Team

**Cloudinary Credentials (ALL TEAM MEMBERS USE THESE):**
```env
CLOUDINARY_URL=cloudinary://941895174125793:GaktyH47qtxPLB3dvt_-WH0PDNA@dpqvlddix
CLOUDINARY_CLOUD_NAME=dpqvlddix
CLOUDINARY_API_KEY=941895174125793
CLOUDINARY_API_SECRET=GaktyH47qtxPLB3dvt_-WH0PDNA
```

---

**Full Guide:** `CLOUDINARY_IMPLEMENTATION_COMPLETE.md`
