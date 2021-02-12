<template>
    <div>

        <!-- Modal -->
            <transition name="slide-fade">
                <div  v-if="showLoginForm" class="modal">
                <login-0 @closeModal="showLoginForm = false" style="margin-top:3rem"></login-0>
                </div>
            </transition>

        <div  class="page-header level">
            <div class="Media">

                <!-- check if is person-->
                <div v-if="organization.person == 1">
                    <img v-if="organization.avatar !== null" class="Media-figure img-avatar" :src="this.domain + 'storage/users/' + organization.id + '/' + organization.avatar">
                    <img v-else class="Media-figure" style="max-width: 6rem" :src="this.domain + 'images/avatar.png'">
                </div>

                <!--Non Person picture-->
                <i v-else style="font-size: 6rem; color: silver; float: left; margin-right: 1.1rem" class="far fa-image"></i>

                <div class="Media-body">
                    <div style="display: inline-block">
                        <h2 v-text="organization.title"></h2>

                        <div v-if="organization.person == 1" @click="toggle" class="navButton" :class="{'activeButton' :showDescription }">Autor</div>
                        <div v-else @click="toggle" class="navButton" :class="{'activeButton' :showDescription }" >Profil</div>
                        <!--<help-us></help-us>-->
                        <a v-if="isVideoPage" href="#" @click="openModal()" class="navButton">Zapojiť sa</a>
                    </div>


                    <transition name="fade">
                        <div v-if="showDescription">
                            <!--<textarea v-if="textform" name="" id=""style="width: 50vw" rows="7"></textarea>-->
                            <div v-if="organization.description == null">Profil je nevyplnený.</div>
                            {{ organization.description }} <br>

                            <div @click="toggle" style="cursor: pointer; font-size: 75%; float: left; margin-right: 1rem">Zavrieť X</div>

                        </div>
                    </transition>

                </div>

            </div>

        <div>
            <!-- Button i-Memeber-->
            <div v-show="buttonStatus" v-html="button" @click="subscribe" title="Budete dostávať nové príspevky!" :class="classButton" class="button"></div>

        </div>
    </div>


    </div>

</template>

<script>
    import {bus} from '../app';
    export default {
        props: ['organization', 'post'],
        data: function() {
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
            signedIn: function() {
                return window.App.signedIn;
            },

            isVideoPage: function() {
             return this.post.video_id  !== null
            },

            button: function() {
                if(this.favorited){
                    return this.buttonText = 'Ste&nbsp;členom <i style="color: rgba(27, 136, 201, 0.67)" class="fas fa-check-circle"></i>'
                }
                return this.buttonText = 'Sledovať kanál<br>' + this.organization.title
            },

            classButton: function() {
                return [ this.favorited ? 'buttonB' : 'buttonA' ]
            }

        },

        methods: {
            toggle: function() {
                this.showDescription = ! this.showDescription;
            },
            toggleLogin: function() {
                this.showLoginForm = ! this.showLoginForm;
            },

            toggleFavorited: function() {
                this.favorited = !this.favorited;
            },

            subscribe: function() {
                if (! this.signedIn) {
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

            messageNotification: function(){
                if(this.favorited) {
                    bus.$emit('flash', {body:'Ste prihlásený!'});
                } else {
                    bus.$emit('flash', {body:'Ste odhlásený!'});
                }
            },

            openModal: function() {
                bus.$emit('openModal');
            }
        }
    }
</script>

<style>
    .slide-fade-enter-active, .slide-fade-leave-to {
        transition: opacity .5s;
    }

    .slide-fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }
    .navButton {
        float: left;
        margin-right: 1rem;
        cursor: pointer;
        padding: .7rem .9rem;
        border-radius: .6rem;
    }
    .img-avatar {
        max-width: 6rem;
        border-radius: .5rem;
        border: 1px solid black
    }

    .button {
        cursor:pointer;
        float: right;
        font-size: 120%;
        padding: 0rem 1.8rem;
        border-radius: .5rem;
        text-align: center;
    }

    .activeButton {
        background: rgba(59,89,153 ,1);
        color: white;
    }

    .buttonA {
        background: red;
        color: whitesmoke;

    }
    .buttonB {
        background: silver;
        color: #535353;
    }


    .Media {
        display: flex;
        align-items: flex-start;
    }

    .Media-figure {
        margin-right: 1em;
    }

    .Media-body {
        flex: 1;
    }


</style>
