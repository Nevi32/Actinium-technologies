// Define static cache names
const staticCacheName = 'static-cache-v1';
const dynamicCacheName = 'dynamic-cache-v1';

// Define assets to cache
const assets = [
    '/',
    '/index.html',
    '/SRstyles.css',
    '/StoreResources.php',
    '/calc_commission.php',
    '/checkLHInventory.php',
    '/check_inventory.php',
    '/fetchInventoryStats.php',
    '/fetchNewSales.php',
    '/fetchProfitStats.php',
    '/fetchRestockOrders.php',
    '/home.php',
    '/inventory.php',
    '/inventory2.php',
    '/inventorystyle.css',
    '/login.html',
    '/login.php',
    '/logout.php',
    '/memo.php',
    '/pics/smart-inventory-management-system.jpg.webp'
    // Add paths to other static files here
];

// Install service worker
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(staticCacheName).then(cache => {
            console.log('Caching assets');
            cache.addAll(assets);
        })
    );
});

// Activate service worker
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => key !== staticCacheName && key !== dynamicCacheName)
                .map(key => caches.delete(key))
            );
        })
    );
});

// Fetch event
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(cacheResponse => {
            return cacheResponse || fetch(event.request).then(fetchResponse => {
                return caches.open(dynamicCacheName).then(cache => {
                    cache.put(event.request.url, fetchResponse.clone());
                    return fetchResponse;
                });
            });
        }).catch(() => {
            return caches.match('/offline.html'); // Serve a fallback page when offline
        })
    );
});

