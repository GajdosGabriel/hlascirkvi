<template>

    <div>
        <div class="level" style="background:rgb(59, 89, 153); color: wheat; margin-top: -.8rem;padding: .7rem 1rem;">
            <strong @click="toggle" style="cursor: pointer">O autorovi</strong>

            <i v-if="singnedIn" @click="toggleMessage" class="far fa-envelope" title="Správa pre autora" style="cursor: pointer"></i>
            <i v-else else @click="annotation=true" class="far fa-envelope" title="Správa pre autora" style="cursor: pointer"></i>
        </div>

        <transition name="fade">
            <small v-if="annotation">
                <a href="/auth/facebook">
                    Najprv sa registrujte cez Facebook
                </a>
            </small>
        </transition>

        <transition name="fade">
            <div>
                <div v-show="showDescription" style="padding: .8rem">
                    {{ user.description }}
                <div @click="toggle" style="cursor: pointer; font-size: 75%; float: right">Zavrieť X</div>

                </div>

                <div v-if="showForm" class="messenger">
                    <div style="text-align: center">Správa pre <strong>{{ user.first_name }} {{ user.last_name}}</strong></div>
                    <div  class="form-group">
                        <textarea v-model="body" class="form" rows="3" placeholder="Text správy..."></textarea>
                    </div>

                    <div v-if="errors.length" class="required">*** Vyplnte text</div>
                    <button style="float: right" class="btn btn-small"  @click="sendmessage">Odoslať</button>
                    <div style="clear: both"></div>
                </div>
            </div>
        </transition>


    </div>

</template>

<script>
    import {bus} from '../app';

    export default {
        props: ['user'],
        data: function() {
            return {
                errors: [],
                showDescription: false,
                body: '',
                annotation: false,
                showForm: false
            }
        },

        computed: {
            singnedIn: function() {
                return window.App.signedIn;
            }

        },

        methods: {
            toggle: function() {
                this.showDescription = ! this.showDescription;
            },

            toggleMessage: function() {
//                this.showForm = ! this.showForm;
                this.showDescription = false;
                bus.$emit('openMessenger');

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
            },
                3000
                )
            }
        }
    };
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
