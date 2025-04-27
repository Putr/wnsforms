# WNSForms - Who Needs SaaS Forms

WNSForms is a simple, self-hosted solution for handling form submissions from static websites without relying on third-party services. Built on Laravel, it provides a clean admin interface to manage forms, submissions, and Slack integrations.

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
git clone https://github.com/Putr/wnsforms.git
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

# Install the frontend dependencies
npm i

# Build the frontend (run npm run dev to start the development server)
npm run build

# Set permissions
chmod -R 775 storage bootstrap/cache

# Publish Livewire assets
php artisan livewire:publish

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

## 📝 Usage

1. Create a new form in the admin dashboard
2. Copy the generated HTML form code
3. Paste it into your website
4. Start receiving submissions!
