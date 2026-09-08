<template>
    <article
        class="ar-card relative rounded-xl px-5 py-4"
        :class="{
            'ar-prayer--ok': prayer.fulfilled_at,
            'cursor-pointer': !prayer.fulfilled_at
        }"
        @click="passToModalShow"
    >
        <div class="flex items-start gap-3">
            <span class="ar-prayer__mark">
                <img :src="'/images/prayed_hand.png'" alt="" />
            </span>

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="ar-display min-w-0 text-[.95rem] font-bold leading-snug">
                        {{ prayer.title || "Prosba o modlitbu" }}
                    </h3>

                    <!-- Ponuka vlastníka -->
                    <div class="relative shrink-0" v-if="canManage">
                        <button
                            type="button"
                            class="flex h-6 w-6 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            title="Možnosti"
                            @click.stop="toggle"
                        >
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <div v-if="open" class="ar-prayer__menu">
                            <button type="button" @click.stop="passToModalEdit">
                                Upraviť
                            </button>
                            <button type="button" @click.stop="prayerDestroy">
                                Zmazať
                            </button>
                        </div>
                    </div>
                </div>

                <p class="ar-prayer__meta mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span class="font-semibold text-[color:var(--ar-ink-soft)]">
                        {{ prayer.user_name }}
                    </span>
                    <span aria-hidden="true">·</span>
                    <time :datetime="prayer.created_at" :title="prayer.created_at | dateTime">
                        {{ prayer.created_at_humans }}
                    </time>
                    <template v-if="prayer.organization_title">
                        <span aria-hidden="true">·</span>
                        <span>{{ prayer.organization_title }}</span>
                    </template>
                    <span v-if="prayer.fulfilled_at" class="ar-badge ar-badge--ok">
                        <i class="fas fa-check"></i> Vypočutá
                    </span>
                </p>
            </div>
        </div>

        <p
            class="mt-3 whitespace-pre-line text-sm leading-relaxed text-[color:var(--ar-ink-soft)]"
            :class="{ 'ar-clamp-3': !expanded }"
        >
            {{ prayer.body }}
        </p>

        <!-- Vypočuté prosby sa neotvárajú v okne, dlhší text preto rozbalí
             samotná karta. -->
        <button
            v-if="prayer.fulfilled_at && isLong"
            type="button"
            class="mt-2 text-xs font-semibold text-[color:var(--ar-accent)] hover:underline"
            @click.stop="expanded = !expanded"
        >
            {{ expanded ? "Zbaliť" : "Čítať celé" }}
        </button>

        <div
            v-if="!prayer.fulfilled_at"
            class="mt-4 flex items-center justify-between gap-3 border-t border-[color:var(--ar-line)] pt-3"
        >
            <span class="ar-prayer__meta">{{ prayingLabel }}</span>
            <favorites-count :prayer="prayer"></favorites-count>
        </div>
    </article>
</template>

<script>
import favoritesCount from "./components/favoritesCount";
import { bus } from "../app";
import Axios from "axios";
import { filterMixin } from "../mixins/filtersMixin";
import { createdMixin } from "../mixins/createdMixin";

export default {
    props: ["prayer"],
    mixins: [filterMixin, createdMixin],
    components: { favoritesCount },

    data() {
        return {
            open: false,
            expanded: false,
            authUser: window.App.user,
        };
    },

    computed: {
        canManage() {
            return this.authUser && this.authUser.id == this.prayer.organization_id;
        },

        isLong() {
            return (this.prayer.body || "").length > 180;
        },

        // Slovenčina skloňuje počty inak než angličtina, preto celá veta
        // namiesto skladania „počet + slovo".
        prayingLabel() {
            const count = this.prayer.favoritesCount;

            if (!count) return "Zatiaľ sa nepridal nikto";
            if (count === 1) return "Modlí sa 1 človek";
            if (count < 5) return `Modlia sa ${count} ľudia`;

            return `Modlí sa ${count} ľudí`;
        },
    },

    methods: {
        passToModalShow() {
            if (this.prayer.fulfilled_at) return;
            bus.$emit("passToModalPrayer", this.prayer);
        },

        passToModalEdit() {
            this.open = false;
            bus.$emit("passToModalEdit", this.prayer);
        },

        toggle() {
            this.open = !this.open;
        },

        prayerDestroy() {
            if (!window.confirm("Skutočne chcete zmazať položku?")) {
                return;
            }
            Axios.delete("/api/prayers/" + this.prayer.id).then(() => {
                this.open = false;
                window.location.reload();
            });
        },
    },
};
</script>
