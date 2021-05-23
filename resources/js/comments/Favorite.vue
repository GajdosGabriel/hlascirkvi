<template>
    <div>
        <!--<a href="{{ route('favorites.comments', [$comment->id]) }}">-->
        <!--@if($comment->isFavorited())-->
        <!--{{ $comment->Favorites_count }} <i style="color: #c60000" class="fas fa-heart fa-lg" title="Označili ste komentár"></i>-->
        <!--@else-->
<!--        <i @click="store(reply)"  class="fas fa-heart fa-lg" title="Hlasovať za komentár"></i>-->
        <div @click="store(reply)" >
            <svg class="h-5 w-5 cursor-pointer hover:text-red-500" @click="store(reply)" :class="replyClass" title="Hlasovať za komentár" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </div>

        <!--@endif-->
        <!--</a>-->

    </div>
</template>

<script>
    import {bus} from '../app';
    export default {
        props: ['reply'],
        data: function() {
            return {
                replyCount: this.reply.favoritesCount,
                replyisFavorited: this.reply.isFavorited

            }
        },

        computed: {

            signedIn: function() {
                return window.App.signedIn;
            },

            replyManager: function() {
                if(this.reply.favoritesCount > 0) {
                   return ['bg-red-700'];
                }
            },

            replyClass: function() {
                if(this.replyisFavorited) {
                    return ['bg-red-700 text-white rounded-full h-7 w-7 p-1'];
                }
            },

            replyCounter: function() {
                return ['fas fa-heart fa-lg fa-disabled'];
            }

        },

        methods: {
            store: function(reply) {
                if(! this.signedIn) {
                    return alert('Najprv sa prihláste!')
                };
                axios.put('/favorites/' + this.reply.id, { model: 'Comment', model_id: this.reply.id } );
                this.replyisFavorited = true;
                bus.$emit('flash', {body:'Pridaný hlas komentáru.'});
            }
        }
    }
</script>
<style>
    .fa-disabled {
        opacity: .6;
        pointer-events: none;
        color: red;
    }

    .fa-active {
        color: red;
    }
</style>
