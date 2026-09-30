<?php
/**
 * Olive Catering Company - server settings.
 *
 * Copy this file to config.php on EACH server (live and staging) using
 * hPanel File Manager, then fill in the values. config.php is never
 * committed to git and is blocked from the web by .htaccess.
 */
return [
    // 'production' on olivecateringcompany.in, 'staging' on staging.olivecateringcompany.in
    'env' => 'staging',

    // false = show the "coming soon" holding page (noindex). true = full site.
    'site_live' => false,

    // Canonical base URL, no trailing slash.
    'base_url' => 'https://olivecateringcompany.in',

    // Staging login gate (only used when env = 'staging').
    // Make the hash with: php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
    'staging_user' => 'olive',
    'staging_pass_hash' => '',

    // Tracking
    'gtm_id' => 'GTM-TKTF7C5X',

    // Enquiry email (Hostinger SMTP)
    'smtp' => [
        'host' => 'smtp.hostinger.com',
        'port' => 465,               // 465 = SSL
        'secure' => 'ssl',
        'user' => 'info@olivecateringcompany.in',
        'pass' => '',
        'from_email' => 'info@olivecateringcompany.in',
        'from_name' => 'Olive Website',
    ],
    // Who receives new enquiries (one or more addresses)
    'lead_recipients' => ['info@olivecateringcompany.in'],

    // Google Sheet logging (Apps Script web app URL, see docs/apps-script.gs)
    'sheet_webhook_url' => '',
    'sheet_webhook_secret' => '',   // same random string as in the Apps Script

    // Meta Conversions API (keep false until ads run)
    'capi_enabled' => false,
    'meta_dataset_id' => '',
    'meta_access_token' => '',
    'meta_test_event_code' => '',   // only while testing in Events Manager
    'meta_api_version' => 'v23.0',  // Graph API version; update when Meta retires it

    // Random 64-char string, used to sign form tokens. Generate with:
    // php -r "echo bin2hex(random_bytes(32));"
    'app_secret' => '',
];
