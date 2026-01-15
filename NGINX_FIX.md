# Nginx Configuration Fix for Filament Login Error

## Problem
`MethodNotAllowedHttpException: The POST method is not supported for route admin/login`

This happens because:
1. **Livewire JavaScript isn't loading** - Form falls back to regular POST
2. **Nginx isn't routing POST requests correctly** to PHP-FPM
3. **Missing fastcgi parameters** for POST requests

## Quick Diagnosis

### Step 1: Check if Livewire is loading
Open browser DevTools → Network tab → Reload `/admin/login` page

**You should see:**
- ✅ `livewire/livewire.js` loads (200 status)
- ✅ `livewire/livewire.min.js` loads (200 status)

**If you see 404 errors:**
- Nginx isn't routing `/livewire/*` requests correctly
- Fix: Add the Livewire location block (see Step 2)

### Step 2: Check what the form POSTs to
1. Open `/admin/login` page
2. Open DevTools → Network tab
3. Enter credentials and submit
4. **Check the request URL:**

**CORRECT:** Should POST to `/livewire/update`  
**WRONG:** POSTs to `/admin/login` (means Livewire isn't working)

## Solution: Fix Nginx Configuration

### 1. Find Your Nginx Config File

```bash
# Usually located at:
/etc/nginx/sites-available/your-site-name
# or
/etc/nginx/conf.d/your-site-name.conf
```

### 2. Update Your Nginx Configuration

**CRITICAL:** Your location block MUST include these:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/project/public;  # MUST point to public/ directory
    
    index index.php;

    # Main location - handles all requests
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # CRITICAL: Explicitly handle Livewire routes
    location ~ ^/livewire/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # Adjust version
        
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # CRITICAL: These ensure POST requests work
        fastcgi_param REQUEST_METHOD $request_method;
        fastcgi_param CONTENT_TYPE $content_type;
        fastcgi_param CONTENT_LENGTH $content_length;
        
        # Increase timeouts for Livewire
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }
}
```

### 3. Test Nginx Configuration

```bash
sudo nginx -t
```

**If you see errors:** Fix them before proceeding.

### 4. Reload Nginx

```bash
sudo systemctl reload nginx
# or
sudo service nginx reload
```

### 5. Clear Laravel Caches

```bash
cd /path/to/your/project
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

### 6. Rebuild Caches (Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 7. Verify Livewire Routes

```bash
php artisan route:list --path=livewire
```

**Expected output:**
```
POST       livewire/update
POST       livewire/upload-file
GET|HEAD   livewire/livewire.js
```

## Common Nginx Issues

### Issue 1: Document Root Wrong
**Symptom:** 404 errors for all routes

**Fix:**
```nginx
root /path/to/your/project/public;  # NOT just /path/to/your/project
```

### Issue 2: PHP-FPM Socket Wrong
**Symptom:** 502 Bad Gateway errors

**Find correct socket:**
```bash
ls -la /var/run/php/
# Look for: php8.2-fpm.sock, php8.1-fpm.sock, etc.
```

**Update config:**
```nginx
fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # Use correct version
```

### Issue 3: Missing REQUEST_METHOD
**Symptom:** POST requests return 405 Method Not Allowed

**Fix:** Add to PHP location block:
```nginx
fastcgi_param REQUEST_METHOD $request_method;
fastcgi_param CONTENT_TYPE $content_type;
fastcgi_param CONTENT_LENGTH $content_length;
```

### Issue 4: Livewire Assets 404
**Symptom:** `livewire/livewire.js` returns 404

**Fix:** Add explicit Livewire location block:
```nginx
location ~ ^/livewire/ {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Issue 5: CSRF Token Issues
**Symptom:** 419 errors or CSRF token mismatch

**Fix:** Ensure sessions work:
```bash
# Check session driver in .env
SESSION_DRIVER=file  # or redis, database

# Ensure storage/framework/sessions is writable
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions
```

## Complete Example Configuration

See `nginx.conf.example` in the project root for a complete working configuration.

## Verification Checklist

After applying fixes:

- [ ] `sudo nginx -t` passes without errors
- [ ] Nginx reloaded successfully
- [ ] Can access `/admin/login` (GET request works)
- [ ] Browser DevTools shows `livewire/livewire.js` loads (200 status)
- [ ] Browser DevTools shows form POSTs to `/livewire/update` (NOT `/admin/login`)
- [ ] Login form submits successfully
- [ ] No 405 Method Not Allowed errors
- [ ] No 404 errors for Livewire assets

## Still Not Working?

### Debug Steps:

1. **Check Nginx error logs:**
   ```bash
   sudo tail -f /var/log/nginx/error.log
   ```

2. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Test Livewire route directly:**
   ```bash
   curl -X POST https://your-domain.com/livewire/update \
     -H "Content-Type: application/json" \
     -H "X-CSRF-TOKEN: your-token"
   ```

4. **Check if PHP-FPM is running:**
   ```bash
   sudo systemctl status php8.2-fpm
   ```

5. **Verify file permissions:**
   ```bash
   ls -la storage/framework/sessions
   ls -la bootstrap/cache
   ```

## Quick Test Commands

```bash
# Test Nginx config
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# View Nginx access logs
sudo tail -f /var/log/nginx/access.log

# View Nginx error logs
sudo tail -f /var/log/nginx/error.log
```
