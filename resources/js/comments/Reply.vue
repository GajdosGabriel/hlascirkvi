<template>
    <div class="comment">
        <div class="level">
            <strong v-text="getShortName"></strong>
            <favorite :reply="data"></favorite>
        </div>

        <div v-if="!editComment" v-text="cakanaschvalenie" :class="redText"></div>

        <div v-if="editComment">
        <textarea v-model="body" style="width: 100%" rows="3" placeholder="Pridajte nový komentár ..." required></textarea>
        </div>

        <div class="comment__footer level" v-if="canUpdate">
            <a href="#" @click.prevent="editComment=true">Upraviť</a>
            <a href="#" @click.prevent="destroy()">Zmazať</a>

            <a href="#" @click.prevent="updateComment" v-if="editComment" style="float: right" class="btn btn-small">Uložiť</a>
        </div>
    </div>

</template>

<script>
    import Favorite from './Favorite.vue';
    export default {
        props: ['data'],
        components: {Favorite},
        data: function() {
            return {
                editComment: false
            }
        },

        computed: {
            signedIn: function() {
                return window.App.signedIn;
            },

            canUpdate: function() {
                return this.authorize(user => this.data.user_id == window.App.user.id);
            },

            getShortName: function() {
               return this.data.user.first_name + ' ' +  this.data.user.last_name.charAt(0) +'.'
            },

            cakanaschvalenie: function() {
                if(this.data.deleted_at != null ) {
                    return 'Váš komentár čaká na schválenie.'
                }
                return this.data.body
            },

            redText: function() {
                return this.data.deleted_at != null ? 'redText' : '';
            }
        },

        methods: {
            destroy: function() {
                axios.get('/comment/' + this.data.id + '/delete/comment');

                $(this.$el).fadeOut(300, () => {
                    this.$emit('deleted', this.data.id);
            })
            },

            updateComment: function() {
                axios.put('/comment/' + this.data.id + '/update/comment' , {body: this.body});
                this.editComment = false;
            }
        }
    };;
</script>

<style>
    .redText {
        color: red;
    }
</style>