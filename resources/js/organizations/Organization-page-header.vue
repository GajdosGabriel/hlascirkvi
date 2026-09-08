<template>
    <!--
        Identita kanála a odber. Stojí v dvoch šablónach naraz — v profile
        kanála ako jeho hlavička a v detaile príspevku ako pásik nad textom —
        preto si nesie len vnútorný rad a rám s odsadením necháva na obal.
    -->
    <div
        @click="closeLoginInfo"
        class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3"
    >
        <div class="flex min-w-0 items-center gap-3">
            <organization-avatar :organization="organization" />

            <div class="min-w-0">
                <!--
                    Titulok kanála je nadpisom len tam, kde je kanál témou
                    stránky. V detaile príspevku patrí h1 článku, preto sa
                    značka dá prepnúť zvonka.
                -->
                <component
                    :is="heading"
                    class="ar-display truncate text-xl font-bold leading-tight text-[color:var(--ar-ink)] md:text-2xl"
                    v-text="organization.title"
                ></component>

                <button
                    v-if="organization.description"
                    type="button"
                    @click.stop="toggle"
                    class="mt-0.5 inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 transition-colors hover:text-[color:var(--ar-accent)]"
                >
                    <i
                        class="fas fa-chevron-down text-[.6rem] transition-transform"
                        :class="{ 'rotate-180': showDescription }"
                    ></i>
                    {{ showDescription ? "Skryť profil" : "Profil kanála" }}
                </button>
            </div>
        </div>

        <div class="relative shrink-0">
            <button
                type="button"
                @click.stop="onClickSubscribeButton"
                :title="buttonTitle"
                :class="classButton"
                class="ar-btn"
            >
                <i class="far" :class="favorited ? 'fa-bell-slash' : 'fa-bell'"></i>
                {{ buttonText }}
            </button>

            <!-- Bez účtu odber nemá kam patriť; namiesto chyby ponuka prihlásenia. -->
            <transition name="fade">
                <div
                    v-if="open"
                    class="absolute right-0 z-20 mt-2 w-56 rounded-lg border border-[color:var(--ar-line)] bg-white p-4 text-center shadow-lg"
                >
                    <p class="mb-3 text-sm text-gray-600">
                        Na odber sa treba prihlásiť alebo zaregistrovať.
                    </p>
                    <a href="/login" class="ar-btn ar-btn--accent w-full">Pokračovať</a>
                </div>
            </transition>
        </div>

        <!-- Popis je v pružnom rade celou šírkou, takže si ide pod identitu. -->
        <transition name="fade">
            <div
                v-if="showDescription"
                class="w-full border-t border-[color:var(--ar-line)] pt-3 text-sm leading-relaxed text-gray-600"
            >
                {{ organization.description }}
            </div>
        </transition>
    </div>
</template>

<script>
import { bus } from "../app";
import organizationAvatar from "./Organization-avatar";
import { createdMixin } from "../mixins/createdMixin";

export default {
    props: {
        organization: { type: Object, required: true },
        // Značka titulku. h1 patrí kanálu len na jeho vlastnej stránke.
        heading: { type: String, default: "h1" },
    },
    components: { organizationAvatar },
    mixins: [createdMixin],
    data: function () {
        return {
            showDescription: false,
            favorited: this.organization.isFavorited,
            open: false,
        };
    },
    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },

        // Názov kanála stojí hneď vedľa tlačidla, v jeho popise by sa
        // zopakoval po tretíkrát a na mobile rozbil riadok.
        buttonText: function () {
            return this.favorited ? "Sledujete kanál" : "Sledovať kanál";
        },

        buttonTitle: function () {
            return this.favorited
                ? "Zrušiť upozornenia na nové príspevky"
                : "Upozornenia na nové príspevky kanála " + this.organization.title;
        },

        classButton: function () {
            return this.favorited ? "ar-btn--quiet" : "ar-btn--accent";
        },
    },

    methods: {
        toggle: function () {
            this.showDescription = !this.showDescription;
        },

        toggleLogin: function () {
            this.open = !this.open;
        },

        closeLoginInfo() {
            if (this.open == true) {
                this.open = false;
            }
        },

        onClickSubscribeButton: function () {
            if (!this.signedIn) {
                return this.toggleLogin();
            }

            axios
                .post("/api/organizations/" + this.organization.id + "/favorites")
                .then(() => {
                    this.favorited = !this.favorited;
                    this.messageNotification();
                });
        },

        messageNotification: function () {
            bus.$emit("flash", {
                body: this.favorited
                    ? "Odoberáte kanál " + this.organization.title
                    : "Odber kanála je zrušený.",
            });
        },
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>
