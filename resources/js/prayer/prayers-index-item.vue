<template>
    <div class="px-6 py-2 border-2 border-gray-500 my-6 rounded-md shadow-lg">
        <div @click="passToModalShow">
            <div class="flex flex-col mb-4">
                <div class="flex justify-between">
                    <div>
                        <span class="font-semibold">{{ prayer.user_name }} </span>
                        žiada o modlitbu
                        <i class="fas fa-praying-hands" title="modlitbu"></i>
                    </div>
                    <!-- Nav menu-->
                    <div class="relative" v-if="authUser && authUser.id == prayer.user_id">
                        <div class="h-7 w-7 bg-gray-200 rounded-full" @click.stop="toggle">
                            <svg class="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <!-- Nav Drop Down menu-->
                        <div v-if="openDropDown" class="absolute right-0 bg-white border-2 rounded-lg border-gray-300 flex flex-col">
                            <span class="hover:bg-gray-300 p-2" @click.stop="passToModalEdit">Upraviť</span>
                            <span class="hover:bg-gray-300 p-2" @click.stop="prayerDestroy">Zmazať</span>
                        </div>
                    </div>

                </div>


                <span class="text-base flex items-center">
                   <svg class="h-5 w-5 mr-1 text-gray-500 fill-current" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"/>
                    </svg>
                   dňa: {{ prayer.created_at | dateTime }} hod.
               </span>
            </div>


            <div class="flex">
                <img :src="'images/prayed_hand.png'" class="h-10 mr-3 md:h-20 md:mr-10">
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
    import Axios from 'axios';

    export default {
        props: ['prayer'],
        components: {modalShowPrayer},

        data() {
            return {
                openDropDown: false,
                authUser: window.App.user
            }
        },

        methods: {
            passToModalShow() {
                bus.$emit('passToModalPrayer', this.prayer);
            },

            passToModalEdit() {
                bus.$emit('passToModalEdit', this.prayer);
            },

            toggle() {
                this.openDropDown = !this.openDropDown
            },

            prayerDestroy(){
                Axios.delete('/modlitby/' + this.prayer.id).then(
                    () => {
                        this.toggle();
                        window.location.reload()
                    }
                )
            }
        },

        created() {
            let self = this;
            window.addEventListener('click', function (e) {
                // close dropdown when clicked outside
                if (!self.$el.contains(e.target)) {
                    self.openDropDown = false
                }
            });
        },

        filters: {
            dateTime: function (value) {
                return moment(value).format('D.M.Y, H:mm');
            }
        }

    }
</script>
