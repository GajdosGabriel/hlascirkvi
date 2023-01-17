<template>
    <div class="relative z-10 px-2">
        <div @click="toggle">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-7 w-7 cursor-pointer bg-gray-100 rounded-full text-gray-400 hover:bg-gray-200 p-1" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" title="Spravovať článok">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <!-- <slot name="button"></slot> -->
        </div>

        <div class="" v-if="open">
            <slot></slot>
        </div>


    </div>
</template>
<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],

    data: function () {
        return {
            open: false,
        };
    },

    methods: {
        toggle: function () {
            this.open = !this.open;
        },
    },
};
</script>
