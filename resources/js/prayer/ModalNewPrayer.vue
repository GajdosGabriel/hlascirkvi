<template>
    <div>

        <!-- Modal -->
        <div v-if="show" class="modal" id="modal-name">
            <div class="modal-sandbox"></div>
            <div class="modal-box">
                <div class="modal-header level">
                    <h4>Pridať modlitebný úmysel</h4>
                    <div @click="toggle" class="close-modal">&#10006;</div>
                </div>

                <div class="modal-body">
                    <form @submit.prevent="savePrayer">
                        <div>
                            <label for="title" style="font-weight: bold;">Modlitba za</label>
                            <input type="text" v-model="form.title" required autofocus style="padding: 5px" id="title"
                                   plaveholder="Nadpis modlitby">
                            <span>Krátko o Vašom modltitbovom úmysle, napr: za uzdravenie manžela, za prácu a pod.</span>
                        </div>

                        <div style="margin-top: 10px">
                            <label for="body" style="font-weight: bold;">Viac o úmysle</label>
                            <textarea v-model="form.body" required style="padding: 5px; width: 100%" rows="5"
                                      id="body"></textarea>
                            <span>Tu napíšte viac o dôvode modlitby, aby Vám ostatní mohli lepšie porozumieť.</span>
                        </div>

                        <div style="margin-top: 10px">
                            <label for="user_name" style="font-weight: bold;">Prezývka</label>
                            <input type="text" v-model="form.user_name" required style="padding: 5px"
                                   id="user_name" placeholder="Prezývka">
                            <span>Pre ostatných, aby vedeli, ako Vás osloviť v modlitbe.</span>
                        </div>

                        <div style="margin-top: 10px" v-if="! authUser">
                            <label for="email" style="font-weight: bold;">Emailova adresa</label>
                            <input type="email" v-model="form.email" required style="padding: 5px;  width: 100%"
                                   placeholder="emailová adresa" id="email">
                            <span>Email sa nikde nezverejňuje a je potrebný na overenie.
                            Zároveň, Vám budú doručené oznámenia, keď sa za Vás niekto pomodlí, alebo napíše.</span>
                        </div>

                        <div style="margin-top: 10px;  width: 100%;">
                            <button @click="toggle" class=" btn">Zružiť</button>
                            <button type="submit" class=" btn"
                                    style="background: rgba(0,0,125,0.77); color: whitesmoke;  font-size: 15px">Uložiť
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {bus} from '../app';
    import Form from "../messenger/form";
    import Axios from 'axios';


    export default {
        components: {Form},
        data: function () {
            return {
                show: false,
                annotation: false,
                authUser: window.App.user,
                form: {}
            }
        },

        created: function () {
            bus.$on('openModalPrayer', () => {
                this.show = true
            });

            bus.$on('passToModalEdit', (prayer) => {
                this.form = prayer;
                this.show = true;
            });
        },

        methods: {
            toggle: function () {
                this.show = !this.show;
            },

            savePrayer: function () {
                // Update prayer
                if (this.form.id){
                    console.log(this.form);
                    Axios.put('/modlitby/' + this.form.id , this.form)
                        .then(response => {
                            this.form = {};
                            this.show = false;
                            window.location.reload()
                        }
                    );
                    return;
                }
                // Save prayer
                Axios.post('/modlitby', this.form).then(
                    response => {
                        this.form = {};
                        this.show = false;
                        window.location.reload()
                    }
                )
            }
        }


    }
</script>
