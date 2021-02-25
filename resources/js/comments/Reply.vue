<template>
    <div class="comment text-gray-600 mb-6 shadow-md border-2 border-gray-100 p-3 rounded-md">
        <div class="flex justify-between">
            <strong v-text="getShortName"></strong>
            <favorite :reply="data"></favorite>
        </div>

        <div v-if="!editComment" v-text="cakanaschvalenie" :class="redText"></div>

        <div v-if="editComment">
        <textarea class="w-full p-2 border-2 border-gray-400 rounded-md" v-model="body" rows="3" placeholder="Pridajte nový komentár ..." required></textarea>
        </div>

        <div class="flex justify-between mt-2" v-if="canUpdate">
            <button class="btn"  @click.prevent="destroy()">Zmazať</button>
            <button class="btn"  @click.prevent="editComment=true">Upraviť</button>

            <button class="btn" @click.prevent="updateComment" v-if="editComment" style="float: right">Uložiť</button>
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
