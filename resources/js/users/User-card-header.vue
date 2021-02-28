<template>

    <div class="bg-blue-800 p-2 text-gray-100">
            <div v-html="user.title"></div>

            <!--<transition name="fade">-->
                <!--<i v-if="!annotation" style="cursor: pointer" :class="handIcon" title="Spriateľstvo s autorom" @click="makeFriend"> {{ favoritedCount }} ľudí</i>-->
            <!--</transition>-->

            <transition name="fade">
                <span style="font-size: 80%" v-if="annotation">
                    <a href="/auth/facebook">
                        Najprv sa registrujte cez Facebook
                    </a>
                </span>
            </transition>
    </div>
</template>

<script>
    import {bus} from '../app';
    export default {
        props: ['user'],
        data: function() {
            return {
                isFavorite: this.user.isFavorited,
                annotation: false,
                favoritedCount: this.user.postsCount *2 + this.user.FavoritesCount
            }
        },

        computed: {
            handIcon: function () {
                    return ['fas fa-handshake grow', this.isFavorite ? 'color-ddd' : ''];
            }
        },

        methods: {

            makeFriend: function() {

                if(window.App.signedIn == false) {
                    this.annotation = true;
                    return console.log('neprihlásený');
                }
                axios.get('/users/' + this.user.id + '/favorites/user');

                if(this.isFavorite) {
                    this.favoritedCount--;
                } else {
                    this.favoritedCount++;
                }


                this.toggle();

                this.notificationAnnonce();
            },

            toggle: function() {
                this.isFavorite = ! this.isFavorite;
            },

            notificationAnnonce: function() {
                return this.isFavorite ? bus.$emit('flash', {body:'Ste priatelia.'}) : bus.$emit('flash', {body:'Priateľstvo zružené.'});
            }


        }
    }
</script>

<style>

    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.25s;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }

    .color-ddd{
        color: #ffff55;
    }
</style>
