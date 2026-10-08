# Home Care Website (Laravel + Filament CMS)

Home care business website with a full admin panel. The design is the same as the original static site; all content is editable from the admin.

- **Laravel 13 + Filament 5**, PHP 8.3+, MySQL
- Admin panel at `/admin`: pages, Page Builder (20 section types), on-page SEO with a live SEO score, services, media library, enquiries (leads) with CSV export, site settings (phone, WhatsApp, email, SMTP, colours, menu, footer, tracking codes), one-click database update
- Web installer at `/install` (no SSH needed on shared hosting)

## Install on Hostinger (no SSH)

See [INSTALL-HOSTINGER.txt](INSTALL-HOSTINGER.txt). Upload the ready-made ZIP (includes `vendor/`), extract it into `public_html`, then open `/install`.

## Run from this repository

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Then open `http://localhost:8000/install`.

## Build the upload ZIP

```bash
composer install --no-dev --optimize-autoloader
```

Zip the whole folder (including `vendor/`, `.env` with a fresh `APP_KEY`, and the root `.htaccess`), but leave out `storage/app/installed.lock`, `database/*.sqlite` and `public/uploads/*`.

## Where things are

| Path | What |
|---|---|
| `resources/site/` | Website design (original templates, header/footer, Page Builder sections) |
| `app/Services/SiteRenderer.php` | Renders a page from the database |
| `app/Services/SeoAnalyzer.php` | SEO score checks |
| `app/Filament/` | Admin panel (resources, settings, system page, widgets) |
| `config/site_defaults.php` | Default settings copied in at install |
| `database/seeders/` | Original website content |
| `public/assets/site/` | Website CSS / JS |
