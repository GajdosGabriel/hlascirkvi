<template>
    <div>

        <a href="#" @click="toggle">Pomôžte autorovi</a> <!-- Trigger Modal. -->

        <!-- Modal -->
        <div v-if="show" class="modal" id="modal-name">
            <div class="modal-sandbox"></div>
            <div class="modal-box">
                <div class="modal-header level">
                    <h4>Pomôžte šíriť myšlienky Božieho slova! Pozrite ako to spraviť.</h4>
                    <div @click="toggle" class="close-modal">&#10006;</div>
                </div>

                <div class="modal-body">

                    <transition name="fade">
                        <div v-if="formOpen">
                            <div v-if="formClose"  class="form-group">
                                <textarea v-model="body" class="form" rows="2" placeholder="Napíšte svoj telefón: zavoláme vám, alebo email: napíšeme Vám!"></textarea>
                            </div>
                            <div v-if="errors.length" style="color: red">Vyplnte text</div>
                        </div>
                    </transition>

                    <a v-if="!formOpen" style="background: red; margin-bottom: .8rem; color: white; font-size: 130%;" class="btn" @click="formOpen=true">Chcem pomôsť! <i class="fas fa-arrow-right"></i></a>
                    <a  @click="sendmessage" v-if="formOpen" style="margin-bottom: 2rem; float: right; font-size: 130%;" class="btn">Odoslať správu <i class="fas fa-arrow-right"></i></a>

                    <transition name="fade">
                        <div v-if="annotation" style="background: brown; color: whitesmoke; padding: 1.7rem">
                            <h5>Správa bola odoslaná! Budeme Vás kontaktovať.</h5>
                        </div>
                    </transition>

                    <iframe width="100%" height="470px" src="https://www.youtube.com/embed/tTvyX9Nzzrw?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    <br />
                    <!--<button @click="toggle" class="close-modal">Zatvoriť!</button>-->
                </div>
            </div>
        </div>

        <!-- Aditional Styles -->
        <!--<link href="https://fonts.googleapis.com/css?family=Roboto:300" rel="stylesheet">-->


    </div>
</template>

<script>
    import {bus} from '../app';
    export default {
        data: function() {
            return {
                show: false,
                errors: [],
                annotation: false,
                formOpen: false,
                formClose: true,
                body: ''
            }
        },

        created: function() {
            bus.$on('openModal', () => {this.toggle()});
        },

        methods:{
            toggle: function(){
                this.show = !this.show;
            },

            sendmessage: function() {

                this.checkForm();

                axios.post('/store/message', { body: this.body });
                this.formClose = false;
                this.annotation = true;
                this.hide();
            },

            hide: function() {
                setTimeout(() => {
                    this.annotation = false
                }, 3000);
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