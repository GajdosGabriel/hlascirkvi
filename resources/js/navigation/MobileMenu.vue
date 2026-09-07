<template>
    <div class="lg:hidden">
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-md p-2 text-blue-100 transition-colors hover:bg-blue-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-300"
            :aria-expanded="open ? 'true' : 'false'"
            aria-controls="mobile-menu"
            aria-label="Menu"
            @click="toggle"
        >
            <svg
                v-if="!open"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg
                v-else
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <transition name="mobile-menu">
            <div
                v-show="open"
                id="mobile-menu"
                class="absolute inset-x-0 top-full z-40 border-t border-blue-800 bg-blue-900 shadow-xl"
            >
                <div class="mx-auto max-w-7xl space-y-1 px-2 py-3">
                    <slot></slot>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],

    data() {
        return {
            open: false
        };
    },

    methods: {
        toggle() {
            this.open = !this.open;
        }
    }
};
</script>

<style scoped>
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.mobile-menu-enter,
.mobile-menu-leave-to {
    opacity: 0;
    transform: translateY(-0.5rem);
}
</style>
