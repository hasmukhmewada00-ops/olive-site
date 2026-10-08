# olive-site

Website for **Olive Catering Company** (Gandhidham, Kutch, serving across Gujarat). Built and maintained by One Man Marketing.

Plain PHP 8.3+ on Hostinger shared hosting. No WordPress, no database, no build step.

## Environments

| Branch | Site | Notes |
| --- | --- | --- |
| `staging` | https://staging.olivecateringcompany.in | Password-gated in PHP, `noindex`, robots blocks all |
| `main` | https://olivecateringcompany.in | Live |

hPanel → Advanced → Git pulls each branch into that site's `public_html`. Push = deploy.

## Folder map

```
.htaccess              HTTPS + non-www (one 301), security headers, caching, private folder blocks
index.php              Holding page until site_live = true, then the full site
enquiry.php            Form handler: spam checks, validation, CSV backup, email, Sheet, Meta CAPI
thank-you.php          Fires generate_lead once per lead (dataLayer), WhatsApp follow-up
robots.php             Served as /robots.txt
404.php
admin/                 Admin panel (login, contact details, photos, gallery, associations, areas, reviews...)
app/bootstrap.php      Settings, staging gate, content loading + admin overrides, helpers (blocked from web)
app/cms.php            Admin library: login, CSRF, backups, image processing (blocked from web)
app/leads.php          Lead delivery functions
app/lib/PHPMailer/     PHPMailer 6.10.0 (LGPL), SMTP email
assets/css, js, img    Theme files
assets/js/attribution.js  UTM / click-ID capture, hidden form fields, click events
partials/              GTM snippets (blocked from web)
docs/apps-script.gs    Google Sheet logger (blocked from web)
config.sample.php      Settings template -> copy to config.php on each server
content.sample.json    Base content (copy, SEO, FAQ, sections). Edited in code only.
data/                  NOT in git: cms.json (admin edits), backups, leads CSV, logs (blocked from web)
uploads/               NOT in git: client images (scripts cannot run here)
```

## First install on a server (once per site)

1. Connect the branch in hPanel → Advanced → Git (target: `public_html`, empty first).
2. In File Manager, copy `config.sample.php` to `config.php` and fill in:
   - `env`: `staging` or `production`
   - `staging_pass_hash` (staging only): `php -r "echo password_hash('PASSWORD', PASSWORD_DEFAULT);"`
   - `app_secret`: `php -r "echo bin2hex(random_bytes(32));"`
   - SMTP password once `info@olivecateringcompany.in` exists
3. Set `admin_pass_hash` (same command as above) to unlock `/admin/`.
4. Leave `site_live` = `false` until launch day.

## Admin panel (/admin/)

- One login (`admin_user` / `admin_pass_hash` in config.php). 5 wrong tries = 15 min lockout. 30 min idle logout.
- Editable: calling + WhatsApp numbers, email, Instagram, hours, FSSAI, address, map, GBP link; every section photo; gallery (add, describe, reorder, remove, max 30); associations (current / previous); highlights strip; service areas; live counter dishes; client reviews; announcement bar; pop-up; footer text.
- Not editable (protects SEO): headings, service and section copy, FAQ, title/meta, schema structure.
- Edits are saved to `data/cms.json` and applied on top of `content.sample.json`, so code updates to copy still flow through.
- Every save backs up the previous version (last 20 kept, one-click restore) and sends `X-LiteSpeed-Purge: *`.
- Photos: JPG/PNG/WebP, real type checked, min 800 px, re-encoded through GD (strips EXIF and anything hidden), auto-rotated, saved as WebP at 480/960/1600 px under byte budgets (hero 1600 under 250 KB), named from the alt text. Alt text is mandatory (10 to 125 chars).
- Articles (`/admin/articles.php`): write, tag, save as draft, preview, publish, unpublish and delete articles; change the cover photo of the built-in guides. Client articles live in `data/articles.json` (markdown-lite body, escaped on output). Publishing needs title, 70-160 char description, 300+ words, cover photo with alt text and a tag. Cover photos also get a 1200x630 share image. Tag pages at `/blog/tag/{tag}/` are noindex,follow. Drafts are hidden from the blog and sitemap (preview via a signed link).
- Staging and live each have their own admin and their own `data/` and `uploads/`.

`config.php`, `data/` and `uploads/` are never touched by a deploy.

## Tracking

- GTM `GTM-TKTF7C5X` loads GA4 (`G-FG2XD237R9`) and, later, the Meta Pixel. Nothing is hard-coded in the site except GTM.
- dataLayer events: `form_start`, `generate_lead` (thank-you page only), `whatsapp_click`, `call_click`, `email_click`, `map_click`, `menu_view`.
- Every page pushes `site_env` (`staging` / `production`) and `page_type` before GTM loads.
- Visit any page once with `?omm_internal=1` to mark your browser as internal (`?omm_internal=0` to undo).

## Rules

- Never commit `config.php`, leads, or client photos.
- Never mention anything other than Olive and One Man Marketing in code, comments or content.
- Every lead is written to `data/leads/YYYY-MM.csv` before anything else, so an email or Sheet failure never loses it.
