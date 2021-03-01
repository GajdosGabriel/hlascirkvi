<template>
    <div class="container mx-auto text-gray-700 border-2 border-gray-300 p-5">

        <div class="md:flex justify-between">
            <div class="flex">

                <!-- check if is person-->
                <div class="" v-if="organization.person == 1">
                    <img v-if="organization.avatar !== null" class="rounded-sm w-24 mr-6"
                         :src="this.domain + 'storage/users/' + organization.id + '/' + organization.avatar">
                    <img v-else class="Media-figure" style="max-width: 6rem" :src="this.domain + 'images/avatar.png'">
                </div>

                <!--Non Person picture-->
                <i v-else  style="font-size: 6rem; color: silver; float: left; margin-right: 1.1rem"
                   class="far fa-image"></i>

                <div class="flex justify-start flex-col justify-between">
                    <h2 class="text-2xl font-semibold" v-text="organization.title"></h2>

                    <div class="flex space-x-4 my-2">
                        <button v-if="organization.person == 1" @click="toggle" class="px-2 hover:border-gray-500 border-2 rounded-sm"
                             :class="{'bg-blue-700' :showDescription }">Autor
                        </button>
                        <button v-else @click="toggle" class="px-2 hover:border-gray-400 border-2 rounded-md" :class="{'bg-blue-700' :showDescription }">
                            Profil
                        </button>
                        <!--<help-us></help-us>-->
                        <button v-if="isVideoPage" @click="openModal" class="px-2 hover:border-gray-400 border-2 rounded-md whitespace-nowrap">Zapojiť sa</button>
                        <a :href="/user/ + organization.id + '/'+ organization.slug + '/posts' " class="px-2 hover:border-gray-400 border-2 rounded-md whitespace-nowrap">Všetky videa</a>
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

            <div>
                <!-- Button i-Memeber-->
                <div v-show="buttonStatus" v-html="button" @click="subscribe" title="Budete dostávať nové príspevky!"
                     :class="classButton" class="p-4 rounded-md cursor-pointer flex justify-center"></div>

            </div>
        </div>


    </div>

</template>

<script>
    import {bus} from '../app';

    export default {
        props: ['organization', 'post'],
        data: function () {
            return {
                domain: window.App.baseUrl,
                showDescription: false,
                textform: false,

                favorited: this.organization.isFavorited,
                buttonText: '',
                buttonStatus: true,
                registrationLink: false,
                showLoginForm: false
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
                return this.buttonText = 'Sledovať kanál<br>' + this.organization.title
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
                this.showLoginForm = !this.showLoginForm;
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

            openModal: function () {
                bus.$emit('openModal');
            }
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
