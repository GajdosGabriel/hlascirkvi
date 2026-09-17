<template>
    <!--
        Uložiť na neskôr. Súkromná záložka, na rozdiel od odporúčania bez
        počtu. Obal je `relative`, aby ponuka prihlásenia visela pod tlačidlom.
    -->
    <div class="relative inline-flex">
        <button
            type="button"
            @click.stop="toggle"
            :title="saved ? 'Odobrať z uložených' : 'Uložiť a pozrieť neskôr'"
            :aria-pressed="saved ? 'true' : 'false'"
            :class="saved
                ? 'border-[color:var(--ar-accent)] bg-[color:var(--ar-accent-soft)] text-[color:var(--ar-accent)]'
                : 'ar-btn--quiet'"
            class="ar-btn"
        >
            <i :class="saved ? 'fas fa-bookmark' : 'far fa-bookmark'"></i>
            {{ saved ? "Uložené" : "Uložiť" }}
        </button>

        <transition name="fade">
            <div
                v-if="open"
                class="absolute right-0 top-full z-20 mt-2 w-56 rounded-lg border border-[color:var(--ar-line)] bg-white p-4 text-center shadow-lg"
            >
                <p class="mb-3 text-sm text-gray-600">
                    Ukladať si príspevky môžu prihlásení čitatelia.
                </p>
                <a href="/login" class="ar-btn ar-btn--accent w-full">Pokračovať</a>
            </div>
        </transition>
    </div>
</template>

<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],
    props: {
        postId: { type: Number, required: true },
        initialSaved: { type: Boolean, default: false },
    },

    data() {
        return {
            saved: this.initialSaved,
            open: false,
            busy: false,
        };
    },

    methods: {
        toggle() {
            if (!window.App.signedIn) {
                this.open = true;
                return;
            }

            if (this.busy) {
                return;
            }

            // Stav sa prepne hneď; server ho potom potvrdí alebo vráti späť.
            const previous = this.saved;
            this.saved = !previous;
            this.busy = true;

            axios
                .put("/post/" + this.postId + "/ulozit")
                .then((response) => (this.saved = response.data.saved))
                .catch(() => (this.saved = previous))
                .finally(() => (this.busy = false));
        },
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
