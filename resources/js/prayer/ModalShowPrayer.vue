<template>
    <div>
        <!-- Modal -->
        <div v-if="prayer" class="modal" id="modal-name">
            <div class="modal-sandbox"></div>
            <div class="modal-box">
                <div class="modal-header flex justify-between">
                    <h4>Modlitbová prosba</h4>
                    <div @click="toggle" class="close-modal">&#10006;</div>
                </div>

                <div class="modal-body" style="font-size: 15px">

                    <span style="font-weight: 600;">
                        {{ prayer.user_name }}
                        </span>

                    žiada o

                    <i class="fas fa-praying-hands"></i>


                    <div class="mb-3 mt-6 font-semibold" v-if="prayer.title">{{ prayer.title }}</div>

                    <p class="mb-8">{{ prayer.body }}</p>

                    <div style="margin-bottom: .5rem" class="date">
                        <span style="font-weight: bold">Modlitba je stále aktuálna </span>

                        Zverejnená dňa: {{ prayer.created_at | dateTime }}
                    </div>

                </div>

                <div class="bg-gray-600 p-10" >
                    <button @click="toggle" class="text-gray-200 border-2 border-white hover:bg-gray-500 rounded-md">Zavrieť</button>
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
