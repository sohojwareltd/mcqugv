#!/bin/bash
# Nginx Diagnostic Script for Filament Login Issue
# Run this on your production server to diagnose the problem

echo "=========================================="
echo "Nginx + Filament Login Diagnostic"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  Some checks require sudo. Run with: sudo bash NGINX_DIAGNOSTIC.sh"
    echo ""
fi

echo "1. Checking Nginx configuration syntax..."
if sudo nginx -t 2>&1 | grep -q "successful"; then
    echo "✅ Nginx configuration is valid"
else
    echo "❌ Nginx configuration has errors:"
    sudo nginx -t
    exit 1
fi
echo ""

echo "2. Checking if PHP-FPM is running..."
if systemctl is-active --quiet php8.2-fpm || systemctl is-active --quiet php8.1-fpm || systemctl is-active --quiet php-fpm; then
    echo "✅ PHP-FPM is running"
else
    echo "❌ PHP-FPM is not running"
    echo "   Start it with: sudo systemctl start php8.2-fpm"
fi
echo ""

echo "3. Checking Laravel Livewire routes..."
if php artisan route:list --path=livewire 2>/dev/null | grep -q "livewire/update"; then
    echo "✅ Livewire routes are registered"
    php artisan route:list --path=livewire | grep "POST.*livewire"
else
    echo "❌ Livewire routes are NOT registered"
    echo "   Run: php artisan route:clear && php artisan route:cache"
fi
echo ""

echo "4. Checking Nginx config for critical directives..."
NGINX_CONF=$(find /etc/nginx -name "*.conf" -o -name "*" -type f 2>/dev/null | grep -E "(sites-available|conf.d)" | head -1)

if [ -z "$NGINX_CONF" ]; then
    echo "⚠️  Could not find Nginx config file automatically"
    echo "   Please check manually: /etc/nginx/sites-available/your-site"
else
    echo "   Checking: $NGINX_CONF"
    
    if grep -q "fastcgi_param REQUEST_METHOD" "$NGINX_CONF" 2>/dev/null; then
        echo "✅ REQUEST_METHOD parameter found"
    else
        echo "❌ REQUEST_METHOD parameter MISSING - This is likely the problem!"
        echo "   Add to PHP location block: fastcgi_param REQUEST_METHOD \$request_method;"
    fi
    
    if grep -q "location.*livewire" "$NGINX_CONF" 2>/dev/null; then
        echo "✅ Livewire location block found"
    else
        echo "⚠️  Livewire location block not found (may still work)"
    fi
    
    if grep -q "root.*public" "$NGINX_CONF" 2>/dev/null; then
        echo "✅ Document root points to public/ directory"
    else
        echo "❌ Document root may not point to public/ directory"
        echo "   Check: root directive should be /path/to/project/public"
    fi
fi
echo ""

echo "5. Checking file permissions..."
if [ -w "storage/framework/sessions" ] 2>/dev/null; then
    echo "✅ storage/framework/sessions is writable"
else
    echo "❌ storage/framework/sessions is not writable"
    echo "   Fix: sudo chmod -R 775 storage/framework/sessions"
fi

if [ -w "bootstrap/cache" ] 2>/dev/null; then
    echo "✅ bootstrap/cache is writable"
else
    echo "❌ bootstrap/cache is not writable"
    echo "   Fix: sudo chmod -R 775 bootstrap/cache"
fi
echo ""

echo "6. Checking Laravel caches..."
if [ -f "bootstrap/cache/routes-v7.php" ] || [ -f "bootstrap/cache/routes.php" ]; then
    echo "⚠️  Route cache exists - may need clearing"
    echo "   Run: php artisan route:clear"
else
    echo "✅ No route cache (or cleared)"
fi
echo ""

echo "=========================================="
echo "Diagnostic Complete"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Fix any ❌ errors above"
echo "2. See NGINX_FIX.md for detailed instructions"
echo "3. Test Nginx: sudo nginx -t"
echo "4. Reload Nginx: sudo systemctl reload nginx"
echo "5. Clear Laravel caches: php artisan optimize:clear"
echo ""
