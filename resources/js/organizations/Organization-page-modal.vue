<template>

    <div v-if="show" class="fixed z-10 inset-0 overflow-y-auto">
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
                            Pomôžte šíriť myšlienky Božieho slova! Pozrite ako to spraviť.
                        </h3>

                        <div @click="toggle" class="close-modal cursor-pointer">&#10006;</div>
                    </div>
                </div>

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="">

                        <div class="text-center sm:mt-0 sm:ml-4 sm:text-left">

                            <transition name="fade">
                                <div v-if="formOpen">
                                    <div v-if="formClose" class="form-group">
                                        <textarea v-model="body" class="form-control w-full" rows="2"
                                                  placeholder="Napíšte svoj telefón: zavoláme vám, alebo email: napíšeme Vám!"></textarea>
                                    </div>
                                    <div v-if="errors.length" style="color: red">Vyplnte text</div>
                                </div>
                            </transition>

                        </div>


                        <div class="mb-3">

                            <a v-if="!formOpen" class="btn bg-red-700 text-white rounded-md py-3 cursor-pointer"
                               @click="formOpen=true">Chcem pomôsť! <i class="fas fa-arrow-right"></i>
                            </a>
                            <a @click="sendmessage" v-if="formOpen"
                               class="btn bg-red-700 text-white rounded-md py-3 cursor-pointer">Odoslať
                                správu <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <transition name="fade">
                            <div v-if="annotation" style="background: brown; color: whitesmoke; padding: 1.7rem">
                                <h5>Správa bola odoslaná! Budeme Vás kontaktovať.</h5>
                            </div>
                        </transition>

                        <div class="mt-5">
                            <iframe width="100%" height="470px" src="https://www.youtube.com/embed/tTvyX9Nzzrw?rel=0"
                                    frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        </div>

                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="toggle"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Zrušiť
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {bus} from '../app';
    import Axios from 'axios';


    export default {

        data: function () {
            return {
                show: false,
                authUser: window.App.user,
                errors: [],
                annotation: false,
                formOpen: false,
                formClose: true,
                body: ''

            }
        },

        created: function () {
            bus.$on('openModalHelpUs', () => {
                this.show = true
            });

        },

        methods: {
            toggle: function () {
                this.show = !this.show;
            },

            sendmessage: function () {

                this.checkForm();

                Axios.post('/store/message', {body: this.body});
                this.formClose = false;
                this.annotation = true;
                this.hide();
            },

            hide: function () {
                setTimeout(() => {
                    this.annotation = false
                }, 3000);
            },

            checkForm: function (e) {
                if (this.body) {
                    return true;
                }

                this.errors = [];

                if (!this.body) {
                    this.errors.push('Text sa vyžaduje.');
                }
                e.preventDefault();
            }


        }


    }
</script>
