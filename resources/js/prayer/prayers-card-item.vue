<template>
    <div class="px-4 py-2 text-base hover:bg-gray-100">
        <div @click="passToModalShow">
            <div class="flex justify-between w-full">
                <div class="font-semibold" v-if="prayer.title">
                    {{ prayer.title }}
                </div>
                <div class="font-semibold" v-else>žiadam o modlitbu</div>

                <!-- <favorites-count :prayer="prayer"></favorites-count> -->
            </div>

            <div class="flex">
                <div class="w-full">
                    <div class="text-sm">
                        {{ prayer.body.slice(0, 80) }} ...
                    </div>

                    <div class="flex mb-2 justify-between">
                        <div class="flex items-center">
                            <svg
                                class="h-4 w-4 mr-1 mt-1 text-gray-400 fill-current"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                v-if="prayer.user_name"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            <span
                                class="font-semibold mr-1 whitespace-nowrap"
                                >{{ prayer.user_name }}</span
                            >
                        </div>

                        <div
                            class="text-xs bg-blue-200 border-blue-600 border-1 text-gray-700 rounded-md px-2 pt-1 "
                        >
                            {{ prayer.created_at_humans }}
                        </div>
                        <!-- <span class="text-xs flex items-center  flex-shrink-0">
                            <svg
                                class="h-4 w-4 mr-2 text-gray-400 fill-current"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            {{ prayer.created_at | dateTime }} hod.
                        </span> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import modalShowPrayer from "../prayer/ModalShowPrayer";
import favoritesCount from "./components/favoritesCount";
import { bus } from "../app";
import { filterMixin } from "../mixins/filtersMixin";

export default {
    props: ["prayer"],
    mixins: [filterMixin],
    components: { modalShowPrayer, favoritesCount },

    data() {
        return {
            showModal: false
        };
    },
    methods: {
        passToModalShow() {
            bus.$emit("passToModalPrayer", this.prayer);
        }
    }
};
</script>
