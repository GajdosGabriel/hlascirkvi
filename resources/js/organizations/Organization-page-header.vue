<template>

    <div @click="closeLoginInfo" class="md:flex justify-between text-gray-700 mb-6 border-b-2 border-gray-400">
        <div class="flex">

            <!-- check if is person-->
            <div class="mr-4 mb-4">
                <img v-if="organization.avatar" class="rounded-full w-16"
                     :src="this.domain + 'storage/organizations/' + organization.id + '/' + organization.avatar">

                <div v-else class="h-12 w-12 bg-gray-300 rounded-full flex items-center justify-center font-semibold text-2xl">
                    {{ organization.initialName }}
                </div>
            </div>


            <div class="">
                <h2 class="text-2xl font-semibold" v-text="organization.title"></h2>

                <div class="flex">
                    <a href="#" v-if="organization.person == 1"
                    @click="toggle"
                            class="hover:text-blue-500 hover:underline"
                            :class="{'bg-blue-500 text-gray-200 rounded-sm px-2' :showDescription }">
                            Autor
                    </a>
                    <button v-else @click="toggle" class="btn-small btn-default"
                            :class="{'bg-blue-300' :showDescription }">
                        Profil
                    </button>
                </div>


                <transition name="fade">
                    <div v-if="showDescription">
                        <!--<textarea v-if="textform" name="" id=""style="width: 50vw" rows="7"></textarea>-->
                        <div v-if="organization.description == null">Profil je nevyplnený.</div>
                        {{ organization.description }} <br>

                        <div @click="toggle"
                             style="cursor: pointer; font-size: 75%; float: left; margin-right: 1rem">Zavrieť X
                        </div>

                    </div>
                </transition>
            </div>
        </div>

        <div class="relative">
            <!-- Button i-Memeber-->
            <div v-show="buttonStatus" v-html="button" @click.stop="subscribe" title="Budete dostávať nové príspevky!"
                 :class="classButton" class="p-2 rounded-md cursor-pointer flex justify-center hover:bg-red-700">

            </div>
            <!-- Login Form-->
            <div v-if="open" class="absolute z-10 bg-white border-2 border-gray-400 p-2 rounded-md text-center">
                <p class="pb-4">Prihláste sa, alebo zaregistrujte.</p>
                <a :href="'/login'" class="btn btn-primary w-full mb-2">Pokračovať</a>
            </div>
        </div>

        <!-- <modal></modal> -->
    </div>

</template>

<script>
    import {bus} from '../app';
    import modal from '../organizations/Organization-page-modal';
    import { createdMixin } from "../mixins/createdMixin";

    export default {
        props: ['organization', 'post'],
        components: {modal},
        mixins:[createdMixin],
        data: function () {
            return {
                domain: window.App.baseUrl,
                showDescription: false,
                textform: false,

                favorited: this.organization.isFavorited,
                buttonText: '',
                buttonStatus: true,
                registrationLink: false,
                open: false
            }
        },
        computed: {
            signedIn: function () {
                return window.App.signedIn;
            },

            isVideoPage: function () {
                return this.post.video_id !== null
            },

            button: function () {
                if (this.favorited) {
                    return this.buttonText = 'Sledujete kanál'
                }
                return this.buttonText = 'Sledovať kanál ' + this.organization.title
            },

            classButton: function () {
                return [this.favorited ? 'bg-gray-300' : 'bg-red-600 text-white']
            }

        },

        methods: {
            toggle: function () {
                this.showDescription = !this.showDescription;
            },
            toggleLogin: function () {
                // alert('Vytvorte si účet alebo prihláste sa.');
                this.open = !this.open;
            },

            closeLoginInfo(){
              if (this.open == true) {
                  this.open = false
              }
            },
            toggleFavorited: function () {
                this.favorited = !this.favorited;
            },

            subscribe: function () {
                if (!this.signedIn) {
                    return this.toggleLogin();
                }
                axios.get('/user/' + this.organization.id + '/favorites/subscribe')
                    .then((response) => {
                        this.toggleFavorited();
                        this.messageNotification();
                    });

//                this.messageNotification();
//                this.toggleFavorited();
            },

            messageNotification: function () {
                if (this.favorited) {
                    bus.$emit('flash', {body: 'Ste prihlásený!'});
                } else {
                    bus.$emit('flash', {body: 'Ste odhlásený!'});
                }
            },

            // openModal: function () {
            //     bus.$emit('openModalHelpUs');
            // }
        }
    }
</script>

<style>
    .slide-fade-enter-active, .slide-fade-leave-to {
        transition: opacity .5s;
    }

    .slide-fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
    {
        opacity: 0;
    }

</style>
