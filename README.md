# WNSForms

> A self-hosted form handling solution that doesn't require expensive SaaS subscriptions.

WNSForms is a simple, elegant solution for handling form submissions without relying on third-party services. Built on Laravel, it provides a clean admin interface to manage forms, submissions, and integrations.

![WNSForms](https://img.shields.io/badge/WNSForms-Who%20Needs%20SaaS%20Forms-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## ✨ Features

- **Admin Dashboard** - Manage all your forms in one place
- **Email Notifications** - Get notified of new submissions
- **Slack Integration** - Receive submissions directly in your Slack channels
- **Spam Protection** - Built-in honeypot fields and rate limiting
- **Domain Restrictions** - Only accept submissions from allowed domains
- **Custom Redirects** - Configure success and error redirects

## 🚀 Quick Start

```bash
# Clone the repository
git clone https://github.com/yourusername/wnsforms.git
cd wnsforms

# Install dependencies
composer install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Create a user
php artisan make:filament-user
```

## 💻 Development

### Prerequisites

- PHP 8.2+
- Composer
- SQLite, MySQL, or PostgreSQL

### Docker Development

```bash
# Start the Docker environment
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Create a user
./vendor/bin/sail artisan make:filament-user
```

## 🌐 Production Deployment

### Server Requirements

- PHP 8.2+
- Nginx or Apache
- MySQL, PostgreSQL, or SQLite
- Composer

### Deployment Steps

1. **Set up your server**

   ```bash
   # Install dependencies
   apt-get update
   apt-get install -y php8.2-fpm php8.2-mbstring php8.2-xml php8.2-zip php8.2-mysql nginx
   ```

2. **Clone and configure**

   ```bash
   # Clone repository
   git clone https://github.com/yourusername/wnsforms.git /var/www/wnsforms
   cd /var/www/wnsforms
   
   # Install dependencies
   composer install --no-dev --optimize-autoloader
   
   # Set permissions
   chown -R www-data:www-data /var/www/wnsforms
   chmod -R 755 /var/www/wnsforms/storage
   
   # Configure environment
   cp .env.example .env
   php artisan key:generate
   
   # Edit .env file with production settings
   nano .env
   
   # Run migrations
   php artisan migrate --force
   
   # Cache configuration
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Configure Nginx**

   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /var/www/wnsforms/public;
   
       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";
   
       index index.php;
       charset utf-8;
   
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
   
       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }
   
       error_page 404 /index.php;
   
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }
   
       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

4. **Set up SSL with Let's Encrypt**

   ```bash
   apt-get install -y certbot python3-certbot-nginx
   certbot --nginx -d your-domain.com
   ```

5. **Configure Supervisor for Queue Worker**

   ```bash
   apt-get install -y supervisor
   
   # Create configuration file
   nano /etc/supervisor/conf.d/wnsforms.conf
   ```

   Add the following content:

   ```
   [program:wnsforms-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/wnsforms/artisan queue:work --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   stopasgroup=true
   killasgroup=true
   user=www-data
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/var/www/wnsforms/storage/logs/worker.log
   stopwaitsecs=3600
   ```

   Start the supervisor:

   ```bash
   supervisorctl reread
   supervisorctl update
   supervisorctl start wnsforms-worker:*
   ```

6. **Set up Cron for Scheduled Tasks**

   ```bash
   crontab -e
   ```

   Add the following line:

   ```
   * * * * * cd /var/www/wnsforms && php artisan schedule:run >> /dev/null 2>&1
   ```

## 📝 Usage

1. Create a new form in the admin dashboard
2. Copy the generated HTML form code
3. Paste it into your website
4. Start receiving submissions!
