import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

Alpine.plugin([collapse, focus]);

window.Alpine = Alpine;

Alpine.start();
