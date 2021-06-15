<template>
    <div v-if="show" class="fixed z-10 inset-0 overflow-y-auto">
        <div
            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
        >
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span
                class="hidden sm:inline-block sm:align-middle sm:h-screen"
                aria-hidden="true"
                >&#8203;</span
            >

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-headline"
            >
                <div class="bg-blue-900 text-gray-300 p-4">
                    <!--  Header  -->
                    <div class="flex justify-between">
                        <h3
                            class="text-lg leading-6 font-medium"
                            id="modal-headline"
                        >
                            Pridať modlitebný úmysel
                        </h3>

                        <div @click="toggle" class="close-modal cursor-pointer">
                            &#10006;
                        </div>
                    </div>
                </div>

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <form @submit.prevent="savePrayer">
                                <div class="flex flex-col">
                                    <label
                                        for="title"
                                        style="font-weight: bold;"
                                        >Modlitba za</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.title"
                                        required
                                        autofocus
                                        class="border-2 border-gray-400 rounded-md p-2"
                                        id="title"
                                        plaveholder="Nadpis modlitby"
                                    />
                                    <span class="text-sm italic"
                                        >Krátko o Vašom modltitbovom úmysle,
                                        napr: za uzdravenie manžela, za prácu a
                                        pod.</span
                                    >
                                </div>

                                <div class="flex flex-col mt-3 ">
                                    <label for="body" style="font-weight: bold;"
                                        >Viac o úmysle</label
                                    >
                                    <textarea
                                        v-model="form.body"
                                        required
                                        class="border-2 border-gray-400 rounded-md p-2"
                                        rows="5"
                                        id="body"
                                    ></textarea>
                                    <span class="text-sm italic"
                                        >Tu napíšte viac o dôvode modlitby, aby
                                        Vám ostatní mohli lepšie
                                        porozumieť.</span
                                    >
                                </div>

                                <div class="flex flex-col mt-3 ">
                                    <label
                                        for="user_name"
                                        style="font-weight: bold;"
                                        >Prezývka</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.user_name"
                                        required
                                        class="border-2 border-gray-400 rounded-md p-2"
                                        id="user_name"
                                        placeholder="Prezývka"
                                    />
                                    <span class="text-sm italic"
                                        >Pre ostatných, aby vedeli, ako Vás
                                        osloviť v modlitbe.</span
                                    >
                                </div>

                                <div class="flex flex-col" v-if="!authUser">
                                    <label
                                        for="email"
                                        style="font-weight: bold;"
                                        >Emailova adresa</label
                                    >
                                    <input
                                        type="email"
                                        v-model="form.email"
                                        required
                                        class="border-2 border-gray-400 rounded-md p-2"
                                        placeholder="emailová adresa"
                                        id="email"
                                    />
                                    <span class="text-sm italic"
                                        >Email sa nikde nezverejňuje a je
                                        potrebný na overenie. Zároveň, Vám budú
                                        doručené oznámenia, keď sa za Vás niekto
                                        pomodlí, alebo napíše.</span
                                    >
                                </div>

                                <div
                                    class="bg-gray-50 sm:flex justify-between  "
                                >
                                    <button
                                        type="button"
                                        @click="toggle"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                    >
                                        Zrušiť
                                    </button>

                                    <button
                                        type="submit"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-gray-300 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                    >
                                        Uložiť
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { bus } from "../app";
import Form from "../messenger/form";
import Axios from "axios";

export default {
    components: { Form },
    data: function() {
        return {
            show: false,
            annotation: false,
            authUser: window.App.user,
            form: {}
        };
    },

    created: function() {
        bus.$on("openModalPrayer", () => {
            this.show = true;
        });

        bus.$on("passToModalEdit", prayer => {
            this.form = prayer;
            this.show = true;
        });
    },

    methods: {
        toggle: function() {
            this.show = !this.show;
        },

        savePrayer: function() {
            // Update prayer
            if (this.form.id) {
                console.log(this.form);
                Axios.put("/modlitby/" + this.form.id, { title: this.form.title, body: this.form.body, user_name: this.form.user_name } ).then(
                    response => {
                        this.form = {};
                        this.show = false;
                        window.location.reload();
                    }
                );
                return;
            }
            // Save prayer
            Axios.post("/modlitby", this.form).then(response => {
                this.form = {};
                this.show = false;
                window.location.reload();
            });
        }
    }
};
</script>
