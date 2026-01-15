# Production Deployment Checklist

## Pre-Deployment
- [ ] Commit all changes to git
- [ ] Run tests locally: `php artisan test`
- [ ] Build frontend assets: `npm run build`
- [ ] Verify `.env.production` or production environment variables are set

## Deployment Steps

### 1. Pull Latest Code
```bash
git pull origin main
```

### 2. Install/Update Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm ci --omit=dev
```

### 3. Build Assets
```bash
npm run build
```

### 4. Clear All Caches (CRITICAL)
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

### 5. Run Migrations
```bash
php artisan migrate --force
```

### 6. Rebuild Production Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 7. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Restart Services
```bash
# For PHP-FPM
sudo systemctl restart php8.2-fpm

# For Queue Workers (if using)
php artisan queue:restart

# For Supervisor (if using)
sudo supervisorctl restart all
```

## Post-Deployment Verification

### 1. Verify Livewire Routes (CRITICAL for Filament)
```bash
php artisan route:list --path=livewire
```

**Expected output:**
- ✅ `POST livewire/update` - Required for Filament login
- ✅ `POST livewire/upload-file`
- ✅ `GET|HEAD livewire/livewire.js`

**If POST routes are missing:** The code fix in `routes/web.php` is not deployed. Pull latest code and repeat steps 1-6.

### 2. Test Admin Login
1. Visit `https://yourdomain.com/admin/login`
2. Open browser DevTools → Network tab
3. Enter credentials and submit
4. Verify the form POSTs to `livewire/update` (NOT `/admin/login`)
5. Confirm successful login

### 3. Check Application Health
- [ ] Homepage loads correctly
- [ ] Admin dashboard accessible
- [ ] No JavaScript console errors
- [ ] No 500 errors in logs: `tail -f storage/logs/laravel.log`

## Troubleshooting

### Issue: "MethodNotAllowedHttpException: POST not supported for /admin/login"

**Cause:** Livewire routes not registered after route caching.

**Solution:**
1. Verify `routes/web.php` contains the Livewire route fix (lines 20-24)
2. Clear and rebuild caches (Steps 4 & 6 above)
3. Verify Livewire routes are registered (Post-Deployment step 1)

### Issue: Login form doesn't submit or shows JavaScript errors

**Cause:** Livewire assets not loading.

**Solution:**
1. Check browser console for errors
2. Verify `livewire/livewire.js` loads (Network tab)
3. Ensure `config/livewire.php` has `'inject_assets' => true`
4. Clear browser cache

### Issue: CSRF token mismatch

**Cause:** Session configuration or cache issues.

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan session:clear
```

## Rollback Procedure

If deployment fails:

```bash
# Revert to previous version
git reset --hard HEAD~1

# Clear caches
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache

# Restart services
sudo systemctl restart php8.2-fpm
```

## Environment-Specific Notes

### Apache
- Document root must point to `public/` directory
- `.htaccess` file must be present in `public/`
- `mod_rewrite` must be enabled

### Nginx
- Root directive must point to `public/` directory
- Location block must include: `try_files $uri $uri/ /index.php?$query_string;`

### Shared Hosting
- Some shared hosts don't support route caching
- If issues persist, skip `php artisan route:cache` step
- Performance will be slightly slower but functionality will work

## Quick Reference Commands

```bash
# Full cache clear
php artisan optimize:clear

# Full cache rebuild
php artisan optimize

# View all routes
php artisan route:list

# Check Livewire routes specifically
php artisan route:list --path=livewire

# View application logs
tail -f storage/logs/laravel.log
```
