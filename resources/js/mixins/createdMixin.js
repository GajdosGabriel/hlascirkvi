// Capture clicks before a dropdown can stop propagation.
export const createdMixin = {
    mounted() {
        document.addEventListener('click', this.closeOnOutsideClick, true);
        document.addEventListener('keyup', this.closeOnEscape);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.closeOnOutsideClick, true);
        document.removeEventListener('keyup', this.closeOnEscape);
    },
    methods: {
        closeOnOutsideClick(event) {
            if (!this.$el.contains(event.target)) this.open = false;
        },
        closeOnEscape(event) {
            if (event.key === 'Escape') this.open = false;
        },
    },
};
