<template>
    <article
        :class="isReply
            ? 'border-l-2 border-[color:var(--ar-line)] pl-3 sm:pl-4'
            : 'group rounded-xl border border-[color:var(--ar-line)] bg-white p-4 shadow-sm transition hover:border-gray-300 hover:shadow-md sm:p-5'"
    >
        <div class="flex items-start gap-3 sm:gap-4">
            <img
                :src="comment.user_avatar"
                :alt="comment.user_name"
                :class="isReply ? 'h-8 w-8 sm:h-9 sm:w-9' : 'h-10 w-10 sm:h-11 sm:w-11'"
                class="shrink-0 rounded-full bg-[color:var(--ar-paper-deep)] object-cover ring-2 ring-white"
            />

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 pr-2">
                        <strong
                            class="block truncate text-sm text-[color:var(--ar-ink)]"
                            v-text="comment.user_name"
                        ></strong>
                        <span class="mt-0.5 block text-xs text-gray-400">{{ comment.datetime }}</span>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5">
                        <dropdown-slot v-if="canUpdate">
                            <button type="button" @click="startEdit">
                                <i class="far fa-edit w-4 text-center text-gray-400"></i>
                                Upraviť
                            </button>
                            <button type="button" class="ar-act--danger" @click.prevent="destroy()">
                                <i class="far fa-trash-alt w-4 text-center text-gray-400"></i>
                                Zmazať
                            </button>
                        </dropdown-slot>
                    </div>
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
                    class="mt-3 whitespace-pre-line break-words text-sm leading-relaxed text-gray-700"
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

                <!-- Na nezverejnený komentár sa odpovedať ani reagovať nedá —
                     server by odpoveď odmietol. -->
                <div v-if="! editComment && ! waiting" class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <favorite :reply="comment"></favorite>

                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 transition-colors hover:text-[color:var(--ar-accent)]"
                        @click="reply(comment)"
                    >
                        <i class="fas fa-reply"></i> Odpovedať
                    </button>
                </div>

                <div
                    v-if="! isReply && (replies.length || replyTo)"
                    class="mt-4 space-y-4"
                >
                    <comment-item
                        v-for="item in replies"
                        :key="item.id"
                        :comment="item"
                        :post="post"
                        is-reply
                        @reply="reply"
                        @deleted="removeReply"
                    ></comment-item>

                    <new-reply
                        v-if="replyTo"
                        :key="replyTo.id"
                        :post="post"
                        :parent-id="replyTo.id"
                        :initial-body="replyTo.id === comment.id ? '' : '@' + replyTo.user_name + ' '"
                        @newComment="addReply"
                        @cancel="replyTo = null"
                    />
                </div>
            </div>
        </div>
    </article>
</template>

<script>
import { bus } from "../eventBus";
import Favorite from "./Favorite.vue";
import NewReply from "./NewReply.vue";

export default {
    name: "CommentItem",
    props: {
        comment: { required: true },
        post: { required: true },
        // Odpovede sa zobrazujú vnútri hlavného komentára a ďalej sa nevnárajú.
        isReply: { type: Boolean, default: false },
    },
    emits: ["deleted", "reply"],
    components: { Favorite, NewReply },
    data: function () {
        return {
            editComment: false,
            // Úprava beží nad kópiou; v-model priamo nad prop by prepisoval
            // dáta rodiča ešte pred tým, než ich server prijme.
            draft: this.comment.body,
            // Komentár (hlavný alebo odpoveď), na ktorý je otvorený formulár.
            replyTo: null,
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

        replies: function () {
            return this.comment.replies || [];
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

        // Odpoveď vždy otvára formulár v hlavnom komentári; odpoveď na
        // odpoveď server zavesí pod ten istý hlavný komentár.
        reply: function (target) {
            if (this.isReply) {
                return this.$emit("reply", target);
            }
            this.replyTo = this.replyTo && this.replyTo.id === target.id ? null : target;
        },

        addReply: function (reply) {
            if (!this.comment.replies) {
                this.comment.replies = [];
            }
            this.comment.replies.push(reply);
            this.replyTo = null;

            bus.$emit("flash", { body: "Odpoveď je pridaná!" });
        },

        removeReply: function (id) {
            var index = this.replies.findIndex(function (item) {
                return item.id === id;
            });

            if (index !== -1) {
                this.comment.replies.splice(index, 1);
            }

            bus.$emit("flash", { body: "Odpoveď je zmazaná", type: "danger" });
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
