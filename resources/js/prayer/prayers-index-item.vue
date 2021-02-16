<template>
    <div class="px-6 py-2 border-2 border-gray-500 my-6 rounded-md shadow-lg">
        <div @click="passToModalShow">
            <div class="flex justify-between mb-4">
                <div>
                    <span class="font-semibold">{{ prayer.user_name }} </span>
                    žiada o modlitbu
                    <i class="fas fa-praying-hands" title="modlitbu"></i>
                </div>
               <span>dňa: {{ prayer.created_at | dateTime }} hod.</span>
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
            passToModalShow(){
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
