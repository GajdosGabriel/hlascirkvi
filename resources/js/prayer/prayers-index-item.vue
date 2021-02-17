<template>
    <div class="px-6 py-2 border-2 border-gray-500 my-6 rounded-md shadow-lg">
        <div @click="passToModalShow">
            <div class="flex flex-col mb-4">
                <div>
                    <span class="font-semibold">{{ prayer.user_name }} </span>
                    žiada o modlitbu
                    <i class="fas fa-praying-hands" title="modlitbu"></i>
                </div>
                <span class="text-base flex items-center">
                   <svg class="h-6 w-6 mr-3 text-gray-500 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"/>
                    </svg>
                   dňa: {{ prayer.created_at | dateTime }} hod.
               </span>
            </div>


            <div class="flex">
                <img :src="'images/prayed_hand.png'" class="h-20 mr-10">
                <div>
                    <div class="font-semibold" v-if="prayer.title">{{ prayer.title }}</div>
                    <p style="margin-bottom: .4rem">{{ prayer.body }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from "moment";
    import modalShowPrayer from '../prayer/ModalShowPrayer';
    import {bus} from "../app";

    export default {
        props: ['prayer'],
        components: {modalShowPrayer},

        methods: {
            passToModalShow() {
                bus.$emit('passToModalPrayer', this.prayer);
            }
        },

        filters: {
            dateTime: function (value) {
                return moment(value).format('D.M.Y, H:mm');
            }
        }

    }
</script>
