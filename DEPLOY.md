# Deploying Sparx Bit (Laravel) to the VPS

This replaces the old Next.js site at **app.sparxbit.com** with the Laravel app.
It serves the web UI, the JSON API (for mobile), and the Filament admin, all from
one app, backed by MySQL.

## 0. Server requirements
- PHP **8.2+** with extensions: `mbstring, pdo_mysql, intl, zip, gd, sodium, bcmath, ctype, fileinfo, openssl, tokenizer, xml`
- Composer 2
- Node 18+ (to build front-end assets)
- MySQL 8 (DB `sparx_bit` already exists on the VPS)
- Nginx + PHP-FPM

## 1. Get the code
```bash
cd /var/www
git clone <your-laravel-repo-url> sparxbit-laravel   # or: git pull in an existing checkout
cd sparxbit-laravel
```

## 2. Install dependencies
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build          # builds public/build (compiled CSS/JS)
```

## 3. Environment
```bash
cp .env.production.example .env
# edit .env — fill APP_KEY (next line generates it), DB password, SendGrid key,
# Google/Apple creds, and confirm APP_URL=https://app.sparxbit.com
php artisan key:generate
```

## 4. Database + storage
```bash
php artisan migrate --force      # creates all tables incl. sessions/cache/jobs
php artisan storage:link         # public symlink for uploaded profile photos
```
(Optional) seed an admin/demo: `php artisan db:seed` — or set a user's `is_admin=1`
in MySQL to grant admin-panel access at `/admin`.

## 5. Cache for production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6. Permissions
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

## 7. Nginx (point the domain at `public/`)
```nginx
server {
    listen 443 ssl;
    server_name app.sparxbit.com;
    root /var/www/sparxbit-laravel/public;

    # ssl_certificate ... (your existing certs)
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```
Then: `nginx -t && systemctl reload nginx`.
**Cutover note:** this is where you stop the old Next.js pm2 process and switch the
`app.sparxbit.com` server block to this one.

## 8. Reminders — cron (required for reminder emails)
Add one system cron entry so the scheduler fires `bits:send-reminders` every minute:
```bash
* * * * * cd /var/www/sparxbit-laravel && php artisan schedule:run >> /dev/null 2>&1
```

## 9. Browser push notifications (Web Push)
Generate a VAPID key pair **once** on the server and keep it stable (if you change
it after browsers subscribe, those subscriptions stop working):
```bash
php artisan webpush:vapid        # writes VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY to .env
php artisan config:cache
```
Requires the PHP `openssl`, `gmp` (or `bcmath`), `mbstring` and `curl` extensions
(all standard on the server). Browser popups are delivered by the same
`bits:send-reminders` job, so the cron in §8 is what makes them fire on schedule;
the **"Send a test notification"** button on *Profile → Notifications* works
immediately, independent of the cron. Served over HTTPS only (already the case).

## 10. (Optional) queue worker
Mail currently sends synchronously, so a worker isn't required. If you later queue
jobs, run a Supervisor-managed `php artisan queue:work`.

## 11. SendGrid domain + OAuth redirect URIs
- Finish **SendGrid domain authentication** (CNAMEs) so mail sends as `noreply@…`
  and lands in the inbox.
- In Google/Apple consoles, register the production redirect URIs:
  - `https://app.sparxbit.com/auth/google/callback`
  - `https://app.sparxbit.com/auth/apple/callback`

## Redeploying later (manual)
```bash
cd /var/www/sparxbit-laravel
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache route:cache view:cache
systemctl reload php8.2-fpm
```

## Mobile app
The React Native app (in the `Sparxbit` monorepo, `apps/mobile`) is already
repointed to this API. Build/submit it with Expo (`eas build`), setting
`EXPO_PUBLIC_API_URL=https://app.sparxbit.com` (or your API host).
