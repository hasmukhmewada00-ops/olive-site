# olive-site

Website for **Olive Catering Company** (Adipur / Gandhidham, Kutch). Built and maintained by One Man Marketing.

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
app/bootstrap.php      Settings, staging gate, helpers (blocked from web)
app/leads.php          Lead delivery functions
app/lib/PHPMailer/     PHPMailer 6.10.0 (LGPL), SMTP email
assets/css, js, img    Theme files
assets/js/attribution.js  UTM / click-ID capture, hidden form fields, click events
partials/              GTM snippets (blocked from web)
docs/apps-script.gs    Google Sheet logger (blocked from web)
config.sample.php      Settings template -> copy to config.php on each server
content.sample.json    Seed content -> copied to data/content.json on first install
data/                  NOT in git: content.json, leads CSV, backups, logs (blocked from web)
uploads/               NOT in git: client images (scripts cannot run here)
```

## First install on a server (once per site)

1. Connect the branch in hPanel → Advanced → Git (target: `public_html`, empty first).
2. In File Manager, copy `config.sample.php` to `config.php` and fill in:
   - `env`: `staging` or `production`
   - `staging_pass_hash` (staging only): `php -r "echo password_hash('PASSWORD', PASSWORD_DEFAULT);"`
   - `app_secret`: `php -r "echo bin2hex(random_bytes(32));"`
   - SMTP password once `info@olivecateringcompany.in` exists
3. Leave `site_live` = `false` until launch day.

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
