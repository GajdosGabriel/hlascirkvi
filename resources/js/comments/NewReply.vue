<template>
    <div>
        <form @submit.prevent="sendComment">
            <div class="form-group">
                <textarea class="w-full p-2 border-2 border-gray-400 rounded-md" v-model="body" rows="2"
                          placeholder="Pridajte komentár ..." required></textarea>
                <button style="float: right" class="btn">Uložiť</button>
            </div>

            <!-- Modal -->
            <div v-if="modal" class="fixed z-10 inset-0 overflow-y-auto">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <!-- This element is to trick the browser into centering the modal contents. -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                          aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                        role="dialog" aria-modal="true" aria-labelledby="modal-headline">

                        <div class="bg-blue-900 text-gray-300 p-4">
                            <!--  Header  -->
                            <div class="flex justify-between">
                                <h3 class="text-lg leading-6 font-medium" id="modal-headline">
                                    Uložiť komentár
                                </h3>

                                <div @click="toggle" class="close-modal cursor-pointer">&#10006;</div>
                            </div>
                        </div>

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">

                                <div class="text-center sm:mt-0 sm:ml-4 sm:text-left w-full">

                                        <p>Komentár je pripravený na odoslanie.</p>
                                        <input required type="email" v-model="email"
                                               class="form-control w-full my-3" placeholder="Váš email">
                                        <p>Email nebude nikde zverejnený.</p>

                                    <a href="#" class="hover:text-blue-700" @click="toggle">
                                        Zrušiť
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">

                            <button @click="storeComment"
                                class="btn w-full py-3 bg-blue-700 text-white font-semibold hover:text-gray-900">
                                Zverejniť hneď
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        props: ['post'],
        data: function () {
            return {
                body: '',
                email: '',
                endpoint: '/comment' + location.pathname,
                modal: false
//                endpoint:'/store/comment/xx/qwe' + location.pathname
            }
        },

        computed: {
            signedIn: function () {
                return window.App.signedIn;
            }
        },

        methods: {
            toggle: function () {
                this.modal = !this.modal;
            },

            sendComment: function () {
                if (!this.signedIn) {
                    return this.toggle()
                }
                ;
                this.storeComment();
            },

            storeComment: function () {
                axios.post(this.endpoint, {body: this.body, email: this.email})
                    .then(({data}) => {
                        this.body = '';
                        this.$emit('created', data);
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
    .form-group {
        padding: 0rem;
    }
</style>
