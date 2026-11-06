import './bootstrap';
import * as bootstrap from 'bootstrap';
import 'aos/dist/aos.css';
import Alpine from 'alpinejs';
import AOS from 'aos';
import Chart from 'chart.js/auto';
import confetti from 'canvas-confetti';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

window.Alpine = Alpine;
window.Chart = Chart;
window.confetti = confetti;
window.bootstrap = bootstrap;
window.L = L;

// Leaflet's default marker images are resolved relative to the CSS file,
// which breaks once Vite hashes the assets. Point them at the bundled URLs.
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

// Modals nested inside animated/transformed containers (AOS, cards) get
// trapped below the backdrop because the ancestor creates a stacking context.
// Relocate them to <body> right before Bootstrap displays them.
document.addEventListener('show.bs.modal', (event) => {
    const modal = event.target;
    if (modal instanceof Element && modal.classList.contains('modal') && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Apply consistent reveal motion to recurring dashboard components.
    if (!reduceMotion) {
        document.querySelectorAll('.stat-card, .hero-card, .tes-card, .auth-card').forEach((element, index) => {
            if (!element.hasAttribute('data-aos')) {
                element.setAttribute('data-aos', 'fade-up');
                element.setAttribute('data-aos-delay', String(Math.min(index % 5, 4) * 65));
            }
        });
    }

    AOS.init({
        once: true,
        duration: reduceMotion ? 0 : 650,
        easing: 'ease-out-cubic',
        offset: 32,
        disable: reduceMotion,
    });

    document.querySelectorAll('.alert[data-autohide]').forEach((el) => {
        window.setTimeout(() => {
            if (window.bootstrap && window.bootstrap.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(el).close();
            }
        }, 4200);
    });
});

Alpine.start();
