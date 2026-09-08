<template>
    <!--
        Odporúčanie príspevku. Obal je `relative`, aby ponuka prihlásenia
        visela pod tlačidlom a nie pri okraji stránky.
    -->
    <div class="relative inline-flex">
        <button
            type="button"
            @click.stop="pressRecomendedButton"
            :title="title"
            :class="buttonClass"
            class="ar-btn"
        >
            <i class="far fa-thumbs-up"></i>
            {{ label }}
            <span v-if="favoriteCount > 0" class="tabular-nums opacity-70">{{ favoriteCount }}</span>
        </button>

        <transition name="fade">
            <div
                v-if="showLoginForm"
                class="absolute right-0 top-full z-20 mt-2 w-56 rounded-lg border border-[color:var(--ar-line)] bg-white p-4 text-center shadow-lg"
            >
                <p class="mb-3 text-sm text-gray-600">
                    Odporúčať môžu prihlásení čitatelia.
                </p>
                <a href="/login" class="ar-btn ar-btn--accent w-full">Pokračovať</a>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    props: ["post"],
    data: function () {
        return {
            favoriteCount: this.post.favoritesCount,
            isFavorite: this.post.isFavorited,
            showLoginForm: false,
        };
    },

    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },

        /*
         * Popis aj vzhľad boli predtým v jedinej computed, ktorá popri
         * počítaní prepisovala data a posielala flash správy — teda menila
         * stav pri každom prekreslení. Tu už len čítajú.
         */
        label: function () {
            return this.isFavorite ? "Odporúčané" : "Odporúčať príspevok";
        },

        title: function () {
            return this.isFavorite
                ? "Zrušiť odporúčanie"
                : "Odporučiť tento príspevok ostatným";
        },

        buttonClass: function () {
            return this.isFavorite
                ? "border-[color:var(--ar-accent)] bg-[color:var(--ar-accent-soft)] text-[color:var(--ar-accent)]"
                : "ar-btn--quiet";
        },
    },

    methods: {
        pressRecomendedButton: function () {
            if (!this.signedIn) {
                this.showLoginForm = true;
                return;
            }

            this.isFavorite = !this.isFavorite;
            this.favoriteCount += this.isFavorite ? 1 : -1;

            axios.put("/favorites/" + this.post.id, {
                model: "Post",
                model_id: this.post.id,
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
