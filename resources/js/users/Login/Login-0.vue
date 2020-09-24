<template>
    <div class="login" v-if="closeAllForm">
        <div class="card" style="background: white">
            <div class="card-header level">
                <div>Login</div>
                <a @click="toggle" href="#" v-if="showForm">x</a>
                <a @click="close" href="#" v-else="showForm">x</a>
            </div>

            <div class="card-body">

                <ul style="color: red" v-if="errors.length > 0">
                    <li v-for="error in errors" :key="errors.indexOf(error)" >{{ error }}</li>
                </ul>

                <ul v-if="! showForm" class="box" style="padding-top: 1rem;margin-bottom: 1.3rem;">

                    <li style="margin-bottom: 1.3rem;">
                        <a href="/auth/facebook" title="Prihlásenie cez Facebook">
                            <h5> <i class="fas fa-check"></i> Pokračovať pomocou Facebooku</h5>
                            <!--<img style="max-width: 20rem" src="images/facebook-sign-in-button.png">-->
                        </a>
                    </li>


                    <li style="margin-bottom: 1.3rem;">
                        <a @click="toggle" href="#" title="Prihlásenie sa pomocou emailu">
                            <h5> <i class="fas fa-check"></i> Už som registrovaný</h5>
                        </a>
                    </li>

                    <li>
                        <a href="/register" title="Vytvoriť účet">
                            <h5> <i class="fas fa-check"></i> Vytvoriť novú registráciu</h5>
                        </a>
                    </li>

                </ul>

                <LoginByEmail :getShowForm="showForm"></LoginByEmail>

            </div>

            <!--<div class="card-footer" style="">-->
                <!--<a href="/password/reset">Zabudnuté heslo</a>-->
                <!--&lt;!&ndash;<a href="/register">Nová registrácia</a>&ndash;&gt;-->
            <!--</div>-->
        </div>
    </div>
</template>
<script>
    import {bus} from '../../app';
    import LoginByEmail from './Login-by-email.vue';
    export default {
        components: {LoginByEmail},
        data: function() {
            return {
                showForm: false,
                closeAllForm: true,
                errors: []
            }
        },

        methods: {
            toggle: function() {
                this.showForm = ! this.showForm;
            },

            close: function() {
                this.$emit('closeModal', false)
            }
        }
    }
</script>

