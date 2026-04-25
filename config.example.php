<?php
/**
 * Snake II — Configuration template
 *
 * 1. Copy this file to `config.php`
 * 2. Replace each value below with your real credentials
 * 3. Open `index.html` and update APP_SECRET to match this file
 *
 * Never commit your real `config.php` — it should stay private.
 * The `.gitignore` already excludes it.
 */

// ── Database connection ──
// Get these from your hosting control panel (cPanel, Plesk, etc.)
define('DB_HOST', 'localhost');
define('DB_NAME', 'YOUR_DATABASE_NAME');
define('DB_USER', 'YOUR_DATABASE_USER');
define('DB_PASS', 'YOUR_DATABASE_PASSWORD');

// ── Anti-cheat secret ──
// This should be a long random string. Anyone with this string can
// submit fake scores, so keep it private.
//
// Generate one quickly:   openssl rand -hex 16
//
// IMPORTANT: this exact value must also be set in `index.html`
// (search for APP_SECRET inside the script).
define('APP_SECRET', 'CHANGE_ME_TO_A_LONG_RANDOM_STRING');
