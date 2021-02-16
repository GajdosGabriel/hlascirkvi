<template>
    <div class="px-6 py-2">
        <div class="relative" @click="passToModalShow">

                        <span class="font-semibold">{{ prayer.user_name }} </span>

            žiada o modlitbu

            <i class="fas fa-praying-hands" title="modlitbu"></i>

            <div class="font-semibold" v-if="prayer.title">{{ prayer.title }}</div>
            <div style="margin-bottom: .4rem">{{ prayer.body }}</div>
            <div class="absolute bottom-0 right-0 text-gray-400">dňa: {{ prayer.created_at | dateTime }} hod.</div>
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
