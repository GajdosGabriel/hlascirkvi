<template>
    <div>
<!-- Nepoužíva sa tento component -->
        <a href="#" @click="toggle"><i title="Zmazať" class="fas fa-trash-alt"></i></a> <!-- Trigger Modal. -->

        <!-- Modal -->
        <div v-if="show" class="modal" id="modal-name">
            <div class="modal-sandbox"></div>
            <div class="modal-box">
                <div class="modal-header level">
                    <h4>Vykonať</h4>
                    <div @click="toggle" class="close-modal">&#10006;</div>
                </div>

                <div class="modal-body">
                    <h2>Skutočne zmazať túto položku?</h2>

                    <!--<transition name="fade">-->
                        <!--<div v-if="formOpen">-->
                            <!--<div v-if="formClose"  class="form-group">-->
                                <!--<textarea v-model="body" class="form" rows="2" placeholder="Napíšte svoj telefón: zavoláme vám, alebo email: napíšeme Vám!"></textarea>-->
                            <!--</div>-->
                            <!--<div v-if="errors.length" style="color: red">Vyplnte text</div>-->
                        <!--</div>-->
                    <!--</transition>-->


                </div>
                <div class="modal-header">
                  <button @click="sendmessage" class=" btn btn-primary">Áno</button>
                  <button  @click="toggle" class=" btn btn-primary">Nie</button>
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
        props: ['organization'],
        data: function() {
            return {
                show: false,
                annotation: false
            }
        },

        created: function() {
            bus.$on('openModal', () => {this.show = true});
        },

        methods:{
            toggle: function(){
                this.show = !this.show;
            },

            sendmessage: function() {
                axios.get('/user/' + this.organization.id + '/' + this.organization.slug + '/delete');
                this.show = false;
                bus.$emit('flash', {body:'Položka bola zmazaná!'});
            }
        }


    }
</script>
