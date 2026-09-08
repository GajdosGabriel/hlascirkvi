<template>
    <div class="relative" :class="open ? 'z-50' : ''" @click.stop @keydown.esc.stop="close">
        <button type="button" ref="trigger" @click="toggle" :aria-expanded="String(open)" aria-label="Možnosti položky" title="Možnosti položky">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-7 w-7 cursor-pointer bg-gray-100 rounded-full text-gray-400 hover:bg-gray-200 p-1"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"

        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
            />
        </svg>
        </button>
        <div class="dropdown-menu dropdown-slot-menu" :class="{ 'dropdown-menu--left': align === 'left' }" v-if="open" @click="open = false">
            <slot></slot>
        </div>
    </div>
</template>
<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],
    props: { align: { type: String, default: "right" } },
    data: function () {
        return {
            open: false,
        };
    },

    methods: {
        close() { this.open = false; this.$refs.trigger.focus(); },
        toggle: function () {
            this.open = !this.open;
        },
    },

    computed: {
        authUser: function () {
            return window.App;
        },
    },
};
</script>

<style>
.dropdown-slot-menu { min-width: 10rem; margin-top: .25rem; padding: .25rem; white-space: nowrap; }
.dropdown-slot-menu a, .dropdown-slot-menu button { display: flex; align-items: center; gap: .4rem; width: 100%; padding: .5rem .75rem; border: 0; border-radius: .25rem; background: transparent; color: inherit; font-size: .8125rem; text-align: left; }
.dropdown-slot-menu a:hover, .dropdown-slot-menu button:hover { background: #f3f4f6; color: #374151; }
.dropdown-slot-menu .ar-act--danger:hover { color: #b91c1c; }
.dropdown-slot-menu .ar-act--ok:hover { color: #047857; }
</style>
