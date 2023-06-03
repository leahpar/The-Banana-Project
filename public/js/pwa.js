
navigator.serviceWorker
    .register('/service-worker.js')
    .then(function (registration) {
        console.log('Service worker successfully registered.');
        return registration;
    })
    .catch(function (err) {
        console.error('Unable to register service worker.', err);
    });

