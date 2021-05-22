<template>
    <div style="max-height: 53rem;overflow: auto;">

        <div class="">
            <h4 @click="showForm" class="mb-4 font-semibold mt-2">Komentáre <i class="far fa-comment-dots"></i> <span style="font-size: 70%; cursor: pointer">pridať nový</span></h4>

            <div v-for="reply in post.comments" :key="reply.id"  class="p-3">
               <reply :data="reply" @deleted="remove(reply.id)"></reply>
            </div>

            <div v-if="show">
                <new-reply  :post="post" @newComment="addNewComment"></new-reply>
            </div>

        </div>

    </div>

</template>

<script>
    import {bus} from '../app';
    import Reply from './Reply.vue';
    import NewReply from './NewReply.vue';
    export default {
        props: ['post'],
        components: {Reply, NewReply},
        data: function() {
            return {
                show: false
            }
        },

        computed: {

            signedIn: function() {
                return window.App.signedIn;
            },
            countComments: function() {
                return this.items.length + ' Pozrieť diskusiu'
            }
        },

        methods: {
            toggle: function() {
              this.modal = ! this.modal;
            },

            showForm: function() {
                this.show = ! this.show;
            },
            remove: function(id) {
                this.data.splice(id, 1);
                bus.$emit('flash', {body:'Komentár je zmazaný', type: 'danger'});
            },

            addNewComment: function(reply){
                this.items.push(reply);

                bus.$emit('flash', {body:'Komentár je pridaný!'});
            }

        }
    }
</script>
