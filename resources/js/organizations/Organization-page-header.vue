<template>
    <div
        @click="closeLoginInfo"
        class="md:flex justify-between text-gray-700 pb-3 col-span-12 items-center"
    >
        <div class="flex">
            <!-- check if is person-->

            <organization-avatar :organization="organization" />

            <div class="ml-4 mb-4">
                <h1
                    class="text-2xl font-semibold"
                    v-text="organization.title"
                ></h1>

                <div class="flex" v-if="organization.description">
                    <span
                        @click="toggle"
                        class="hover:bg-blue-400 cursor-pointer rounded-sm px-2"
                        :class="{
                            'bg-blue-500 text-gray-200 ': showDescription,
                        }"
                    >
                        Profil
                    </span>
                </div>

                <transition name="fade">
                    <div v-if="showDescription">
                        <!--<textarea v-if="textform" name="" id=""style="width: 50vw" rows="7"></textarea>-->
                        <div v-if="organization.description == null">
                            Profil je nevyplnený.
                        </div>
                        {{ organization.description }} <br />

                        <div
                            @click="toggle"
                            style="
                                cursor: pointer;
                                font-size: 75%;
                                float: left;
                                margin-right: 1rem;
                            "
                        >
                            Zavrieť X
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <div class="relative">
            <!-- Button i-Memeber-->
            <div
                v-html="buttonText"
                @click.stop="onClickSubscribeButton"
                title="Upozornenia na nové príspevky!"
                :class="classButton"
                class="p-2 rounded-md cursor-pointer flex justify-center whitespace-nowrap text-white"
            ></div>
            <!-- Login link -->
            <div
                v-if="open"
                class="absolute z-10 bg-white border-2 border-gray-400 p-2 pb-5 rounded-md text-center"
            >
                <p class="pb-4 text-sm">Prihláste sa, alebo zaregistrujte.</p>
                <a :href="'/login'" class="btn btn-primary w-full"
                    >Pokračovať</a
                >
            </div>
        </div>
    </div>
</template>

<script>
import { bus } from "../app";
import organizationAvatar from "./Organization-avatar";
import modal from "../organizations/Organization-page-modal";
import { createdMixin } from "../mixins/createdMixin";

export default {
    props: ["organization"],
    components: { modal, organizationAvatar },
    mixins: [createdMixin],
    data: function () {
        return {
            domain: window.App.baseUrl,
            showDescription: false,
            textform: false,
            favorited: this.organization.isFavorited,
            open: false,
        };
    },
    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },
        buttonText: function () {
            return this.favorited
                ? "Sledujete kanál"
                : "Sledovať kanál " + this.organization.title;
        },

        classButton: function () {
            return [
                this.favorited
                    ? "bg-gray-400 hover:bg-gray-500"
                    : "bg-red-600 hover:bg-red-700",
            ];
        },
    },

    methods: {
        toggle: function () {
            this.showDescription = !this.showDescription;
        },
        toggleLogin: function () {
            // alert('Vytvorte si účet alebo prihláste sa.');
            this.open = !this.open;
        },

        closeLoginInfo() {
            if (this.open == true) {
                this.open = false;
            }
        },
        toggleFavorited: function () {
            this.favorited = !this.favorited;
        },

        onClickSubscribeButton: function () {
            if (!this.signedIn) {
                return this.toggleLogin();
            }
            axios
                .post(
                    "/api/organizations/" + this.organization.id + "/favorites"
                )
                .then((response) => {
                    this.toggleFavorited();
                    this.messageNotification();
                });
        },

        messageNotification: function () {
            if (this.favorited) {
                bus.$emit("flash", { body: "Ste prihlásený!" });
            } else {
                bus.$emit("flash", { body: "Ste odhlásený!" });
            }
        },

        // openModal: function () {
        //     bus.$emit('openModalHelpUs');
        // }
    },
};
</script>

<style>
.slide-fade-enter-active,
.slide-fade-leave-to {
    transition: opacity 0.5s;
}

.slide-fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
    opacity: 0;
}
</style>
