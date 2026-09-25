import Alpine from './alpine';
import 'trix';
import 'trix/dist/trix.css';
import '../css/admin.css';

/**
 * Same rules as Laravel's Str::slug for the Latin text the admin types, used to preview generated slugs.
 */
const slugify = (value) =>
    value
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/@/g, '-at-')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

Alpine.data('slugField', (name = '', slug = '') => ({
    name,
    slug,
    get suggestedSlug() {
        return slugify(this.name);
    },
}));

// Rich text is text-only in the admin: block file attachments from drag, drop and paste.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());

Alpine.start();
