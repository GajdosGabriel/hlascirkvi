<template>
    <div :class="{ 'fixed inset-0 bg-gray-600': open }">
        <div
            v-if="open"
            @click="showModal"
            class="absolute right-0 border-gray-200 border-2 bg-gray-700 hover:bg-gray-500 rounded px-4 py-2 text-white font-semibold text-2xl cursor-pointer "
        >
            Zrušiť x
        </div>

        <img
            class="rounded cursor-pointer"
            alt="image.title "
            :class="{ 'h-full': open }"
            :src="image.OriginalImageUrl"
            @click="showModal"
        />
    </div>
</template>

<script>
import { createdMixin } from "../mixins/createdMixin";
export default {
    mixins: [createdMixin],
    props: ["image"],
    data() {
        return {
            open: false
        };
    },
    methods: {
        showModal() {
            this.open = !this.open;
        }
    }
};
</script>
