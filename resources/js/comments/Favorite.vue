<template>
    <button
        type="button"
        @click="toggle"
        :disabled="pending"
        :aria-pressed="reply.is_favorited ? 'true' : 'false'"
        :title="reply.is_favorited ? 'Zrušiť Páči sa mi to' : 'Páči sa mi to'"
        :class="reply.is_favorited
            ? 'text-[color:var(--ar-accent)]'
            : 'text-gray-500 hover:text-[color:var(--ar-accent)]'"
        class="inline-flex items-center gap-1.5 text-xs font-semibold transition-colors disabled:cursor-wait"
    >
        <i :class="reply.is_favorited ? 'fas' : 'far'" class="fa-thumbs-up"></i>
        <span>Páči sa mi to</span>
        <span v-if="reply.favorites_count" class="tabular-nums font-normal text-gray-400">
            · {{ reply.favorites_count }}
        </span>
    </button>
</template>

<script>
// bus sa tu volal bez importu — kliknutie na srdiečko končilo výnimkou
// ReferenceError a hlas sa síce uložil, ale hláška sa nikdy nezobrazila.
import { bus } from "../eventBus";

export default {
    props: ["reply"],
    data: function () {
        return { pending: false };
    },

    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },
    },

    methods: {
        toggle: function () {
            if (!this.signedIn) {
                return bus.$emit("flash", {
                    body: "Ak chcete označiť Páči sa mi to, prihláste sa.",
                    type: "danger",
                });
            }

            // Stav sa prepne hneď; server potom vráti skutočný stav a počet,
            // pri chybe sa vráti pôvodný.
            var before = {
                is_favorited: this.reply.is_favorited,
                favorites_count: this.reply.favorites_count,
            };

            this.reply.is_favorited = !before.is_favorited;
            this.reply.favorites_count = before.favorites_count + (this.reply.is_favorited ? 1 : -1);
            this.pending = true;

            axios
                .post("/api/comments/" + this.reply.id + "/like")
                .then(({ data }) => {
                    this.reply.is_favorited = data.is_favorited;
                    this.reply.favorites_count = data.favorites_count;
                })
                .catch(() => {
                    this.reply.is_favorited = before.is_favorited;
                    this.reply.favorites_count = before.favorites_count;
                    bus.$emit("flash", { body: "Nepodarilo sa uložiť, skúste to znova.", type: "danger" });
                })
                .finally(() => {
                    this.pending = false;
                });
        },
    },
};
</script>
