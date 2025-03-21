<?php
// ✅ Only send headers if not already sent
if (!headers_sent()) {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME-type sniffing
    header('X-Content-Type-Options: nosniff');

    // Hide referrer info on HTTP downgrades
    header('Referrer-Policy: no-referrer-when-downgrade');

    // Restrict access to browser features (replace values as needed)
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // Optional: Enforce HTTPS on supported browsers
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
