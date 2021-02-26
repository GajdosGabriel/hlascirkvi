<template>
    <div style="max-height: 53rem;overflow: auto;">

        <div class="">
            <h4 @click="showForm" class="mb-4">Komentáre <i class="far fa-comment-dots"></i> <span style="font-size: 70%; cursor: pointer">pridať nový</span></h4>

            <div v-for="(reply, index) in items" >
               <reply :data="reply" @deleted="remove(index)"></reply>
            </div>

            <div v-if="show">
                <new-reply  :post="data" @created="add"></new-reply>
            </div>

        </div>

    </div>

</template>

<script>
    import {bus} from '../app';
    import Reply from './Reply.vue';
    import NewReply from './NewReply.vue';
    export default {
        props: ['data'],
        components: {Reply, NewReply},
        data: function() {
            return {
                items: this.data,
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
            remove: function(index) {
                this.data.splice(index, 1);
                bus.$emit('flash', {body:'Komentár je zmazaný', type: 'danger'});
            },

            add: function(reply){
                this.items.push(reply);

                bus.$emit('flash', {body:'Komentár je pridaný!'});
            }

        }
    }
</script>
