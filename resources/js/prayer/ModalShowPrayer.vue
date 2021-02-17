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


                    <div class="flex">
                        <img :src="'images/prayed_hand.png'" class="h-20 mr-10">
                        <div>
                            <div class="font-semibold" v-if="prayer.title">{{ prayer.title }}</div>
                            <p style="margin-bottom: .4rem">{{ prayer.body }}</p>
                        </div>
                    </div>

                    <div class="flex mt-5">
                       <span class="mr-3">Modlitba je stále aktuálna</span>
                        <span class="text-base flex items-center">
                   <svg class="h-6 w-6 mr-3 text-gray-500 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"/>
                    </svg>
                   dňa: {{ prayer.created_at | dateTime }} hod.
               </span>
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
