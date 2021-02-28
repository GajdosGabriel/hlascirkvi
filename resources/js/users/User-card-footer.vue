<template>

    <div class="bg-blue-800 p-2">

<!--        <svg class="h20 w-20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">-->
<!--            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />-->
<!--        </svg>-->

        <div class="flex space-x-4 text-gray-100  items-center">
            <strong @click="toggle" class="cursor-pointer">O autorovi</strong>

            <svg v-if="singnedIn" @click="toggleMessage" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
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
