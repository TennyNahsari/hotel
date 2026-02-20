# Deployment Guide - Hotel Management System
## Ubuntu 24.04 Server

### Prerequisites (Already Installed)
- ✅ PostgreSQL
- ✅ Composer

### Step 1: Create Ubuntu User

```bash
# Create new user 'hotel'
sudo adduser hotel

# Add user to sudo group (optional, for admin access)
sudo usermod -aG sudo hotel

# Switch to the new user
su - hotel

# Verify current user
whoami
# Should output: hotel
```

### Step 2: Install Required Software

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2+ and extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-pgsql \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath \
    php8.2-gd php8.2-intl php8.2-redis

# Install Nginx
sudo apt install -y nginx

# Install Node.js 18+ and npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verify installations
php -v
composer -V
node -v
npm -v
psql --version
```

### Step 3: Setup PostgreSQL Database

```bash
# Login to PostgreSQL
sudo -u postgres psql

# Create user 'hotel' first
CREATE USER hotel WITH PASSWORD 'your_secure_password';

# Create database and assign ownership
CREATE DATABASE hotel OWNER hotel;

# Grant all privileges
GRANT ALL PRIVILEGES ON DATABASE hotel TO hotel;

# Exit PostgreSQL
\q

# Test connection with new user
psql -U hotel -d hotel -h localhost
# Enter password when prompted
# Type \q to exit
```

### Step 4: Upload Project Files

```bash
# Create project directory (as user 'hotel' or with sudo)
sudo mkdir -p /var/www/hotel
sudo chown -R hotel:hotel /var/www/hotel

# Upload your project files to /var/www/hotel
# Or clone from git (make sure user 'hotel' has SSH key configured):
# cd /var/www/hotel
# git clone your-repository-url .

# After upload/clone, ensure correct ownership
sudo chown -R hotel:hotel /var/www/hotel
cd /var/www/hotel
ls -la  # Verify owner is 'hotel'
```

### Step 5: Setup Backend (Laravel)

```bash
cd /var/www/hotel/backend

# Fix git ownership issue (if cloned from git)
git config --global --add safe.directory /var/www/hotel

# Create vendor directory and set permissions
mkdir -p vendor
sudo chown -R hotel:hotel /var/www/hotel

# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup environment
cp .env.example .env
nano .env
```

**Configure `.env` file:**
```env
APP_NAME=HotelOne
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hotel
DB_USERNAME=hotel
DB_PASSWORD=your_secure_password

SESSION_DRIVER=cookie
SESSION_DOMAIN=your-domain.com
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false

SANCTUM_STATEFUL_DOMAINS=your-domain.com
FRONTEND_URL=http://your-domain.com

# Important: SESSION_DOMAIN and SANCTUM_STATEFUL_DOMAINS should NOT include http://
# Only domain name, e.g., tazkia-hotel.duckdns.org
# SESSION_SECURE_COOKIE=false for HTTP, true for HTTPS
```

**Continue setup:**
```bash
# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data /var/www/hotel/backend/storage
sudo chown -R www-data:www-data /var/www/hotel/backend/bootstrap/cache
sudo chmod -R 775 /var/www/hotel/backend/storage
sudo chmod -R 775 /var/www/hotel/backend/bootstrap/cache

# Add user 'hotel' to www-data group for easier file management
sudo usermod -aG www-data hotel
```

### Step 6: Setup Frontend (Vue.js)

```bash
cd /var/www/hotel/frontend

# Install dependencies
npm install

# Create production .env file
nano .env.production
```

**Configure `.env.production`:**
```env
VITE_API_URL=http://your-domain.com
VITE_API_BASE_URL=http://your-domain.com/api
```

**Note:** The application now uses environment variables, so you only need to configure the `.env.production` file with your domain. The source code will automatically use these values during build.

**Build for production:**
```bash
cd /var/www/hotel/frontend
npm run build

# The dist folder will contain the built files
```

### Step 7: Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/hotel
```

**Nginx configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    
    root /var/www/hotel/frontend/dist;
    index index.html;

    # Frontend - Vue.js SPA
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API - Laravel
    location /api {
        root /var/www/hotel/backend/public;
        try_files $uri $uri/ /index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public/index.php;
            include fastcgi_params;
            fastcgi_param HTTP_PROXY "";
        }
    }

    # Direct handling for /api routes
    location ~ ^/api/(.*)$ {
        try_files $uri /api/index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public/index.php;
            include fastcgi_params;
            fastcgi_param HTTP_PROXY "";
        }
    }

    # Laravel Sanctum CSRF cookie endpoint
    location /sanctum {
        root /var/www/hotel/backend/public;
        try_files $uri $uri/ /index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public/index.php;
            include fastcgi_params;
            fastcgi_param HTTP_PROXY "";
        }
    }

    # Direct handling for /sanctum routes
    location ~ ^/sanctum/(.*)$ {
        try_files $uri /sanctum/index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public/index.php;
            include fastcgi_params;
            fastcgi_param HTTP_PROXY "";
        }
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Hide nginx version
    server_tokens off;

    # Logs
    access_log /var/log/nginx/hotel_access.log;
    error_log /var/log/nginx/hotel_error.log;
}
```

**Alternative simpler configuration (recommended):**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    
    # Serve frontend from root
    root /var/www/hotel/frontend/dist;
    index index.html;

    # Frontend - Vue.js SPA (for non-api routes)
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API routes - proxy to Laravel
    location ~ ^/(api|sanctum)/ {
        root /var/www/hotel/backend/public;
        try_files $uri /index.php$is_args$args;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public/index.php;
            include fastcgi_params;
        }
    }

    # Handle PHP files in Laravel public directory
    location ~ \.php$ {
        root /var/www/hotel/backend/public;
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    server_tokens off;

    access_log /var/log/nginx/hotel_access.log;
    error_log /var/log/nginx/hotel_error.log;
}
```

**Enable site and restart Nginx:**
```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/hotel /etc/nginx/sites-enabled/

# Test nginx configuration
sudo nginx -t

# Restart nginx
sudo systemctl restart nginx

# Enable nginx to start on boot
sudo systemctl enable nginx
```

### Step 8: Setup SSL with Let's Encrypt (Recommended)

**Important:** SSL/HTTPS is highly recommended for production. Let's Encrypt provides free SSL certificates.

```bash
# 1. Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# 2. Stop nginx temporarily (or use --nginx option)
sudo systemctl stop nginx

# 3. Get SSL certificate (interactive mode)
# Replace your-domain.com with your actual domain
sudo certbot certonly --standalone -d your-domain.com

# Or if you prefer, use nginx plugin (with nginx running)
# sudo certbot --nginx -d your-domain.com

# 4. Certificate will be saved to:
# /etc/letsencrypt/live/your-domain.com/fullchain.pem
# /etc/letsencrypt/live/your-domain.com/privkey.pem
```

**Update Nginx Configuration for HTTPS:**

```bash
sudo nano /etc/nginx/sites-available/hotel
```

**Replace with SSL-enabled configuration:**

```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    
    # Redirect all HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    # SSL Security Settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Serve frontend from root
    root /var/www/hotel/frontend/dist;
    index index.html;

    # Frontend - Vue.js SPA (for non-api routes)
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API routes - proxy to Laravel
    location ~ ^/(api|sanctum)/ {
        root /var/www/hotel/backend/public;
        try_files $uri /index.php$is_args$args;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public/index.php;
            include fastcgi_params;
            fastcgi_param HTTPS on;
        }
    }

    # Handle PHP files in Laravel public directory
    location ~ \.php$ {
        root /var/www/hotel/backend/public;
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME /var/www/hotel/backend/public$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param HTTPS on;
    }

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    server_tokens off;

    access_log /var/log/nginx/hotel_access.log;
    error_log /var/log/nginx/hotel_error.log;
}
```

**Update Backend .env for HTTPS:**

```bash
cd /var/www/hotel/backend
nano .env
```

**Update these values:**
```env
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=your-domain.com
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

**Update Frontend .env.production:**

```bash
cd /var/www/hotel/frontend
nano .env.production
```

**Update to use HTTPS:**
```env
VITE_API_URL=https://your-domain.com
VITE_API_BASE_URL=https://your-domain.com/api
```

**Rebuild and Restart:**

```bash
# Clear Laravel cache
cd /var/www/hotel/backend
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# Rebuild frontend
cd /var/www/hotel/frontend
npm run build

# Test nginx config
sudo nginx -t

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

**Setup Auto-Renewal:**

```bash
# Certbot automatically adds a cron job, but verify:
sudo systemctl status certbot.timer

# Or manually test renewal:
sudo certbot renew --dry-run

# Certificates will auto-renew before expiry
```

**Verify SSL Installation:**

```bash
# Check certificate expiry
sudo certbot certificates

# Test HTTPS access
curl -I https://your-domain.com

# Should return 200 OK with SSL headers
```

**Troubleshooting SSL:**

```bash
# If certificate fails to renew:
sudo certbot renew --force-renewal

# Check certbot logs:
sudo tail -f /var/log/letsencrypt/letsencrypt.log

# Verify firewall allows HTTPS:
sudo ufw status
sudo ufw allow 'Nginx Full'

# Test SSL configuration:
openssl s_client -connect your-domain.com:443 -servername your-domain.com
```

### Step 9: Setup Process Manager (Optional - for Laravel Queue)

```bash
# Install Supervisor
sudo apt install -y supervisor

# Create supervisor config
sudo nano /etc/supervisor/conf.d/hotel-worker.conf
```

**Supervisor configuration:**
```ini
[program:hotel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hotel/backend/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/hotel/backend/storage/logs/worker.log
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hotel-worker:*
```

### Step 10: Setup Firewall

```bash
# Enable UFW
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

### Step 11: Verify Deployment

1. Visit: `http://your-domain.com`
2. Login with: `owner@hotel.com` / `password`
3. Check all features working

### Troubleshooting

**Error 422 (Unprocessable Content) on POST /api/login:**

This usually means CSRF token issue or validation error.

```bash
# 1. Check Laravel logs for detailed error
cd /var/www/hotel/backend
tail -f storage/logs/laravel.log

# 2. Test CSRF cookie endpoint
curl -v http://your-domain.com/sanctum/csrf-cookie

# Should return 204 and set cookies

# 3. Check CORS configuration
nano config/cors.php

# Ensure:
# 'paths' => ['api/*', 'sanctum/csrf-cookie'],
# 'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
# 'supports_credentials' => true,

# 4. Check .env has correct FRONTEND_URL
cat .env | grep FRONTEND_URL
# Should match your actual domain

# 5. Clear ALL caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache

# 6. Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# 7. In browser DevTools Console, check the actual error:
# Look at Response tab of failed /api/login request
# It will show the exact validation error
```

**Common causes:**

**A. CSRF Token Mismatch:**
```bash
# Frontend is not getting CSRF cookie properly
# Check in browser DevTools > Network tab:
# 1. Visit the site
# 2. Should see request to /sanctum/csrf-cookie
# 3. Check Response Headers - should set XSRF-TOKEN cookie
# 4. Check login request Headers - should include X-XSRF-TOKEN

# Fix: Ensure CORS is configured correctly
cd /var/www/hotel/backend
nano config/cors.php
```

**B. Session configuration mismatch:**
```bash
# .env file check - all must be consistent:
cd /var/www/hotel/backend
cat .env | grep -E "SESSION|SANCTUM|FRONTEND|APP_URL"

# Output should be like:
# APP_URL=http://tazkia-hotel.duckdns.org
# FRONTEND_URL=http://tazkia-hotel.duckdns.org
# SESSION_DOMAIN=tazkia-hotel.duckdns.org
# SANCTUM_STATEFUL_DOMAINS=tazkia-hotel.duckdns.org
# SESSION_SECURE_COOKIE=false

# If using HTTPS, set:
# SESSION_SECURE_COOKIE=true
```

**C. Database issue - user not found:**
```bash
# Check if user exists
cd /var/www/hotel/backend
php artisan tinker

# Then in tinker:
\App\Models\User::where('email', 'owner@hotel.com')->first();
# Should return user object

# If null, seed database:
php artisan db:seed --class=UserSeeder
exit
```

**Login successful but immediately logs out:**

This is a session/cookie issue. Laravel session cookies are not being saved.

```bash
# 1. Check Laravel .env configuration
cd /var/www/hotel/backend
nano .env

# Ensure these settings match your domain:
# SESSION_DRIVER=cookie
# SESSION_DOMAIN=your-actual-domain.com (without http://)
# SESSION_SAME_SITE=lax
# SESSION_SECURE_COOKIE=false (for HTTP) or true (for HTTPS)
# SANCTUM_STATEFUL_DOMAINS=your-actual-domain.com (without http://)
# FRONTEND_URL=http://your-actual-domain.com (with http:// or https://)
# APP_URL=http://your-actual-domain.com (with http:// or https://)

# Example for HTTP (no SSL):
# SESSION_DOMAIN=tazkia-hotel.duckdns.org
# SESSION_SECURE_COOKIE=false
# SANCTUM_STATEFUL_DOMAINS=tazkia-hotel.duckdns.org
# FRONTEND_URL=http://tazkia-hotel.duckdns.org
# APP_URL=http://tazkia-hotel.duckdns.org

# 2. Clear config cache
php artisan config:clear
php artisan config:cache

# 3. Check storage permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage

# 4. Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# 5. Clear browser cookies completely
# In browser: Settings > Privacy > Clear browsing data > Cookies

# 6. Test again
```

**If using duckdns.org or similar dynamic DNS:**
```bash
# .env should be:
SESSION_DOMAIN=your-subdomain.duckdns.org
SANCTUM_STATEFUL_DOMAINS=your-subdomain.duckdns.org
FRONTEND_URL=http://your-subdomain.duckdns.org
APP_URL=http://your-subdomain.duckdns.org
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
```

**Check if cookies are being set:**
```bash
# In browser DevTools (F12):
# 1. Go to Application tab
# 2. Look at Cookies for your domain
# 3. Should see: XSRF-TOKEN and laravel_session cookies
# 4. If not present, session is not being saved
```

**Error 502 (Bad Gateway) on /sanctum/csrf-cookie:**

This means Nginx cannot communicate with PHP-FPM.

```bash
# 1. Check if PHP-FPM is running
sudo systemctl status php8.2-fpm

# 2. If not running, start it
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm

# 3. Check PHP-FPM socket exists
ls -la /var/run/php/php8.2-fpm.sock

# 4. If socket doesn't exist, check PHP-FPM config
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
# Look for: listen = /var/run/php/php8.2-fpm.sock

# 5. Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# 6. Check PHP-FPM error log
sudo tail -f /var/log/php8.2-fpm.log

# 7. Test PHP-FPM
sudo -u www-data SCRIPT_FILENAME=/var/www/hotel/backend/public/index.php \
  REQUEST_METHOD=GET \
  cgi-fcgi -bind -connect /var/run/php/php8.2-fpm.sock
```

**If socket path is different:**
```bash
# Find the correct socket
find /var/run -name "*.sock" | grep php

# Update Nginx config with correct socket path
sudo nano /etc/nginx/sites-available/hotel
# Change all instances of: unix:/var/run/php/php8.2-fpm.sock
# To the correct path found above

# Reload Nginx
sudo systemctl reload nginx
```

**Alternative: Use TCP instead of socket:**
```bash
# Edit PHP-FPM config
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Change:
# listen = /var/run/php/php8.2-fpm.sock
# To:
# listen = 127.0.0.1:9000

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Update Nginx config
sudo nano /etc/nginx/sites-available/hotel
# Change all instances of:
# fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
# To:
# fastcgi_pass 127.0.0.1:9000;

# Reload Nginx
sudo systemctl reload nginx
```

**Error 405 (Method Not Allowed) on POST /api/login:**

This is an Nginx configuration issue. The `alias` directive doesn't work well with POST requests.

```bash
# 1. Use the simpler Nginx configuration provided above
sudo nano /etc/nginx/sites-available/hotel

# 2. Copy the "Alternative simpler configuration (recommended)" from above

# 3. Test and reload
sudo nginx -t
sudo systemctl reload nginx

# 4. Check if Laravel is handling the request
tail -f /var/log/nginx/hotel_error.log
tail -f /var/www/hotel/backend/storage/logs/laravel.log
```

**Test API endpoint manually:**
```bash
# Test POST to login
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@hotel.com","password":"password"}'

# Should return a proper error response, not 405
```

**Frontend shows "Failed to load resource: net::ERR_CONNECTION_REFUSED" on localhost:8000:**

This means frontend is still trying to connect to localhost instead of your domain.

```bash
# 1. Check for hardcoded localhost URLs
cd /var/www/hotel/frontend/src
grep -r "localhost:8000" .

# 2. If found, replace them
find . -type f \( -name "*.js" -o -name "*.vue" \) -exec sed -i "s|http://localhost:8000|http://your-domain.com|g" {} \;

# 3. Rebuild frontend
cd /var/www/hotel/frontend
npm run build

# 4. Clear browser cache and reload
```

**Verify API connection:**
```bash
# Test if backend API is accessible
curl http://your-domain.com/api/test
# or
curl https://your-domain.com/api/test

# Test CSRF cookie endpoint
curl http://your-domain.com/sanctum/csrf-cookie
```

**Permission Issues:**
```bash
# Fix ownership for entire project
sudo chown -R hotel:hotel /var/www/hotel

# Fix storage and cache permissions
sudo chown -R www-data:www-data /var/www/hotel/backend/storage
sudo chown -R www-data:www-data /var/www/hotel/backend/bootstrap/cache
sudo chmod -R 775 /var/www/hotel/backend/storage
sudo chmod -R 775 /var/www/hotel/backend/bootstrap/cache

# If vendor directory has issues
cd /var/www/hotel/backend
rm -rf vendor
composer install --optimize-autoloader --no-dev
```

**Login Failed After Adding New Package (composer require):**

If login was working before but failed after running `composer require` for new features (like Excel export):

```bash
# 1. Check if all dependencies are properly installed
cd /var/www/hotel/backend
composer install --optimize-autoloader --no-dev

# 2. Regenerate autoload files
composer dump-autoload

# 3. Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 4. Rebuild config cache
php artisan config:cache
php artisan route:cache

# 5. Check if new package has config file to publish
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"

# 6. Verify .env has all required variables
cat .env | grep -E "SESSION|SANCTUM|FRONTEND|APP_URL"

# 7. Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# 8. Check Laravel logs for any errors
tail -f storage/logs/laravel.log

# 9. If still not working, check autoload issues
composer validate
composer diagnose

# 10. Frontend also needs to be rebuilt if .env changed
cd /var/www/hotel/frontend
nano .env.production  # Update with correct domain
npm run build
```

**Common issues after composer update:**
- Autoload files not regenerated
- Config cache using old values
- Session configuration changed by new package
- Missing config files from new packages
- Permission issues on vendor directory

```

**Git Ownership Issues:**
```bash
# Add safe directory for git
git config --global --add safe.directory /var/www/hotel
```

**Check current permissions:**
```bash
# Check ownership
ls -la /var/www/hotel

# Check storage permissions
ls -la /var/www/hotel/backend/storage

# Check current user
whoami

# Check current user groups
groups
```

**Check Laravel logs:**
```bash
tail -f /var/www/hotel/backend/storage/logs/laravel.log
```

**Check Nginx logs:**
```bash
tail -f /var/log/nginx/hotel_error.log
```

**Check PHP-FPM logs:**
```bash
tail -f /var/log/php8.2-fpm.log
```

**Clear all caches:**
```bash
cd /var/www/hotel/backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Maintenance

**Update application:**
```bash
cd /var/www/hotel
git pull

# Backend
cd backend
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend
cd ../frontend
npm install
npm run build

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

### Security Checklist

- [ ] Set APP_DEBUG=false in .env
- [ ] Use strong database password
- [ ] Enable SSL certificate
- [ ] Setup firewall (UFW)
- [ ] Regular backups of database
- [ ] Keep system updated
- [ ] Use strong passwords for admin users
- [ ] Review Laravel security best practices

### Backup Database

```bash
# Create backup
pg_dump -U hotel hotel > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
psql -U hotel hotel < backup_file.sql
```

### Performance Optimization

```bash
# Enable OPcache (already enabled in php.ini)
# Verify:
php -i | grep opcache

# For Laravel
cd /var/www/hotel/backend
php artisan optimize

# For Nginx, add to server block:
# location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
#     expires 1y;
#     add_header Cache-Control "public, immutable";
# }
```

---

## Production Deployment Complete! 🚀

Your hotel management system should now be running on Ubuntu 24.04.

**Default Login Credentials:**
- Owner: `owner@hotel.com` / `password`
- Front Desk: `frontdesk@hotel.com` / `password`
- Housekeeping: `housekeeping@hotel.com` / `password`

**⚠️ IMPORTANT: Change default passwords immediately after first login!**
