<template>
    <div class="">
        <div class="" v-if="open">
            <slot></slot>
        </div>

        <div @click="toggle" title="" v-if="!open">
            <slot name="button"></slot>
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
