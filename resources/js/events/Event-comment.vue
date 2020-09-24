<template>

    <div  class="comment">
        <div class="level">
            <small v-text="getShortName"></small>
            <small v-if="canUpdate" @click="destroy"><a>zmazať</a></small>
            <small v-if="singnedIn" @click="toggle"><a>odpovedať</a></small>
            <small v-else><a href="/login">mám&nbsp;záujem</a></small>
        </div>
        <div v-text="data.body"></div>

        <transition name="fade">
            <div v-if="showMessenger">
                <div class="messenger">
                    <div class="form-group" style="padding: 0rem">
                        <textarea v-model="body" class="form" rows="3" placeholder="Text správy..." required></textarea>
                    </div>

                    <div v-if="errors.length" class="required">*** Vyplnte text</div>

                    <div class="level">
                        <small @click="toggle" style="color:black; cursor: pointer">Zavrieť X</small>
                        <button style="float: right" class="btn btn-small"  @click="sendmessage">Odoslať</button>
                    </div>
                </div>
            </div>
        </transition>
    </div>

</template>

<script>

    import {bus} from '../app';

    export default {
        props: ['data'],
        data: function() {
            return {
                errors: [],
                showMessenger: false,
                body:'',
                can: false

            }
        },

        computed: {
            singnedIn: function() {
              return window.App.signedIn;
            },

            canUpdate: function() {
                return this.authorize(user => this.data.user_id == window.App.user.id);
            },

            getShortName: function() {
                return this.data.user.first_name + ' ' +  this.data.user.last_name.charAt(0) +'.'
            }

        },

        methods: {

            toggle: function() {
                this.showMessenger = ! this.showMessenger;
            },

            sendmessage: function() {
                axios.post('/store/message', {
                    body: this.body,
                    requested_organization: this.data.user.user_id
                });

                this.showMessenger = false;
                bus.$emit('flash', {body:'Správa bola odoslaná!'});
                this.body = '';

            },

            destroy: function() {
                axios.get('/comment/' + this.data.id + '/delete/comment');
                    this.$emit('destroy', this.data.id);
            },

            hide: function() {
                setTimeout(() => {
                    this.showMessenger = false
            }, 3000)
            }
        }
    };

</script>

<style>
    .left{ float: right}

    .messenger{
        background: none;
        padding: 0rem;
    }
</style>