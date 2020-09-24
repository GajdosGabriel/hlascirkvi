<template>

    <div>
        <h4>Hľadám na akciu:</h4>

        <div v-for="(comment, index) in comments">
            <event-comment :data="comment" @destroy="remove(index)"></event-comment>
        </div>

        <event-form  @created="newcomment"></event-form>

    </div>

</template>

<script>
    import {bus} from '../app';
    import EventComment from './Event-comment.vue';
    import EventForm from './Event-form-look.vue';
    export default {
        props: ['commentsoffer'],
        components: {EventComment, EventForm},
        data: function() {
            return {
                errors: [],
                comments: this.commentsoffer,
                showDescription: false,
                body: '',
                annotation: false,
                showForm: false,
                items: this.commentsoffer
            }
        },
        methods: {
            toggle: function() {
                this.showDescription = ! this.showDescription;
            },

            newcomment: function(newcomment){
                this.comments.push(newcomment);
                bus.$emit('flash', {body:'Komentár bol pridaný.'});

            },

            remove: function(index) {
                this.items.splice(index, 1);

            },

            toggleMessage: function() {
                this.showForm = ! this.showForm;
                this.showDescription = false;
            },

            checkForm: function (e) {
                if (this.body.length>3) {
                    return true;
                }

                this.errors = [];

                if (!this.body) {
                    this.errors.push('Text sa vyžaduje.');
                }
                e.preventDefault();
            },

            sendmessage: function() {

                this.checkForm();

                axios.post('/store/message', {
                    body: this.body,
                    requested_organization: this.user.id
                });
                this. showForm = false;
                this.annotation = true;
                this.hide();
            },

            hide: function() {
                setTimeout(() => {
                    this.annotation = false
                }, 3000);
            }
        }
    }
</script>

<style>
    .left{ float: right}

    .fade-enter-active {
        transition: opacity .5s;
    }

    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }

    .color-red{
        color: red;
    }
</style>
