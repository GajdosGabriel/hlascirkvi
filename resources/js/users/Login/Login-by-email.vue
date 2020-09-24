<template>

    <div v-if="getShowForm" class="box">
        <h5>Prihlásenie emailom</h5>
        <form @submit.prevent="attemptLogin()">
            <div>
                <div class="form-group inline">
                    <label>Email</label>
                    <input v-model="email" type="email" class="form-control is-invalid" name="email" value="" required autofocus>
                    <transition name="slide-fade">
                        <i v-if="isValidForm" style="color: green" class="fas fa-check"></i>
                    </transition>
                </div>
                <div style="color: red" v-text="errors.errors"></div>
            </div>

            <div>
                <div class="form-group inline">
                    <label>Heslo</label>
                    <input v-model="password" :type="inputType ? 'text' : 'password'" class="form-control is-invalid" name="password" required>
                    <transition name="slide-fade">
                        <i v-if="isValidPassword" style="color: green" class="fas fa-check"></i>
                    </transition>
                </div>
                <br />
                <a href="#" @click.prevent="togglePassword" style="font-size: 80%; margin-top: -1rem"> {{ inputType ? 'Skryť' : 'Zobraziť' }} heslo</a>
            </div>

            <input v-model="rememberMe" type="hidden" name="remember">

            <div class="form-group">
                <button style="width: 100%" type="submit" class="btn btn-primary">
                    Vstúpiť
                </button>
            </div>

        </form>

        <div style="text-align: center">
            <a  href="/register">Nová registrácia</a>
        </div>

    </div>

</template>

<script>
    import {bus} from '../../app';
    export default {
        props:{
            getShowForm: {
                required: true,
                type: Boolean
            }

        },
        data: function() {
            return {
                email:'',
                password: '',
                rememberMe: true,
                loading: false,
                errors: {},
                inputType: false

            }
        },

        computed: {
            isValidForm: function() {
                return this.emailIsValid();
            },

            isValidPassword: function() {
                if(this.password.length < 6) {return false};
                return this.emailIsValid() && this.password;
            }
        },

        methods: {
//            nedokočené opačne
            togglePassword: function() {
              this.inputType = ! this.inputType;
            },
            emailIsValid: function(mail)
            {
                if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.email))
                {
                    return (true)
                } else {
//                    alert("Zadali ste neplatnú emailú adresu!");
                    return false
                }

            },

            attemptLogin: function() {
//                this.errors = [];
                this.loading = true;
                axios.post('/login', {email:this.email, password: this.password, rememberMe: this.rememberMe})
                        .then( resp => {
                            location.reload();
                            bus.$emit('flash', {body:'Vitajte, ste úspešne prihlásený.'})
                        })

//                .catch (error => this.errors = error.response.data);


                .catch ( error => {
                    this.loading = false;
                this.errors = error.response.data;

                    if(error.response.status == 422) {
                        bus.$emit('flash', {body:'Údaje nie sú správne. Skúste znova.'})
                        this.errors.push('Prihlasovacie údaje nie sú správne.');

                    } else {

                        this.errors.push('Niečo zlyhalo, skúste znova načítať web a prihlásiť sa.');
                    }
                });
            }

        }
    }
</script>

<style scoped>
    .box {
        margin: 2rem;
    }

    .card-body {
        display: flex;
    }

    .inline {
        display: inline-flex;
    }

    .form-group {
        padding: 1rem 0rem;
    }

    label {
        background: #898989;
        padding: 0 .5rem;
        color: whitesmoke;
    }


    .slide-fade-enter-active {
        transition: all .3s ease;
    }
    .slide-fade-leave-active {
        transition: all .8s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }
    .slide-fade-enter, .slide-fade-leave-to
        /* .slide-fade-leave-active below version 2.1.8 */ {
        transform: translateX(10px);
        opacity: 0;
    }
</style>