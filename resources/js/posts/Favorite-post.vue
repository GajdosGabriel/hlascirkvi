<template>
    <div class="grow cursor-pointer">

           <div class="mt-1 text-sm whitespace-nowrap" style="border-radius: .3rem;background-color: rgb(173, 80, 146);color: whitesmoke; padding: 0rem .4rem" @click="pressRecomendedButton" :class="isFavorited" v-text="recommended"></div>

        <!-- Modal -->
        <transition name="slide-fade">
               <div v-if="showLoginForm" class="absolute z-10 bg-white border-2 border-gray-400 p-2 pb-5 rounded-md text-center">
                <p class="pb-4 text-sm">Prihláste sa, alebo zaregistrujte.</p>
                <a :href="'/login'" class="btn btn-primary w-full mb-2">Pokračovať</a>
            </div>
        </transition>

    </div>
</template>

<script>
    import {bus} from '../app';
    export default {
        props: ['post'],
        data: function() {
            return {
                favoriteCount: this.post.favoritesCount,
                isFavorite: this.post.isFavorited,
                recommended: '',
                showLoginForm: false
            }
        },

        computed: {
            signedIn: function() {
                return window.App.signedIn;
            },

            isFavorited: function() {
                if(this.isFavorite) {
                    bus.$emit('flash', {body:'Príspevok je doporučaný!'});
                    this.recommended = 'Odporúčali ste ' + this.favoriteCount;
                    return {'favorited' : this.isFavorite };
                }

                if(! this.isFavorite) {

                    if(this.favoriteCount > 0) {
                        this.recommended = 'Odporúčať príspevok ' + this.favoriteCount;
                        }
                    bus.$emit('flash', {body:'Odporúčanie zrušené!'});
                    this.recommended = 'Odporúčať príspevok ';
                    return {'favorited' : this.isFavorite };
                }
            },

            notFavorited: function() {
                this.recommended = 'Odporúčať príspevok';
                return ['fas fa-thumbs-up fa-lg grow', this.isFavorite ? 'favorited' : ''];
            }
        },

        methods: {
            pressRecomendedButton: function() {
                if (! this.signedIn) {
                  return  this.showLoginForm = true;
                }
                this.toggle();
                this.save();
            },
            toggle: function() {
                this.isFavorite = ! this.isFavorite;

                this.favoriteCounter();
            },

            save: function() {
                axios.put('/favorites/' + this.post.id, { model:'Post', model_id: this.post.id } );
            },

            favoriteCounter: function() {
                return this.isFavorite ?  this.favoriteCount++ :  this.favoriteCount--;
            }
        }
    }
</script>

<style>

    .favorited {
        color: #989898;
    }

    /*.grow { transition: all .2s ease-in-out; }*/
    /*.grow:hover { transform: scale(1.3); }*/

    .slide-fade-enter, .slide-fade-leave-to
        /* .slide-fade-leave-active below version 2.1.8 */ {
        /*transform: translateX(10px);*/
        opacity: 0;
    }

</style>
