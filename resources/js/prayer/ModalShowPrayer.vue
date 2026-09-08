<template>
    <div
        v-if="prayer"
        class="fixed inset-0 z-40 overflow-y-auto bg-[rgba(16,24,40,.55)] p-4 sm:p-6"
        @click.self="toggle"
    >
        <div class="flex min-h-full items-center justify-center" @click.self="toggle">
            <div class="ar-modal" role="dialog" aria-modal="true" aria-labelledby="prayer-show-title">
                <header class="ar-modal__head">
                    <div class="min-w-0">
                        <p class="ar-kicker">Modlitebná prosba</p>
                        <h3 id="prayer-show-title" class="ar-display mt-1 text-lg font-extrabold leading-snug">
                            {{ prayer.title || "Prosba o modlitbu" }}
                        </h3>
                    </div>

                    <button type="button" class="ar-modal__close" title="Zavrieť" @click="toggle">
                        <i class="fas fa-times"></i>
                    </button>
                </header>

                <div class="px-5 py-5">
                    <div class="flex items-start gap-3">
                        <span class="ar-prayer__mark">
                            <img :src="'/images/prayed_hand.png'" alt="" />
                        </span>

                        <div class="min-w-0">
                            <p class="text-sm">
                                <span class="font-semibold">{{ prayer.user_name }}</span>
                                <span class="text-gray-500"> prosí o modlitbu</span>
                            </p>
                            <p class="ar-prayer__meta mt-0.5">
                                {{ prayer.created_at_humans }} · {{ prayingLabel }}
                            </p>
                        </div>
                    </div>

                    <p class="mt-4 whitespace-pre-line text-[.95rem] leading-relaxed text-[color:var(--ar-ink-soft)]">
                        {{ prayer.body }}
                    </p>

                    <form
                        class="mt-5 border-t border-[color:var(--ar-line)] pt-4"
                        @submit.prevent="saveFavorites"
                    >
                        <div v-if="!isAuth">
                            <label class="ar-label" for="prayer-favorite-email">Váš e-mail</label>
                            <input
                                id="prayer-favorite-email"
                                v-model="email"
                                type="email"
                                class="ar-field"
                                name="email"
                                placeholder="meno@email.sk"
                                required
                            />
                            <p class="ar-hint">
                                Vložením e-mailu sa pripojíte k modlitbe. E-mail sa nikde
                                nezverejňuje.
                            </p>
                        </div>

                        <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" class="ar-btn ar-btn--quiet" @click="toggle">
                                Zavrieť
                            </button>
                            <button type="submit" class="ar-btn ar-btn--accent">
                                <i class="fas fa-praying-hands"></i>
                                Pripojiť sa k modlitbe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { bus } from "../app";
import { filterMixin } from "../mixins/filtersMixin";

export default {
    mixins: [filterMixin],

    data: function () {
        return {
            prayer: "",
            email: "",
        };
    },

    computed: {
        isAuth() {
            return window.App.signedIn;
        },

        prayingLabel() {
            const count = this.prayer.favoritesCount;

            if (!count) return "zatiaľ sa nepridal nikto";
            if (count === 1) return "modlí sa 1 človek";
            if (count < 5) return `modlia sa ${count} ľudia`;

            return `modlí sa ${count} ľudí`;
        },
    },

    created: function () {
        bus.$on("passToModalPrayer", (prayer) => {
            this.prayer = prayer;
        });

        // Okno visí v komponente na celú životnosť stránky, poslucháč sa
        // preto neodpája — zaniká až s ňou.
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") this.toggle();
        });
    },

    methods: {
        toggle: function () {
            this.prayer = "";
        },

        saveFavorites: function () {
            axios
                .put("/favorites/" + this.prayer.id, {
                    model: "Prayer",
                    model_id: this.prayer.id,
                    email: this.email,
                })
                .then(() => {
                    this.prayer = "";
                    this.email = "";
                });
        },
    },
};
</script>
