<template>
    <!-- This example requires Tailwind CSS v2.0+ -->
    <div v-if="prayer" class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                role="dialog" aria-modal="true" aria-labelledby="modal-headline">

                <div class="bg-blue-900 text-gray-300 p-4">
                    <!--  Header  -->
                    <div class="flex justify-between">
                        <h3 class="text-lg leading-6 font-medium" id="modal-headline">
                            Modlitbová prosba
                        </h3>

                        <div @click="toggle" class="close-modal cursor-pointer">&#10006;</div>
                    </div>
                </div>

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">

                        <img :src="'images/prayed_hand.png'" class="h-20">

                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">

                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    <span class="font-semibold mr-1">{{ prayer.user_name }}</span>žiada o modlitbu
                                </p>
                            </div>

                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mr-2 font-semibold">
                                    {{ prayer.title }}
                                </p>
                            </div>

                            <div class="mt-2">
                                <p class="text-gray-500 text-lg">
                                    {{ prayer.body }}
                                </p>
                            </div>

                            <div class="mt-3">
                                <p class="text-sm text-gray-500 flex">
                                    <span class="mr-3 font-semibold">Modlitba je stále aktuálna</span>
                                    <svg class="h-4 w-4 mr-1 text-gray-500 fill-current"
                                         xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                    dňa: {{ prayer.created_at | dateTime }} hod.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="toggle"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Zavrieť
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import {bus} from '../app';
    import Form from "../messenger/form";
    import Axios from 'axios';
    import moment from "moment";


    export default {
        data: function () {
            return {
                prayer: '',
            }
        },

        created: function () {
            bus.$on('passToModalPrayer', (prayer) => {
                this.prayer = prayer
            });
        },

        methods: {
            toggle: function () {
                this.prayer = '';
            }

        },
        filters: {
            dateTime: function (value) {
                return moment(value).format('D.M.Y, h:mm');
            }
        }

    }
</script>
