/**
 * Page Transition Optimization
 * Preload critical resources and enable smooth transitions
 */

// Preload next page resources on hover
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="/"]');

    links.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const href = this.getAttribute('href');
            if (href && !this.dataset.preloaded) {
                const preload = document.createElement('link');
                preload.rel = 'prefetch';
                preload.href = href;
                document.head.appendChild(preload);
                this.dataset.preloaded = 'true';
            }
        });
    });
});

// Enable faster page loads with turbo/livewire
if (window.Livewire) {
    window.Livewire.onPageExpired((response, message) => {
        console.log('Page expired, reloading...');
    });
}
