<template>
    <div :class="{ 'fixed inset-0 bg-gray-600': open }">
        <div
            v-if="open"
            @click="showModal"
            class="absolute right-0 border-gray-200 border-2 bg-gray-700 hover:bg-gray-500 rounded px-4 py-2 text-white font-semibold text-2xl cursor-pointer "
        >
            Zrušiť x
        </div>

        <transition name="fade">
            <img
                v-if="hideImage"
                class="rounded cursor-pointer"
                alt="image.title "
                :class="{ 'h-full': open }"
                :src="'/storage/' + image.url"
                @click="showModal"
            />
        </transition>
        <div
            @click="deleteImage"
            class="cursor-pointer hover:text-red-500"
            v-if="hideImage"
        >
            zmazať
        </div>
    </div>
</template>

<script>
import { createdMixin } from "../mixins/createdMixin";
export default {
    mixins: [createdMixin],
    props: ["image"],
    data() {
        return {
            open: false,
            remove: false,
            hideImage: true
        };
    },
    methods: {
        showModal() {
            this.open = !this.open;
        },

        deleteImage: function() {
            axios.delete("/images/" + this.image.id),
                (this.hideImage = false);
        }
    }
};
</script>
