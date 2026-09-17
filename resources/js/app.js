import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import './echo';
import './auth';
import './register';
import './forgot';
import './verify';
import './reset';
import './expired';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                registration.onupdatefound = () => {
                    const installingWorker = registration.installing;
                    if (installingWorker) {
                        installingWorker.onstatechange = () => {
                            if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('New content available; refresh to update.');
                            }
                        };
                    }
                };
            })
            .catch((error) => {
                console.error('Service Worker registration failed:', error);
            });
    });
}