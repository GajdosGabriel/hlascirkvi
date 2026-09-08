<template>
    <button
        type="button"
        @click="store"
        :title="reply.is_favorited ? 'Hlas ste už dali' : 'Hlasovať za komentár'"
        class="flex shrink-0 items-center gap-1 text-xs text-gray-400 transition-colors hover:text-[color:var(--ar-accent)]"
    >
        <span
            class="flex h-7 w-7 items-center justify-center rounded-full"
            :class="replyClass"
        >
            <i class="fas fa-heart"></i>
        </span>
        <span class="tabular-nums">{{ reply.favorites_count }}</span>
    </button>
</template>

<script>
// bus sa tu volal bez importu — kliknutie na srdiečko končilo výnimkou
// ReferenceError a hlas sa síce uložil, ale hláška sa nikdy nezobrazila.
import { bus } from "../app";

export default {
    props: ["reply"],
    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },

        replyClass: function () {
            return this.reply.is_favorited
                ? "bg-[color:var(--ar-accent)] text-white"
                : "bg-[color:var(--ar-paper-deep)]";
        },
    },

    methods: {
        store: function () {
            if (!this.signedIn) {
                return bus.$emit("flash", {
                    body: "Najprv sa prihláste.",
                    type: "danger",
                });
            }

            axios.put("/favorites/" + this.reply.id, {
                model: "Comment",
                model_id: this.reply.id,
            });

            this.reply.is_favorited = !this.reply.is_favorited;
            this.reply.favorites_count += this.reply.is_favorited ? 1 : -1;

            bus.$emit("flash", { body: "Hlas komentáru je uložený." });
        },
    },
};
</script>
