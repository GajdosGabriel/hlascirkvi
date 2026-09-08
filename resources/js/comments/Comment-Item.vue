<template>
    <div class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
        <div class="flex items-start gap-3">
            <img
                :src="comment.user_avatar"
                :alt="comment.user_name"
                class="h-10 w-10 shrink-0 rounded-full object-cover"
            />

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <strong
                            class="block truncate text-sm text-[color:var(--ar-ink)]"
                            v-text="comment.user_name"
                        ></strong>
                        <span class="text-xs text-gray-400">{{ comment.datetime }}</span>
                    </div>

                    <favorite :reply="comment"></favorite>
                </div>

                <!-- Nezverejnený komentár vidí len jeho autor; trieda redText,
                     ktorou sa to označovalo, nemala v žiadnom štýle telo. -->
                <p
                    v-if="! editComment && waiting"
                    class="mt-2 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800"
                >
                    <i class="far fa-clock mr-1.5"></i> Váš komentár čaká na schválenie.
                </p>

                <p
                    v-else-if="! editComment"
                    class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-gray-700"
                >
                    {{ comment.body }}
                </p>

                <div v-if="editComment" class="mt-2">
                    <textarea
                        class="ar-field"
                        v-model="draft"
                        rows="3"
                        placeholder="Pridajte nový komentár ..."
                        required
                    ></textarea>

                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button" class="ar-btn ar-btn--quiet" @click.prevent="cancelEdit">
                            Zrušiť
                        </button>
                        <button type="button" class="ar-btn ar-btn--accent" @click="updateComment">
                            Uložiť
                        </button>
                    </div>
                </div>

                <div v-if="canUpdate" class="mt-2 flex gap-4 text-xs text-gray-400">
                    <dropdown-slot align="left">
                        <button type="button" class="hover:text-[color:var(--ar-accent)]" @click="startEdit">
                            Upraviť
                        </button>
                        <button type="button" class="hover:text-[color:var(--ar-accent)]" @click.prevent="destroy()">
                            Zmazať
                        </button>

                    </dropdown-slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Favorite from "./Favorite.vue";

export default {
    props: ["comment"],
    components: { Favorite },
    data: function () {
        return {
            editComment: false,
            // Úprava beží nad kópiou; v-model priamo nad prop by prepisoval
            // dáta rodiča ešte pred tým, než ich server prijme.
            draft: this.comment.body,
        };
    },

    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },

        canUpdate: function () {
            if (this.editComment) {
                return false;
            }
            return this.authorize(
                (user) => user.id == 1 || this.comment.user.id == user.id
            );
        },

        waiting: function () {
            return this.comment.deleted_at != null;
        },
    },

    methods: {
        startEdit: function () {
            this.draft = this.comment.body;
            this.editComment = true;
        },

        cancelEdit: function () {
            this.editComment = false;
        },

        destroy: function () {
            if (!window.confirm("Skutočne vymazať!")) {
                return;
            }
            axios.delete("/api/comments/" + this.comment.id);

            // Bolo to jQuery $(el).fadeOut(300). Kvôli tomuto jedinému volaniu
            // sa do bundlu ťahalo celé jQuery.
            this.$el.style.transition = "opacity 300ms";
            this.$el.style.opacity = 0;
            setTimeout(() => this.$emit("deleted", this.comment.id), 300);
        },

        updateComment: function () {
            var body = this.draft;

            axios.put(this.comment.url.update, { ...this.comment, body: body });

            this.comment.body = body;
            this.editComment = false;
        },
    },
};
</script>
