# Audit Log IP Address Capture - Machine IPv4

## Overview

The audit logging system now captures the **actual machine's network IPv4 address** instead of the loopback address (127.0.0.1). This provides accurate tracking of which physical machine performed each action.

## Changes Made

### 1. New Helper Function: `getServerIpAddress()`

**Location:** `app/helpers.php:107-167`

**Purpose:** Retrieves the server/machine's actual IPv4 address dynamically

**Features:**
- **Cross-platform support:** Works on Windows, Linux, and macOS
- **Multiple detection methods:**
  1. `$_SERVER['SERVER_ADDR']` (works in some environments)
  2. System commands:
     - **Windows:** `ipconfig` → Parses "IPv4 Address" line
     - **Linux/macOS:** `hostname -I` → Gets first non-loopback IPv4
  3. PHP fallback: `gethostbyname(gethostname())`
- **Filters out loopback:** Ensures 127.0.0.1 is never returned
- **Error handling:** Returns null if unable to determine IP

### 2. Updated `getRealIpAddress()` Method

**Location:** `app/Services/AuditService.php:33-81`

**Changes:**
- When request IP is localhost (127.0.0.1 or ::1), calls `getServerIpAddress()`
- Caches the result in session (`user_real_ip`) for performance
- Falls back to localhost only if unable to determine machine IP

**Priority order:**
1. X-Forwarded-For header (proxy/load balancer)
2. X-Real-IP header
3. Team member email mapping (for SSH tunnel scenario)
4. Request IP → If localhost, get actual machine IP
5. Fallback to request IP

## How It Works

### Example Scenario (Your Machine)

**Your IPv4 from ipconfig:** `10.18.26.156`

**Before:**
```
Column: ip_address
Value:  127.0.0.1   ← Loopback address (not useful)
```

**After:**
```
Column: ip_address
Value:  10.18.26.156   ← Your actual machine IP
```

### For Team Members

Each team member's machine will automatically capture their own IPv4 address:

| Team Member | Machine IP (Example) | Audit Log Captures |
|-------------|---------------------|-------------------|
| Taufiq | 10.18.26.156 | 10.18.26.156 |
| Danish | 10.18.26.18 | 10.18.26.18 |
| Eilya | 10.18.26.14 | 10.18.26.14 |
| Atiqah | 10.18.26.84 | 10.18.26.84 |
| Shafiqah | 10.18.26.121 | 10.18.26.121 |

## How to Verify

### Method 1: Check Current IP Capture

Open `tinker` to test the helper function:

```bash
php artisan tinker
```

```php
// Get your machine's IP
getServerIpAddress();
// Output: "10.18.26.156" (or your actual IP)
```

### Method 2: Perform Action and Check Audit Log

1. **Login to the application**
2. **Navigate to:** `http://localhost:8000/public.audit_logs` (Admin → Audit Logs)
3. **Check the `ip_address` column** in the latest log entry
4. **Compare with your `ipconfig` output:**
   ```bash
   ipconfig
   ```
   Look for "IPv4 Address" line (e.g., `10.18.26.156`)

### Method 3: Database Query

```bash
php artisan tinker
```

```php
// Get latest audit log
$log = \App\Models\AuditLog::latest()->first();
echo $log->ip_address;
// Should show: "10.18.26.156" (your actual IP)
```

## Testing on Different Operating Systems

### Windows (Your Machine)

```bash
ipconfig
```

**Expected Output:**
```
Ethernet adapter Ethernet:
   IPv4 Address. . . . . . . . . . . : 10.18.26.156
```

**Audit Log captures:** `10.18.26.156` ✅

### Linux

```bash
hostname -I
```

**Expected Output:**
```
10.18.26.156 fe80::1234:5678:9abc:def0
```

**Audit Log captures:** `10.18.26.156` ✅ (first IPv4)

### macOS

```bash
ipconfig getifaddr en0
```

**Expected Output:**
```
10.18.26.156
```

**Audit Log captures:** `10.18.26.156` ✅

## Session Caching

For performance optimization, the IP address is cached in the user's session:

**Session Key:** `user_real_ip`

**Cache Duration:** Entire session (cleared on logout/browser close)

**Why:** Prevents running system commands on every request

**To Clear Cache:**
```php
session()->forget('user_real_ip');
```

## Edge Cases Handled

| Scenario | Behavior |
|----------|----------|
| **Machine has multiple IPs** | Returns first non-loopback IPv4 |
| **Shell commands disabled** | Falls back to `gethostbyname()` |
| **All methods fail** | Returns `127.0.0.1` (prevents errors) |
| **User behind proxy** | Uses X-Forwarded-For header first |
| **SSH tunnel setup** | Uses team member email mapping |

## Troubleshooting

### Problem: Still showing 127.0.0.1

**Solution 1:** Clear session cache
```bash
php artisan session:clear
# Or in browser: Logout and login again
```

**Solution 2:** Test the helper function directly
```bash
php artisan tinker
>>> getServerIpAddress();
```

If it returns `null`, check if shell commands are enabled:
```bash
php -r "echo shell_exec('ipconfig');"
```

**Solution 3:** Enable `shell_exec()` in php.ini
```ini
; Remove shell_exec from disable_functions
disable_functions =
```

### Problem: Permission denied on Linux/macOS

**Solution:** Ensure user has permission to run `hostname` command
```bash
which hostname
hostname -I
```

## Security Considerations

- **Command Injection Protected:** Uses `shell_exec()` with no user input
- **Error Handling:** All exceptions caught and logged
- **Fallback Safe:** Returns localhost if unable to determine IP
- **Session Security:** IP cached per session, not globally

## Related Files

| File | Purpose |
|------|---------|
| `app/helpers.php` | Contains `getServerIpAddress()` function |
| `app/Services/AuditService.php` | Uses helper to capture IP |
| `app/Models/AuditLog.php` | Stores IP in `ip_address` column |
| `database/migrations/2025_12_23_000001_create_audit_logs_table.php` | Defines `ip_address` column |

## Future Enhancements

- [ ] Add IPv6 support (currently IPv4 only)
- [ ] Admin panel to view IP address statistics
- [ ] Alert when unknown IP accesses the system
- [ ] Geolocation mapping for IP addresses
