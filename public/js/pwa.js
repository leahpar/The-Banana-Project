if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
        navigator.serviceWorker
            .register("/service-worker.js")
            .then(res => console.log("[OK] Service worker registered"))
            .catch(err => console.log("[ERR] Service worker not registered", err))
    })
}
else {
    console.log('[WARNING] Service worker is not supported.');
}
