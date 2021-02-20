<template>

    <div class="container mx-auto p-8">
        <div class="mx-auto max-w-sm">
            <div class="py-10 text-center">

            </div>

            <!-- ... -->

            <div class="bg-white rounded shadow border-gray-300 border-2">
                <div class="border-b py-8 font-bold text-black text-center text-xl tracking-widest uppercase">
                    Nová registrácia!
                </div>


                <form @submit.prevent="attemptRegister" class="bg-grey-lightest px-10 py-10">

                    <div class="mb-3">
                        <input v-model="first_name" type="text" class="border w-full p-3" name="email" placeholder="Meno"
                               required  autofocus>
                        <div style="color: red" v-text="errors.errors"></div>
                    </div>

                    <div class="mb-3">
                        <input v-model="last_name" type="text" class="border w-full p-3" name="email" placeholder="Priezvisko"
                               required>
                        <div style="color: red" v-text="errors.errors"></div>
                    </div>

                    <div class="mb-3">
                        <input v-model="email" type="email" class="border w-full p-3" name="email" placeholder="E-Mail"
                               required>
                        <div style="color: red" v-text="errors.errors"></div>
                    </div>
                    <div class="mb-6">
                        <input v-model="password" :type="inputType ? 'text' : 'password'" class="border w-full p-3"
                               name="password" placeholder="Heslo ..." required>
                    </div>

                    <div class="mb-6">
                        <input v-model="password_confirmation" :type="inputType ? 'text' : 'password'" class="border w-full p-3"
                                placeholder="Potvrdiť heslo" required>
                        <span v-if="password" @click.prevent="togglePassword" class="cursor-pointer" style="font-size: 80%; margin-top: -1rem"> {{ inputType ?
                            'Skryť' : 'Zobraziť' }} heslo</span>
                    </div>

                    <div class="mb-6">
                        <span class="text-sm">Som človek  3+2 = </span>
                        <input type="number" v-model="iamHuman" placeholder="Zadajte číslo 5" class="border-2 text-sm w-full border-gray-200 rounded-md px-2 py-1" required>
                    </div>

                    <div class="flex">
                        <button type="submit"
                            class="hover:bg-primary-dark border-2 rounded-sm w-full p-4 text-sm uppercase font-bold tracking-wider">
                            Registrovať sa
                        </button>

                    </div>
                </form>

                <div class="border-t px-10 py-6">
                    <div class="flex justify-between">
                        <a class="font-bold text-primary hover:text-primary-dark no-underline" href="/login">Späť</a>
                        <a href="/auth/facebook" class="text-grey-darkest hover:text-black no-underline">Registrácia pomocou Facebooku</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import {bus} from '../../app';

    export default {
        data: function () {
            return {
                email: '',
                password: '',
                password_confirmation: '',
                first_name: '',
                last_name: '',
                iamHuman: '',
                rememberMe: true,
                loading: false,
                errors: {},
                inputType: false

            }
        },

        computed: {
            isValidForm: function () {
                return this.emailIsValid();
            },

            isValidPassword: function () {
                if (this.password.length < 6) {
                    return false
                }
                ;
                return this.emailIsValid() && this.password;
            }
        },

        methods: {
//            nedokočené opačne
            togglePassword: function () {
                this.inputType = !this.inputType;
            },
            emailIsValid: function (mail) {
                if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.email)) {
                    return (true)
                } else {
//                    alert("Zadali ste neplatnú emailú adresu!");
                    return false
                }

            },

            attemptRegister: function () {
//                this.errors = [];
                this.loading = true;
                axios.post('/register', {
                    iamHuman: this.iamHuman,
                    last_name: this.last_name,
                    first_name: this.first_name,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                    rememberMe: this.rememberMe
                })
                    .then(resp => {
                        location.reload();
                        bus.$emit('flash', {body: 'Vitajte, rezistrácia je úspešná.'})
                    })

                    //                .catch (error => this.errors = error.response.data);


                    .catch(error => {
                        this.loading = false;
                        this.errors = error.response.data;

                        if (error.response.status == 422) {
                            bus.$emit('flash', {body: 'Údaje nie sú správne. Skúste znova.'})
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
        /* .slide-fade-leave-active below version 2.1.8 */
    {
        transform: translateX(10px);
        opacity: 0;
    }
</style>
