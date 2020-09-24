<template>
    <div>
        <transition name="fade">
            <div v-if="show">
                <div  class="form-group" style="padding: 0rem">
                    <textarea v-model="body" class="form" rows="3" placeholder="Poslať právu"></textarea>
                </div>
                <div v-if="errors.length" style="color: red">Vyplnte text</div>

                <button class="btn btn-small"  @click="sendmessage">Odoslať</button>
            </div>
        </transition>

        <transition name="fade">
            <div v-if="annotation" style="background: brown; color: whitesmoke; padding: 1.7rem">
                <h5>Správa bola odoslaná!</h5>
            </div>
        </transition>

        <div v-show="!show" class="left" @click="toggle">Máte typ na vysielanie?</div>
        <div v-show="show" class="left" @click="toggle">Zavrieť</div>

    </div>
</template>

<script>
    import {bus} from '../app';
    export default {
        data: function() {
            return {
                errors: [],
                show: false,
                annotation: false,
                body: ''
            }
        },

        methods: {
            sendmessage: function() {

                this.checkForm();

                axios.post('/store/message', { body: this.body });
                this.show = false;
                this.annotation = true;
                this.hide();
            },

            hide: function() {
                setTimeout(() => {
                    this.annotation = false
                }, 3000);
            },

            toggle: function() {
                this.show = ! this.show;
            },

            checkForm: function (e) {
                if (this.body) {
                    return true;
                }

                this.errors = [];

                if (!this.body) {
                    this.errors.push('Text sa vyžaduje.');
                }
                e.preventDefault();
            }
        }

    }
</script>

<style>
    .left{ float: right}
    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }
</style>