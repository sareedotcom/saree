

        'use strict';
         
        const cacheName = 'https://www.saree.com/-superpwa-2.0.2';
        const startPage = 'https://www.saree.com/';
        const offlinePage = 'https://www.saree.com/';
        const filesToCache = [startPage, offlinePage];
        const neverCacheUrls = [/\/checkout/,/\/customer\/account\/login/,/preview=true/];

        self.addEventListener('install', function(e) {
            console.log('SuperPWA service worker installation');
            e.waitUntil(
                caches.open(cacheName).then(function(cache) {
                    console.log('SuperPWA service worker caching dependencies');
                    filesToCache.map(function(url) {
                        return cache.add(url).catch(function (reason) {
                            return console.log('SuperPWA: ' + String(reason) + ' ' + url);
                        });
                    });
                })
            );
        });

        self.addEventListener('activate', function(e) {
            console.log('SuperPWA service worker activation');
            e.waitUntil(
                caches.keys().then(function(keyList) {
                    return Promise.all(keyList.map(function(key) {
                        if ( key !== cacheName ) {
                            console.log('SuperPWA old cache removed', key);
                            return caches.delete(key);
                        }
                    }));
                })
            );
            return self.clients.claim();
        });

       
        self.addEventListener('fetch', function(e) {
        
            if ( ! neverCacheUrls.every(checkNeverCacheList, e.request.url) ) {
              console.log( 'SuperPWA: Current request is excluded from cache.' );
              return;
            }
            
            if ( ! e.request.url.match(/^(http|https):\/\//i) )
                return;
            
            if ( new URL(e.request.url).origin !== location.origin )
                return;
            
            if ( e.request.method !== 'GET' ) {
                e.respondWith(
                    fetch(e.request).catch( function() {
                        return caches.match(offlinePage);
                    })
                );
                return;
            }
            
            if ( e.request.mode === 'navigate' && navigator.onLine ) {
                e.respondWith(
                    fetch(e.request).then(function(response) {
                        return caches.open(cacheName).then(function(cache) {
                            cache.put(e.request, response.clone());
                            return response;
                        });  
                    })
                );
                return;
            }

            e.respondWith(
                caches.match(e.request).then(function(response) {
                    return response || fetch(e.request).then(function(response) {
                        return caches.open(cacheName).then(function(cache) {
                            cache.put(e.request, response.clone());
                            return response;
                        });  
                    });
                }).catch(function() {
                    return caches.match(offlinePage);
                })
            );
        });

        function checkNeverCacheList(url) {
            if ( this.match(url) ) {
                return false;
            }
            return true;
        }
        