// Average width of an uppercase Montserrat Bold letter plus the imprint's letter spacing, in ems.
const LETTER_WIDTH = 0.86;

/**
 * The diary and pen in the home page hero, carrying the company name a visitor types in the finish they pick.
 * Long names shrink so they still fit across the diary cover and along the pen.
 */
export default () => ({
    name: '',
    finish: 'engraved',

    get imprint() {
        return this.name.trim() || 'Your brand';
    },

    get diarySize() {
        return fit(this.imprint, 46, 3, 6.5);
    },

    get penSize() {
        return fit(this.imprint, 22, 0.9, 2.4);
    },
});

/**
 * The font size, in container units, at which the text fills the given width, kept between min and max.
 */
function fit(text, width, min, max) {
    const size = width / (text.length * LETTER_WIDTH);

    return `${Math.min(max, Math.max(min, size))}cqw`;
}
