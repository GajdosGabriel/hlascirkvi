<template>
    <div>
        <!--<a href="{{ route('favorites.comments', [$comment->id]) }}">-->
        <!--@if($comment->isFavorited())-->
        <!--{{ $comment->Favorites_count }} <i style="color: #c60000" class="fas fa-heart fa-lg" title="Označili ste komentár"></i>-->
        <!--@else-->
        <i @click="store(reply)" :class="replyClass" class="fas fa-heart fa-lg" title="Hlasovať za komentár"></i>

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
                   return ['fa-active'];
                }
            },

            replyClass: function() {
                if(this.replyisFavorited) {
                    return ['fa-disabled'];
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
                axios.get('/comment/' + this.reply.id + '/favorites/comment');
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