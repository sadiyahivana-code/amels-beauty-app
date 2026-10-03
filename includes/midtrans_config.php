<?php
// includes/midtrans_config.php — isi Server Key & Client Key dari dashboard Midtrans (Settings > Access Keys)
// Sandbox = mode testing (aman, bukan uang asli). Ganti IS_PRODUCTION jadi true kalau nanti sudah pakai akun live.

define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-ISI_DI_SINI');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-ISI_DI_SINI');
define('MIDTRANS_IS_PRODUCTION', false);

function midtransConfigured() {
    return strpos(MIDTRANS_SERVER_KEY, 'ISI_DI_SINI') === false
        && strpos(MIDTRANS_CLIENT_KEY, 'ISI_DI_SINI') === false;
}

function midtransSnapUrl() {
    return MIDTRANS_IS_PRODUCTION
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
}

function midtransSnapJsUrl() {
    return MIDTRANS_IS_PRODUCTION
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
}
