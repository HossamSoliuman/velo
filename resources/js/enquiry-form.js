const GENERIC_ERROR = 'Sorry, we could not send your enquiry. Please try again, or call or email us instead.';

/**
 * Sends the enquiry form in the background and shows validation errors next to each field.
 * Without JavaScript the form posts normally and the server renders the same errors and thank-you note.
 *
 * @param {Record<string, string[]>} errors Validation errors from a previous normal post.
 * @param {string} formError An error that isn't about one field, such as too many enquiries.
 * @param {string} sentMessage The thank-you note, when the enquiry has just been sent.
 */
export default (errors = {}, formError = '', sentMessage = '') => ({
    errors,
    formError,
    sentMessage,
    sending: false,

    get sent() {
        return this.sentMessage !== '';
    },

    async submit(event) {
        const form = event.target;

        this.sending = true;
        this.errors = {};
        this.formError = '';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                this.errors = data.errors ?? {};
                this.formError = this.errors.product_id?.[0] ?? '';
                this.$nextTick(() => form.querySelector('[aria-invalid="true"]')?.focus());
            } else if (response.status === 419) {
                this.formError = 'This page has been open for a while. Please refresh it and send your enquiry again.';
            } else if (response.status === 429) {
                this.formError = data.message ?? GENERIC_ERROR;
            } else if (! response.ok) {
                this.formError = GENERIC_ERROR;
            } else {
                form.reset();
                this.sentMessage = data.message;
                this.$nextTick(() => this.$refs.thanks.focus());
            }
        } catch {
            this.formError = GENERIC_ERROR;
        } finally {
            this.sending = false;
        }
    },

    startAgain() {
        this.sentMessage = '';
        this.formError = '';
    },
});
