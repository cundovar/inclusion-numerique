const CACHE_NAME = 'spa-hybrid-v1';
const urlsToCache = [
  
  '/spa/',
  '/build/app.js',
  '/build/app.css',
  '/spa/pwa.png',
  '/spa/pwa1.png',
  '/offline.html'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async cache => {
      for (const url of urlsToCache) {
        try {
          await cache.add(url);
          console.log(`✅ ${url} a été ajouté au cache`);
        } catch (e) {
          console.warn(`⚠️ Impossible de cacher ${url}`, e);
        }
      }
    })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      )
    )
  );
});

self.addEventListener('fetch', event => {
    event.respondWith(
      caches.match(event.request).then(response => {
        return response || fetch(event.request).catch(() => {
          console.warn(`⚠️ Échec de fetch pour ${event.request.url}, fallback offline`);
          return caches.match('/offline.html');
        });
      })
    );
  });
  
