// service-worker.js — cukup untuk syarat "installable PWA".
// Data transaksi/stok selalu diambil online (network), bukan dari cache,
// supaya ga ada resiko data basi (stok/transaksi harus selalu real-time).

const CACHE_NAME = 'amels-beauty-static-v1';
const STATIC_ASSETS = [
  'assets/css/style.css',
  'assets/icons/icon-192.png',
  'assets/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Hanya aset statis (css/icon) yang dicache. Halaman .php (data dinamis)
  // selalu diambil langsung dari server.
  if (STATIC_ASSETS.some((asset) => url.pathname.endsWith(asset))) {
    event.respondWith(
      caches.match(event.request).then((cached) => cached || fetch(event.request))
    );
  }
});
