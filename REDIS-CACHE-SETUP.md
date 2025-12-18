# Redis Cache Setup Guide

**Animal Rescue & Adoption Management System - Distributed Architecture**

This guide provides complete instructions for setting up Redis cache on all 5 machines in the distributed database architecture.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Machine Configuration](#machine-configuration)
3. [Prerequisites](#prerequisites)
4. [Installation Instructions](#installation-instructions)
   - [Windows Machines (WSL)](#windows-machines-wsl---eilya-taufiq-danish-atiqah)
   - [Ubuntu Machine](#ubuntu-machine---shafiqah)
5. [Laravel Configuration](#laravel-configuration)
6. [Testing & Verification](#testing--verification)
7. [Troubleshooting](#troubleshooting)
8. [Performance Monitoring](#performance-monitoring)
9. [Maintenance](#maintenance)
10. [Rollback Instructions](#rollback-instructions)

---

## Overview

### What is Redis?

Redis is an **in-memory data store** used for caching. It's **10-50x faster** than database cache because:
- Stores data in RAM (not disk)
- Optimized for simple key-value operations
- No SQL query overhead
- Persistent connections

### Why We Need Redis

Our application checks 5 distributed databases frequently. Current setup:
- **Database cache**: 15-25ms per cache read
- **Redis cache**: <1ms per cache read
- **Performance gain**: ~95% faster

### Architecture After Redis Setup

```
┌─────────────────────────────────────────────────────────────────┐
│                    Laravel Application                           │
│                                                                  │
│  Cache::get('key') → Redis (Port 6379)                          │
│                        ↓                                         │
│                   < 1ms response                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                  Distributed Databases                           │
│                                                                  │
│  • Taufiq (PostgreSQL) - Port 5434 - User Management            │
│  • Eilya (MySQL) - Port 3307 - Stray Reporting                  │
│  • Shafiqah (MySQL) - Port 3309 - Animal Management             │
│  • Atiqah (MySQL) - Port 3308 - Shelter Management              │
│  • Danish (SQL Server) - Port 1434 - Booking & Adoption         │
└─────────────────────────────────────────────────────────────────┘
```

---

## Machine Configuration

| Machine | OS | Database | Port | Redis Installation Method |
|---------|-----|----------|------|---------------------------|
| **Taufiq** | Windows (WSL) | PostgreSQL | 5434 | WSL Ubuntu Redis |
| **Eilya** | Windows (WSL) | MySQL | 3307 | WSL Ubuntu Redis |
| **Shafiqah** | Ubuntu 22.04+ | MySQL | 3309 | Native Ubuntu Redis |
| **Atiqah** | Windows (WSL) | MySQL | 3308 | WSL Ubuntu Redis |
| **Danish** | Windows (WSL) | SQL Server | 1434 | WSL Ubuntu Redis |

**Redis Port**: 6379 (default) on all machines

---

## Prerequisites

### All Machines

- [ ] Admin/sudo privileges
- [ ] Internet connection
- [ ] Laravel application installed
- [ ] Composer installed

### Windows Machines (Eilya, Taufiq, Danish, Atiqah)

- [ ] Windows 10 version 2004+ or Windows 11
- [ ] WSL 2 installed and configured
- [ ] Ubuntu distribution installed in WSL

### Ubuntu Machine (Shafiqah)

- [ ] Ubuntu 20.04 or later
- [ ] Terminal access

---

## Installation Instructions

## Windows Machines (WSL) - Eilya, Taufiq, Danish, Atiqah

### Step 1: Verify WSL Installation

Open **PowerShell** as Administrator and check WSL version:

```powershell
wsl --list --verbose
```

**Expected output:**
```
  NAME            STATE           VERSION
* Ubuntu          Running         2
```

If WSL is not installed, install it:

```powershell
# Install WSL 2
wsl --install

# Restart your computer after installation
```

### Step 2: Install Ubuntu in WSL (if not already installed)

```powershell
# Install Ubuntu (latest)
wsl --install -d Ubuntu

# Or install specific version
wsl --install -d Ubuntu-22.04
```

**Set up your Ubuntu user:**
- Enter a username (e.g., your name in lowercase)
- Enter a password (you'll need this for sudo commands)

### Step 3: Open WSL Ubuntu Terminal

Open **WSL** terminal (one of these methods):
1. Type `wsl` in PowerShell
2. Search "Ubuntu" in Windows Start menu
3. Open Windows Terminal and select Ubuntu tab

### Step 4: Update Ubuntu Package Manager

```bash
sudo apt update && sudo apt upgrade -y
```

**Enter your WSL Ubuntu password when prompted.**

### Step 5: Install Redis Server

```bash
# Install Redis
sudo apt install redis-server -y

# Verify installation
redis-server --version
```

**Expected output:**
```
Redis server v=6.0.16 sha=00000000:0 malloc=jemalloc-5.2.1 bits=64 build=a3fdef44459b3ad6
```

### Step 6: Configure Redis

Edit Redis configuration file:

```bash
sudo nano /etc/redis/redis.conf
```

**Find and modify these settings:**

Press `Ctrl + W` to search in nano.

1. **Search for: `supervised`**
   ```
   # Change from:
   supervised no

   # To:
   supervised systemd
   ```

2. **Search for: `bind`**
   ```
   # Change from:
   bind 127.0.0.1 ::1

   # To (allow connections from Windows host):
   bind 0.0.0.0
   ```

3. **Search for: `protected-mode`**
   ```
   # Change from:
   protected-mode yes

   # To:
   protected-mode no
   ```

**Save and exit:**
- Press `Ctrl + X`
- Press `Y` to confirm
- Press `Enter` to save

### Step 7: Start Redis Service

```bash
# Start Redis
sudo service redis-server start

# Verify Redis is running
sudo service redis-server status
```

**Expected output:**
```
redis-server is running
```

### Step 8: Test Redis Connection

```bash
# Test Redis CLI
redis-cli ping
```

**Expected output:**
```
PONG
```

If you get `PONG`, Redis is working! ✅

### Step 9: Make Redis Start Automatically

Add this to your `~/.bashrc` file so Redis starts automatically when you open WSL:

```bash
# Edit bashrc
nano ~/.bashrc

# Add at the end of the file:
# Auto-start Redis on WSL startup
if ! service redis-server status > /dev/null 2>&1; then
    sudo service redis-server start
fi

# Save and exit (Ctrl+X, Y, Enter)

# Reload bashrc
source ~/.bashrc
```

### Step 10: Get WSL IP Address (Important!)

Find your WSL IP address to configure Laravel:

```bash
# Get WSL IP address
hostname -I | awk '{print $1}'
```

**Example output:**
```
172.18.160.1
```

**⚠️ IMPORTANT:** Write down this IP address. You'll need it for Laravel configuration.

**Note:** WSL IP addresses can change after reboot. You'll need to update `.env` if this happens. See [Troubleshooting](#troubleshooting) section.

### Step 11: Test Redis from Windows

Open **PowerShell** on Windows and test connection:

```powershell
# Replace <WSL_IP> with your actual WSL IP from Step 10
wsl redis-cli -h <WSL_IP> ping
```

**Example:**
```powershell
wsl redis-cli -h 172.18.160.1 ping
```

**Expected output:**
```
PONG
```

✅ **Redis is now installed and configured on Windows (WSL)!**

---

## Ubuntu Machine - Shafiqah

### Step 1: Update Package Manager

Open terminal and run:

```bash
sudo apt update && sudo apt upgrade -y
```

### Step 2: Install Redis Server

```bash
# Install Redis
sudo apt install redis-server -y

# Verify installation
redis-server --version
```

**Expected output:**
```
Redis server v=6.0.16 sha=00000000:0 malloc=jemalloc-5.2.1 bits=64 build=a3fdef44459b3ad6
```

### Step 3: Configure Redis

Edit Redis configuration:

```bash
sudo nano /etc/redis/redis.conf
```

**Find and modify these settings:**

1. **Search for: `supervised`** (Ctrl + W)
   ```
   # Change from:
   supervised no

   # To:
   supervised systemd
   ```

2. **Optional - Allow remote connections (if Laravel runs on different machine):**

   Search for: `bind`
   ```
   # Change from:
   bind 127.0.0.1 ::1

   # To (if allowing remote connections):
   bind 0.0.0.0
   ```

   **⚠️ Only do this if Laravel application is on a different machine!**

**Save and exit:**
- Press `Ctrl + X`
- Press `Y`
- Press `Enter`

### Step 4: Restart Redis Service

```bash
# Restart Redis to apply configuration
sudo systemctl restart redis-server

# Enable Redis to start on boot
sudo systemctl enable redis-server

# Check status
sudo systemctl status redis-server
```

**Expected output:**
```
● redis-server.service - Advanced key-value store
     Loaded: loaded (/lib/systemd/system/redis-server.service; enabled; vendor preset: enabled)
     Active: active (running) since ...
```

Press `Q` to exit status view.

### Step 5: Test Redis Connection

```bash
# Test Redis CLI
redis-cli ping
```

**Expected output:**
```
PONG
```

### Step 6: Configure Firewall (if enabled)

If you're allowing remote connections and have a firewall enabled:

```bash
# Allow Redis port
sudo ufw allow 6379/tcp

# Check firewall status
sudo ufw status
```

### Step 7: Get IP Address (if needed for remote connections)

```bash
# Get your machine's IP address
hostname -I | awk '{print $1}'
```

**Example output:**
```
192.168.1.100
```

✅ **Redis is now installed and configured on Ubuntu!**

---

## Laravel Configuration

### Step 1: Install PHP Redis Client

On the machine where Laravel is running, navigate to your project directory:

```bash
# Navigate to project
cd /path/to/Animal-Shelter-Workshop

# Install Predis (PHP Redis client)
composer require predis/predis
```

**Expected output:**
```
Using version ^2.2 for predis/predis
./composer.json has been updated
...
Package operations: 1 install, 0 updates, 0 removals
  - Installing predis/predis (v2.2.2): Downloading (100%)
```

### Step 2: Update `.env` File

Edit your `.env` file:

```bash
# Windows (PowerShell in project directory)
notepad .env

# Linux/WSL
nano .env
```

**Find and update these lines:**

#### For Local Redis (same machine as Laravel)

```env
# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=animal_shelter_cache

# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

#### For WSL Redis (Windows machines running Laravel on Windows)

```env
# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=animal_shelter_cache

# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=172.18.160.1    # ← Your WSL IP address from installation step
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

**⚠️ IMPORTANT:** Replace `172.18.160.1` with your actual WSL IP address.

#### For Remote Redis (if Laravel and Redis are on different machines)

```env
# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=animal_shelter_cache

# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=192.168.1.100    # ← IP address of machine running Redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

### Step 3: Clear Laravel Cache

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear database connection status cache
php artisan db:clear-status-cache
```

### Step 4: Test Redis Connection from Laravel

```bash
# Test Redis connection using artisan tinker
php artisan tinker
```

In tinker, run:

```php
// Test connection
Cache::store('redis')->put('test', 'Hello Redis', 60);

// Retrieve value
Cache::store('redis')->get('test');

// Should output: "Hello Redis"

// Exit tinker
exit
```

**Expected output:**
```
>>> Cache::store('redis')->put('test', 'Hello Redis', 60);
=> true

>>> Cache::store('redis')->get('test');
=> "Hello Redis"
```

✅ **Laravel is now connected to Redis!**

---

## Testing & Verification

### Test 1: Cache Performance Test

Create a test script to compare cache performance:

```bash
php artisan tinker
```

**Run this test:**

```php
// Test Redis cache speed
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    Cache::put("test_key_{$i}", "test_value_{$i}", 60);
    Cache::get("test_key_{$i}");
}
$redisTime = (microtime(true) - $start) * 1000;

echo "Redis cache (1000 operations): " . round($redisTime, 2) . "ms\n";

exit
```

**Expected output:**
```
Redis cache (1000 operations): ~50-200ms
```

This means each operation takes ~0.05-0.2ms ⚡

### Test 2: Database Connection Status Caching

```bash
# Clear cache
php artisan db:clear-status-cache

# Start server
php artisan serve
```

Visit `http://localhost:8000` in your browser.

**First request:**
- Should take ~5 seconds (checking all 5 databases)
- Status cached in Redis

**Subsequent requests:**
- Should be instant (<1ms from Redis)
- Modal shows cached status

**Verify cache is working:**

```bash
php artisan tinker
```

```php
// Check if connection status is cached
Cache::has('db_connection_status');
// Should return: true

// View cached data
Cache::get('db_connection_status');
// Should show array of database statuses

exit
```

### Test 3: Monitor Redis Keys

```bash
# WSL/Ubuntu terminal
redis-cli

# List all keys
KEYS *

# Should show keys like:
# 1) "animal_shelter_cache:db_connection_status"
# 2) "animal_shelter_cache:db_connection_status_taufiq"
# etc.

# Check TTL (time to live)
TTL animal_shelter_cache:db_connection_status

# Should show remaining seconds (e.g., 1800 = 30 minutes)

# Exit Redis CLI
exit
```

### Test 4: Server Startup Speed

**Before Redis** (database cache):
```bash
# Clear all cache
php artisan cache:clear

# Start server and time it
time php artisan serve
```

First request to homepage: ~5+ seconds

**After Redis**:
```bash
# Clear cache
php artisan cache:clear

# Start server
time php artisan serve
```

First request to homepage: ~100-500ms (10x faster!) 🚀

---

## Troubleshooting

### Issue 1: "Connection refused" Error

**Error:**
```
Connection refused [tcp://127.0.0.1:6379]
```

**Solution:**

**WSL (Windows):**
```bash
# Check if Redis is running
wsl
sudo service redis-server status

# If not running, start it
sudo service redis-server start

# Test connection
redis-cli ping
```

**Ubuntu:**
```bash
# Check status
sudo systemctl status redis-server

# If not running, start it
sudo systemctl start redis-server

# Enable auto-start
sudo systemctl enable redis-server
```

### Issue 2: WSL IP Address Changed

**Problem:** Redis works, then stops after reboot.

**Cause:** WSL IP addresses can change after Windows restart.

**Solution:**

```bash
# Find new WSL IP
wsl
hostname -I | awk '{print $1}'

# Update .env file with new IP
# Example: REDIS_HOST=172.18.XXX.XXX
```

**Permanent Solution - Use Windows Hosts File:**

1. Open PowerShell as Administrator
2. Edit hosts file:
   ```powershell
   notepad C:\Windows\System32\drivers\etc\hosts
   ```
3. Add this line (replace with your WSL IP):
   ```
   172.18.160.1    redis.local
   ```
4. Update `.env`:
   ```env
   REDIS_HOST=redis.local
   ```

**Better Solution - Use localhost with port forwarding:**

In PowerShell as Administrator:
```powershell
# Forward localhost:6379 to WSL Redis
netsh interface portproxy add v4tov4 listenport=6379 listenaddress=0.0.0.0 connectport=6379 connectaddress=<WSL_IP>
```

Then use in `.env`:
```env
REDIS_HOST=127.0.0.1
```

### Issue 3: "MISCONF Redis is configured to save RDB snapshots"

**Error:**
```
MISCONF Redis is configured to save RDB snapshots, but it is currently not able to persist on disk
```

**Solution:**

```bash
# WSL/Ubuntu terminal
redis-cli

# Disable RDB persistence temporarily
CONFIG SET stop-writes-on-bgsave-error no

# Or fix permissions
exit
sudo chown redis:redis /var/lib/redis
sudo systemctl restart redis-server
```

### Issue 4: Predis Not Found

**Error:**
```
Class 'Predis\Client' not found
```

**Solution:**

```bash
# Install Predis
composer require predis/predis

# Clear config cache
php artisan config:clear
```

### Issue 5: Performance Not Improved

**Symptoms:** Cache still slow after Redis setup.

**Checklist:**

1. **Verify `.env` is using Redis:**
   ```bash
   php artisan tinker
   ```
   ```php
   config('cache.default');
   // Should return: "redis"
   exit
   ```

2. **Clear all caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan db:clear-status-cache
   ```

3. **Check Redis is actually being used:**
   ```bash
   redis-cli MONITOR
   ```
   Then make a request to your app. You should see Redis commands appear.

### Issue 6: Can't Connect from Remote Machine

**Problem:** Laravel on Machine A can't connect to Redis on Machine B.

**Solution:**

**On Redis machine (Machine B):**

1. Edit Redis config:
   ```bash
   sudo nano /etc/redis/redis.conf
   ```

2. Change bind:
   ```
   bind 0.0.0.0
   ```

3. Set protected mode:
   ```
   protected-mode no
   ```

4. Restart Redis:
   ```bash
   sudo systemctl restart redis-server  # Ubuntu
   sudo service redis-server restart    # WSL
   ```

5. Open firewall (if enabled):
   ```bash
   sudo ufw allow 6379/tcp
   ```

**On Laravel machine (Machine A):**

Update `.env`:
```env
REDIS_HOST=<Machine_B_IP>
```

Test connection:
```bash
redis-cli -h <Machine_B_IP> ping
# Should return: PONG
```

---

## Performance Monitoring

### Monitor Redis Performance

#### Check Redis Stats

```bash
redis-cli INFO stats
```

**Key metrics:**
- `total_connections_received` - Total connections
- `total_commands_processed` - Total commands
- `keyspace_hits` - Cache hits
- `keyspace_misses` - Cache misses

#### Calculate Cache Hit Rate

```bash
redis-cli INFO stats | grep keyspace
```

**Formula:**
```
Hit Rate = keyspace_hits / (keyspace_hits + keyspace_misses) × 100%
```

**Good hit rate:** >80%

#### Monitor Real-Time Commands

```bash
# Watch commands as they happen
redis-cli MONITOR
```

**Example output:**
```
1702896541.123456 [0 172.18.160.1:51234] "GET" "animal_shelter_cache:db_connection_status"
1702896541.234567 [0 172.18.160.1:51234] "SET" "animal_shelter_cache:test_key" "value"
```

Press `Ctrl+C` to stop.

#### Check Memory Usage

```bash
redis-cli INFO memory
```

**Key metrics:**
- `used_memory_human` - Current memory usage
- `used_memory_peak_human` - Peak memory usage
- `maxmemory` - Memory limit (0 = unlimited)

### Laravel Cache Statistics

Create a custom artisan command to view cache stats:

```bash
php artisan tinker
```

```php
// Get all cache keys
$keys = Cache::getRedis()->keys('animal_shelter_cache:*');
echo "Total cached items: " . count($keys) . "\n";

// Check specific key TTL
$ttl = Cache::getRedis()->ttl('animal_shelter_cache:db_connection_status');
echo "DB status cache expires in: " . $ttl . " seconds\n";

exit
```

---

## Maintenance

### Daily Maintenance (Automated)

**Nothing required!** Redis handles this automatically:
- Expired keys are deleted automatically
- Memory is managed automatically
- No daily tasks needed

### Weekly Maintenance

#### Check Redis Health

```bash
# Check if Redis is running
# WSL:
sudo service redis-server status

# Ubuntu:
sudo systemctl status redis-server

# Check memory usage
redis-cli INFO memory | grep used_memory_human
```

#### Verify Cache is Working

```bash
php artisan tinker
```

```php
// Test cache
Cache::put('health_check', time(), 60);
Cache::get('health_check');
// Should return the timestamp

exit
```

### Monthly Maintenance

#### Clean Up Old Keys (if needed)

```bash
redis-cli

# Find keys older than expected
KEYS animal_shelter_cache:*

# Remove specific old key
DEL animal_shelter_cache:old_key

# Or flush all cache (use with caution!)
FLUSHDB

exit
```

#### Check Disk Usage

```bash
# Check Redis data directory size
du -sh /var/lib/redis
```

### Backup Redis Data (Optional)

Redis data for caching doesn't need backups (it's temporary). But if you want to:

```bash
# Create backup
redis-cli SAVE

# Backup file location
sudo cp /var/lib/redis/dump.rdb /backup/redis-dump-$(date +%Y%m%d).rdb
```

### Update Redis (when needed)

**WSL/Ubuntu:**

```bash
sudo apt update
sudo apt upgrade redis-server

# Restart Redis
sudo service redis-server restart    # WSL
sudo systemctl restart redis-server  # Ubuntu
```

---

## Rollback Instructions

### If Redis Causes Issues

You can easily rollback to database/file cache:

#### Step 1: Update `.env`

```env
# Change from:
CACHE_STORE=redis

# To (database cache):
CACHE_STORE=database

# Or (file cache):
CACHE_STORE=file
```

#### Step 2: Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan db:clear-status-cache
```

#### Step 3: Test Application

```bash
php artisan serve
```

Your app will now use the old cache method.

#### Step 4: Stop Redis (optional)

If you want to completely disable Redis:

**WSL:**
```bash
sudo service redis-server stop
```

**Ubuntu:**
```bash
sudo systemctl stop redis-server
sudo systemctl disable redis-server
```

#### Step 5: Remove Predis (optional)

```bash
composer remove predis/predis
```

---

## Quick Reference Commands

### Redis Service Control

| Action | WSL Command | Ubuntu Command |
|--------|-------------|----------------|
| Start | `sudo service redis-server start` | `sudo systemctl start redis-server` |
| Stop | `sudo service redis-server stop` | `sudo systemctl stop redis-server` |
| Restart | `sudo service redis-server restart` | `sudo systemctl restart redis-server` |
| Status | `sudo service redis-server status` | `sudo systemctl status redis-server` |

### Redis CLI Commands

| Command | Description |
|---------|-------------|
| `redis-cli ping` | Test connection (returns PONG) |
| `redis-cli` | Open Redis CLI |
| `KEYS *` | List all keys |
| `GET key` | Get value of key |
| `SET key value` | Set key-value pair |
| `DEL key` | Delete key |
| `TTL key` | Time to live (seconds) |
| `FLUSHDB` | Clear current database |
| `INFO` | Server information |
| `MONITOR` | Watch commands in real-time |
| `exit` | Exit Redis CLI |

### Laravel Cache Commands

| Command | Description |
|---------|-------------|
| `php artisan cache:clear` | Clear application cache |
| `php artisan config:clear` | Clear config cache |
| `php artisan db:clear-status-cache` | Clear DB connection status |
| `php artisan tinker` | Open Laravel REPL for testing |

### Useful One-Liners

```bash
# Check if Redis is responding
redis-cli ping

# View all cached keys
redis-cli KEYS "animal_shelter_cache:*"

# Check cache hit rate
redis-cli INFO stats | grep keyspace

# Monitor Redis in real-time
redis-cli MONITOR

# Check memory usage
redis-cli INFO memory | grep used_memory_human

# Get WSL IP address
hostname -I | awk '{print $1}'

# Test Laravel Redis connection
php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');"
```

---

## Support & Resources

### Official Documentation

- **Redis Documentation**: https://redis.io/docs/
- **Laravel Cache**: https://laravel.com/docs/11.x/cache
- **Predis Client**: https://github.com/predis/predis

### Common Questions

**Q: Do I need Redis on all 5 machines?**
A: No! You only need Redis on the machine(s) running the Laravel application. The database servers (Taufiq, Eilya, etc.) only need their database services.

**Q: Can multiple Laravel instances share one Redis server?**
A: Yes! Set `REDIS_HOST` to the IP of the machine running Redis on all Laravel instances.

**Q: Will Redis use a lot of memory?**
A: No. For cache data, Redis typically uses <100MB. You can set a limit in `redis.conf` if needed.

**Q: What happens if Redis crashes?**
A: The app falls back to file cache automatically (thanks to the dual-cache strategy in `DatabaseConnectionChecker`). The app keeps working, just slightly slower.

**Q: Is Redis data persistent after reboot?**
A: By default, yes (RDB snapshots). But for cache data, this doesn't matter - it's meant to be temporary.

---

## Checklist for Each Machine

Use this checklist to ensure proper setup on each machine:

### Windows Machines (Eilya, Taufiq, Danish, Atiqah)

- [ ] WSL 2 installed and running
- [ ] Ubuntu installed in WSL
- [ ] Redis installed: `redis-server --version`
- [ ] Redis configured: `bind 0.0.0.0`, `supervised systemd`
- [ ] Redis running: `sudo service redis-server status`
- [ ] Redis responds: `redis-cli ping` → PONG
- [ ] WSL IP address noted: `hostname -I`
- [ ] Redis auto-starts: Added to `~/.bashrc`
- [ ] Laravel `.env` updated with WSL IP
- [ ] Predis installed: `composer require predis/predis`
- [ ] Laravel config cleared: `php artisan config:clear`
- [ ] Connection tested: `php artisan tinker` → `Cache::get('test')`
- [ ] Server starts fast: `php artisan serve` → instant startup

### Ubuntu Machine (Shafiqah)

- [ ] Redis installed: `redis-server --version`
- [ ] Redis configured: `supervised systemd`
- [ ] Redis running: `sudo systemctl status redis-server`
- [ ] Redis enabled on boot: `sudo systemctl enable redis-server`
- [ ] Redis responds: `redis-cli ping` → PONG
- [ ] Laravel `.env` updated: `CACHE_STORE=redis`
- [ ] Predis installed: `composer require predis/predis`
- [ ] Laravel config cleared: `php artisan config:clear`
- [ ] Connection tested: `php artisan tinker` → `Cache::get('test')`
- [ ] Server starts fast: `php artisan serve` → instant startup

---

## Performance Benchmarks (Expected Results)

### Before Redis (Database Cache)

| Operation | Time |
|-----------|------|
| Cold start (first request) | 5000ms (5 DB checks) |
| Warm cache read | 15-25ms |
| Cache write | 20-30ms |
| 1000 cache operations | 15,000-25,000ms |

### After Redis

| Operation | Time | Improvement |
|-----------|------|-------------|
| Cold start (first request) | 5000ms (unchanged) | - |
| Warm cache read | <1ms | **95% faster** ✅ |
| Cache write | <1ms | **97% faster** ✅ |
| 1000 cache operations | 50-200ms | **99% faster** ✅ |

### Real-World Impact

**Scenario:** User visits homepage with database status modal

**Before Redis:**
- First visit: 5000ms (checking 5 databases)
- Next 50 visits within 30 min: 50 × 20ms = 1000ms
- **Total: 6000ms for 51 requests**

**After Redis:**
- First visit: 5000ms (checking 5 databases)
- Next 50 visits within 30 min: 50 × 0.5ms = 25ms
- **Total: 5025ms for 51 requests**

**Time saved: 975ms (16% faster overall)** 🚀

---

## Conclusion

After completing this setup:

✅ **All machines have Redis installed and configured**
✅ **Laravel uses Redis for caching (95% faster)**
✅ **Database connection status cached efficiently**
✅ **Dual-cache fallback ensures reliability**
✅ **Server startup is instant**

**Next Steps:**
1. Complete installation on all 5 machines using this guide
2. Test Redis performance using the verification steps
3. Monitor cache hit rate for the first week
4. Update `.env` if WSL IP addresses change

**Questions or Issues?**
Refer to the [Troubleshooting](#troubleshooting) section or check Redis logs:

```bash
# WSL
sudo tail -f /var/log/redis/redis-server.log

# Ubuntu
sudo journalctl -u redis-server -f
```

---

**Document Version:** 1.0
**Last Updated:** 2025-12-18
**Maintained By:** Animal Shelter Workshop II Group (UTeM)

