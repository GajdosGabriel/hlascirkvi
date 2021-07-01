<!--<template>-->

    <!--<div>-->
        <!--<h4 style="margin: 2rem 0rem; cursor: pointer" @click="toggle">Vytvoriť organizáciu-->
            <!--<i v-if="!showForm" class="far fa-plus-square"></i>-->
            <!--<i v-if="showForm" class="far fa-minus-square"></i>-->
        <!--</h4>-->

        <!--<form @submit.prevent="saveOrganization" v-if="showForm">-->
            <!--<div class="form-group">-->
                <!--<label>Meno novej organizácie</label>-->
                <!--<input type="text" v-model="title" name="name" placeholder="Názov organizácie" required>-->
            <!--</div>-->

            <!--<div class="form-group">-->
                <!--<label>Ulica a číslo</label>-->
                <!--<input type="text" v-model="street" name="name" placeholder="Ulica a číslo" required>-->
            <!--</div>-->

            <!--<div class="form-group">-->
                <!--<label>Mesto</label>-->
                <!--<input type="text" v-model="city" name="name" placeholder="Mesto" required>-->
            <!--</div>-->


            <!--<div class="form-group">-->
                <!--<label>Telefón</label>-->
                <!--<input type="number" v-model="phone" name="name" placeholder="Potrebné v prípade vytvorenia akcie">-->
            <!--</div>-->


            <!--<div class="form-group" style="text-align: right">-->
                <!--<button class="btn btn-small">Uložiť</button>-->
            <!--</div>-->

        <!--</form>-->
    <!--</div>-->

<!--</template>-->

<script>
    import {bus} from '../app';
    export default {
        props: ['user'],
        data: function() {
            return {
                showForm: false,
                title: '',
                street: '',
                city: '',
                village: '',
                phone: ''
            }
        },

        methods: {
            toggle: function () {
              this.showForm = ! this.showForm;
            },

            saveOrganization: function() {
                axios.post('/organization/new/' + this.user.id + '/store', {
                    title: this.title,
                    street: this.street,
                    village_id: this.village_id,
                    phone: this.phone
                }).then( function () {
                    bus.$emit('flash', {body:'Organizácia bola uložená!'});
                    location.reload();

                });

                this.clearForm();
            },

            clearForm: function() {
                this.title='',
                this.street= '',
                this.village_id='',
                this.phone= '',
                this.showForm = false
            }
        }
    }
</script>
