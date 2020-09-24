<template>
    <div>
        <form @submit.prevent="sendComment">
        <div class="form-group">
            <textarea v-model="body" rows="2" placeholder="Pridajte komentár ..." required></textarea>
            <button style="float: right" class="btn btn-small">Uložiť</button>
        </div>

        <!-- Modal -->
        <div v-if="modal" class="modal" id="modal-name">
            <div class="modal-sandbox"></div>
            <div class="modal-box">
                <div class="modal-body">
                    <div style="text-align: center; margin-bottom: 3rem">
                        <i class="far fa-check-circle fa-8x" style="color: green"></i>
                        <h2>Komentár bol odoslaný!</h2>
                    </div>


                    <transition name="fade">
                        <form @submit.prevent="storeComment">
                            <div  style="text-align: center">
                                <span style="margin-bottom: .5rem">Pripojte email a Váš komentár bude zverejnený ihneď.</span>

                                <div  class="form-group" style="margin: 1rem">
                                    <input required style="max-width: 21rem" type="email" v-model="email" class="form" rows="2" placeholder="Váš email">
                                    <button class="btn">Zverejniť hneď</button>
                                </div>
                                       <a @click="noEmail">Nie, počkám na zverejnenie komentára správcom.</a>
                                <!--<div v-if="errors.length" style="color: red">Vyplnte text</div>-->
                            </div>
                        </form>
                    </transition>

                </div>
            </div>
        </div>


        </form>
    </div>
</template>

<script>
    export default {
        props: ['post'],
        data: function() {
            return {
                body:'',
                email:'',
                endpoint:'/comment' + location.pathname,
                modal: false
//                endpoint:'/store/comment/xx/qwe' + location.pathname
            }
        },

        computed: {
            signedIn: function() {
                return window.App.signedIn;
            }
        },

        methods: {
            toggle: function() {
                this.modal = ! this.modal;
            },

            sendComment: function() {
                if(! this.signedIn) {
                   return this.toggle()
                };
                this.storeComment();
            },

            storeComment: function() {
                axios.post(this.endpoint, {body: this.body, email: this.email} )
                        .then(({data}) => {
                    this.body='';
                    this.$emit('created' , data);
                    this.modal = false;
                });

            },


            noEmail: function () {
                this.storeComment();
                this.toggle();
            }
        }
    }
</script>

<style>
    .form-group{
        padding: 0rem;
    }
</style>