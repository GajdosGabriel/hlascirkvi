<template>
    <div
        class="px-6 py-2 border-2 border-gray-400 my-6 rounded-md shadow-lg"
        :class="isFulfilledAt"
    >
        <!-- If prayer is fulfilled -->
        <div
            v-if="prayer.fulfilled_at"
            class="p-2 border-green-900 border-2 -mt-8 text-2xl bg-gray-50 rounded-md mb-2 text-center"
        >
            Modlitba bola vypočutá
        </div>

        <div @click="passToModalShow">
            <div class="flex flex-col mb-4">
                <div class="flex justify-between">
                    <div class="flex">
                        <span class="font-semibold mr-2"
                            >{{ prayer.user_name }}
                        </span>

                        <span class="text-sm flex items-center">
                            <svg
                                class="h-4 w-4 mr-1 text-gray-500 fill-current"
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
                            dňa: {{ prayer.created_at | dateTime }} hod.
                        </span>
                    </div>
                    <!-- Nav menu-->
                    <div
                        class="relative"
                        v-if="authUser && authUser.id == prayer.user_id"
                    >
                        <div
                            class="h-7 w-7 bg-gray-200 rounded-full"
                            @click.stop="toggle"
                        >
                            <svg
                                class=""
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <!-- Nav Drop Down menu-->
                        <div
                            v-if="open"
                            class="absolute right-0 bg-white border-2 rounded-lg border-gray-300 flex flex-col"
                        >
                            <span
                                class="hover:bg-gray-300 p-2"
                                @click.stop="passToModalEdit"
                                >Upraviť</span
                            >
                            <span
                                class="hover:bg-gray-300 p-2"
                                @click.stop="prayerDestroy"
                                >Zmazať</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:flex justify-between">
                <div class="flex w-full">
                    <img
                        :src="'/images/prayed_hand.png'"
                        class="h-10 mr-3 md:h-20 md:mr-10"
                    />
                    <div class="w-full">
                        <div class="flex justify-between">
                            <div class="font-semibold" v-if="prayer.title">
                                {{ prayer.title }}
                            </div>
                            <div class="font-semibold" v-else>
                                Prosba o modlitbu
                            </div>
                            <favorites-count
                                v-if="!prayer.fulfilled_at"
                                :prayer="prayer"
                            ></favorites-count>
                        </div>

                        <p style="margin-bottom: 0.4rem">{{ prayer.body }}</p>
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
import Axios from "axios";
import { filterMixin } from "../mixins/filtersMixin";
import { createdMixin } from "../mixins/createdMixin";

export default {
    props: ["prayer"],
    mixins: [filterMixin, createdMixin],
    components: { modalShowPrayer, favoritesCount },

    data() {
        return {
            open: false,
            authUser: window.App.user,
        };
    },

    computed: {
        isFulfilledAt: function () {
            return [
                this.prayer.fulfilled_at ? "text-green-700 bg-green-200" : "",
            ];
        },
    },
    methods: {
        passToModalShow() {
            if (this.prayer.fulfilled_at) return;
            bus.$emit("passToModalPrayer", this.prayer);
        },

        passToModalEdit() {
            bus.$emit("passToModalEdit", this.prayer);
        },

        toggle() {
            this.open = !this.open;
        },

        prayerDestroy() {
            if (!window.confirm("Skutočne chcete zmazať položku?")) {
                return;
            }
            Axios.delete("/modlitby/" + this.prayer.id).then(() => {
                this.toggle();
                window.location.reload();
            });
        },
    },
};
</script>
