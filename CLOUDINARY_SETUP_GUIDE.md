# 🌥️ Cloudinary Setup Guide

## Problem Solved

**Issue:** Images uploaded by one team member (e.g., Eilya) were not visible to others because images were stored locally on each machine's filesystem.

**Solution:** Cloudinary - a cloud-based image storage service that provides centralized storage accessible to all team members.

---

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ Git access to the project repository
- ✅ Composer installed
- ✅ A Cloudinary account (free tier available)

---

## 🚀 Quick Setup (For All Team Members)

### Step 1: Pull Latest Changes

```bash
git pull origin feature/distributed-architecture
```

This includes:
- Cloudinary package installation
- Updated controllers (StrayReportingManagementController, AnimalManagementController)
- Updated Blade views
- Updated Image model with URL accessor

### Step 2: Install Dependencies

```bash
composer install
```

This will install the `cloudinary-labs/cloudinary-laravel` package.

### Step 3: Create Cloudinary Account (ONE ACCOUNT FOR ENTIRE TEAM)

**IMPORTANT:** Only ONE person needs to do this. Share the credentials with all team members.

1. Go to [https://cloudinary.com/users/register/free](https://cloudinary.com/users/register/free)
2. Sign up for a free account (no credit card required)
3. After signing in, go to the **Dashboard**
4. You'll see your credentials:
   - **Cloud Name:** `your-cloud-name`
   - **API Key:** `123456789012345`
   - **API Secret:** `abcdefghijklmnopqrstuvwxyz`

**Free Tier Limits:**
- 25 GB storage
- 25 GB bandwidth/month
- More than enough for your project!

### Step 4: Configure .env (ALL TEAM MEMBERS)

Update your `.env` file with the **SAME** Cloudinary credentials:

```env
# Change this line (around line 119)
FILESYSTEM_DISK=cloudinary

# Add these lines (around line 122-127) - ALL TEAM MEMBERS USE THE SAME VALUES
CLOUDINARY_URL=cloudinary://YOUR_API_KEY:YOUR_API_SECRET@YOUR_CLOUD_NAME
CLOUDINARY_UPLOAD_PRESET=
CLOUDINARY_NOTIFICATION_URL=
```

**Example:**
```env
FILESYSTEM_DISK=cloudinary

CLOUDINARY_URL=cloudinary://123456789012345:abcdefghijklmnopqrstuvwxyz@your-cloud-name
CLOUDINARY_UPLOAD_PRESET=
CLOUDINARY_NOTIFICATION_URL=
```

**⚠️ CRITICAL:** All team members MUST use the exact same `CLOUDINARY_URL` value!

### Step 5: Test the Setup

1. Start your development server:
   ```bash
   composer dev
   ```

2. Upload a test image:
   - Go to Stray Reporting module
   - Create a new report with an image
   - Submit the report

3. Verify on Cloudinary:
   - Go to [https://cloudinary.com/console/media_library](https://cloudinary.com/console/media_library)
   - You should see your uploaded image in the `reports` folder

4. Verify on other team member's machine:
   - Another team member should pull the latest data
   - They should be able to see the image uploaded by you

---

## 🔍 What Changed?

### 1. Controllers Updated

**Files Modified:**
- `app/Http/Controllers/StrayReportingManagementController.php`
- `app/Http/Controllers/AnimalManagementController.php`

**Changes:**
```php
// BEFORE (Local Storage)
$path = $image->store('reports', 'public');
Storage::disk('public')->delete($filePath);

// AFTER (Cloudinary)
$path = $image->store('reports', 'cloudinary');
Storage::disk('cloudinary')->delete($filePath);
```

### 2. Image Model Updated

**File:** `app/Models/Image.php`

**Added:** URL accessor that automatically generates Cloudinary URLs

```php
protected $appends = ['url'];

public function getUrlAttribute()
{
    return Storage::url($this->image_path);
}
```

**Benefit:** When you access `$image->url` in Blade or JavaScript, it automatically returns the full Cloudinary URL.

### 3. Blade Views Updated

**Files Modified:** (10 files)
- `resources/views/stray-reporting/*.blade.php` (4 files)
- `resources/views/animal-management/*.blade.php` (3 files)
- `resources/views/booking-adoption/*.blade.php` (3 files)

**Changes:**
```blade
{{-- BEFORE (Local Storage) --}}
<img src="{{ asset('storage/' . $image->image_path) }}">

{{-- AFTER (Cloudinary) --}}
<img src="{{ Storage::url($image->image_path) }}">
{{-- OR using the new accessor --}}
<img src="{{ $image->url }}">
```

**JavaScript:**
```javascript
// BEFORE
<img src="/storage/${img.image_path}">

// AFTER
<img src="${img.url}">
```

---

## 📂 Folder Structure on Cloudinary

Images are organized in folders based on their module:

```
your-cloud-name/
├── reports/              # Stray report images (Eilya's module)
│   ├── abc123.jpg
│   ├── def456.png
│   └── ...
└── animal_images/        # Animal images (Shafiqah's module)
    ├── 1234567890_abc.jpg
    ├── 1234567891_def.jpg
    └── ...
```

---

## 🧪 Testing Checklist

After setup, test these scenarios:

- [ ] **Upload Report Image (Eilya's Module)**
  - Create a new stray report with images
  - Verify image appears on report detail page
  - Check another team member can see the image

- [ ] **Upload Animal Image (Shafiqah's Module)**
  - Create a new animal with images
  - Verify image appears on animal detail page
  - Check another team member can see the image

- [ ] **Edit Animal Images**
  - Add new images to existing animal
  - Delete images from existing animal
  - Verify changes reflect on all machines

- [ ] **Delete Report**
  - Delete a report with images
  - Verify images are removed from Cloudinary (check Media Library)

- [ ] **JavaScript Image Viewer**
  - Open "My Reports" modal
  - Verify images load correctly
  - Click to open image in full view

---

## 🐛 Troubleshooting

### Issue 1: "Class 'CloudinaryLabs\CloudinaryLaravel\CloudinaryServiceProvider' not found"

**Solution:**
```bash
composer install
# OR
composer require cloudinary-labs/cloudinary-laravel
```

### Issue 2: Images Not Uploading

**Symptoms:** Error message when uploading images

**Solutions:**

1. **Check .env configuration:**
   ```bash
   # Verify these lines exist and are correct
   FILESYSTEM_DISK=cloudinary
   CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
   ```

2. **Verify Cloudinary credentials:**
   - Go to [https://cloudinary.com/console](https://cloudinary.com/console)
   - Copy the **API Environment variable** value
   - It should look like: `cloudinary://123456789012345:abcdefghijklmnopqrstuvwxyz@your-cloud-name`

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Issue 3: Images Not Displaying (Broken Image Icon)

**Symptoms:** Images appear as broken icons or don't load

**Solutions:**

1. **Check browser console for errors:**
   - Open DevTools (F12)
   - Look for 404 or CORS errors

2. **Verify Image model has URL accessor:**
   ```bash
   # Check app/Models/Image.php has:
   protected $appends = ['url'];
   public function getUrlAttribute() { ... }
   ```

3. **Check Cloudinary Media Library:**
   - Go to [https://cloudinary.com/console/media_library](https://cloudinary.com/console/media_library)
   - Verify the image exists
   - Click on the image and check the URL

### Issue 4: "Old" Local Images Still Showing

**Symptoms:** Some images still reference `/storage/` instead of Cloudinary URLs

**Solution:**
```bash
# Re-upload those images, or manually update the database
# Update image paths to use Cloudinary paths
```

### Issue 5: Different Team Members See Different Images

**Symptoms:** Team members see images uploaded from their machine only

**Cause:** Not all team members are using the same Cloudinary credentials

**Solution:**
1. Verify ALL team members have the exact same `CLOUDINARY_URL` in their `.env`
2. Run `php artisan config:clear` on all machines
3. Re-upload test images

---

## 💰 Cloudinary Free Tier Limits

Monitor your usage at: [https://cloudinary.com/console](https://cloudinary.com/console)

| Resource | Free Tier Limit | Estimated Usage |
|----------|----------------|-----------------|
| Storage | 25 GB | ~25,000 images (1MB each) |
| Bandwidth | 25 GB/month | ~25,000 image views/month |
| Transformations | 25,000/month | Unlimited for this project |

**Tips to Stay Within Limits:**
- Compress images before uploading (use `quality: auto` in Cloudinary settings)
- Delete test images regularly
- Use thumbnails for image listings (Cloudinary can auto-generate)

---

## 🔐 Security Best Practices

1. **Never commit .env to Git:**
   - ✅ `.env` is already in `.gitignore`
   - ❌ DO NOT commit Cloudinary credentials to the repository

2. **Share credentials securely:**
   - Use encrypted messaging (WhatsApp, Telegram)
   - Or use a password manager (LastPass, 1Password)

3. **Restrict API access:**
   - In Cloudinary dashboard, go to Settings → Security
   - Enable "Restrict media uploads" if needed

---

## 📚 Additional Resources

- **Cloudinary Dashboard:** [https://cloudinary.com/console](https://cloudinary.com/console)
- **Cloudinary Media Library:** [https://cloudinary.com/console/media_library](https://cloudinary.com/console/media_library)
- **Laravel Cloudinary Docs:** [https://github.com/cloudinary-labs/cloudinary-laravel](https://github.com/cloudinary-labs/cloudinary-laravel)
- **Cloudinary PHP SDK:** [https://cloudinary.com/documentation/php_integration](https://cloudinary.com/documentation/php_integration)

---

## 📞 Support

If you encounter issues:

1. **Check this guide** for troubleshooting steps
2. **Check Laravel logs:** `storage/logs/laravel.log`
3. **Ask in team group chat** with:
   - Error message
   - What you were doing when it happened
   - Screenshots if applicable

---

## ✅ Migration Complete!

Once all team members have completed setup:

- ✅ All images are stored centrally on Cloudinary
- ✅ All team members can see all uploaded images
- ✅ No more "image not found" issues
- ✅ Images persist even if local database is refreshed

**Next Steps:**
1. Delete local images in `storage/app/public/` (optional cleanup)
2. Re-upload existing important images to Cloudinary
3. Continue development as normal!

---

**Generated:** 2025-12-26
**Project:** Animal Rescue & Adoption Management System
**Module:** Distributed Image Storage Migration
