<template>
    <article
        class="discussion-comment"
        :class="{ 'discussion-comment--reply': isReply }"
    >
        <div class="discussion-comment__layout">
            <!-- Avatar z YouTube sa bez no-referrer občas nenačíta a starý
                 alebo zmazaný avatar nahradí predvolený obrázok. -->
            <img
                :src="comment.user_avatar || '/images/avatar.png'"
                :alt="comment.user_name"
                :class="isReply ? 'h-8 w-8 sm:h-9 sm:w-9' : 'h-10 w-10 sm:h-11 sm:w-11'"
                class="discussion-comment__avatar shrink-0 rounded-full object-cover"
                referrerpolicy="no-referrer"
                loading="lazy"
                @error="avatarFailed"
            />

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 pr-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <strong
                                class="discussion-comment__name"
                                v-text="comment.user_name"
                            ></strong>
                        </div>
                        <span class="discussion-comment__date">{{ comment.datetime }}</span>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5">
                        <dropdown-slot v-if="canUpdate" label="Spravovať komentár">
                            <button type="button" @click="startEdit">
                                <i class="ph ph-pencil-simple" aria-hidden="true"></i>
                                Upraviť
                            </button>
                            <hr class="ui-dropdown__divider">
                            <button type="button" class="ui-dropdown__item--danger" @click.prevent="confirmingDelete = true">
                                <i class="ph ph-trash" aria-hidden="true"></i>
                                Zmazať
                            </button>
                        </dropdown-slot>
                    </div>
                </div>

                <div
                    v-if="confirmingDelete"
                    class="mt-2 flex flex-wrap items-center gap-2 rounded-md bg-red-50 px-3 py-2 text-sm text-red-800"
                    role="alertdialog"
                >
                    <span class="grow">
                        Zmazať {{ isReply ? 'odpoveď' : 'komentár' }} od <strong>{{ comment.user_name }}</strong>? Nedá sa vrátiť späť.
                    </span>
                    <button type="button" class="ar-btn ar-btn--quiet" :disabled="deleting" @click.prevent="confirmingDelete = false">
                        Ponechať
                    </button>
                    <button type="button" class="ar-btn ar-btn--accent" :disabled="deleting" @click.prevent="destroy">
                        Zmazať
                    </button>
                </div>

                <!-- Nezverejnený komentár vidí len jeho autor; trieda redText,
                     ktorou sa to označovalo, nemala v žiadnom štýle telo. -->
                <p
                    v-if="! editComment && waiting"
                    class="mt-2 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800"
                >
                    <i class="ph ph-clock mr-1.5"></i> Váš komentár nie je zverejnený. Podrobnosti o automatickej kontrole dostanete e-mailom.
                </p>

                <p
                    v-else-if="! editComment"
                    class="discussion-comment__body"
                >
                    <span v-if="comment.reply_to_name" class="font-semibold">
                        <i class="ph ph-arrow-bend-up-left" aria-hidden="true"></i>
                        {{ comment.reply_to_name }}:
                    </span>
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
                <div v-if="! editComment && ! waiting" class="discussion-comment__actions">
                    <favorite :reply="comment"></favorite>

                    <button
                        type="button"
                        class="discussion-action"
                        :aria-expanded="replyTo && replyTo.id === comment.id ? 'true' : 'false'"
                        @click="reply(comment)"
                    >
                        <i class="ph ph-arrow-bend-up-left"></i> Odpovedať
                    </button>
                </div>

                <button
                    v-if="!isReply && !waiting && replies.length"
                    type="button"
                    class="discussion-thread-toggle"
                    :aria-expanded="repliesExpanded"
                    @click="repliesExpanded = !repliesExpanded"
                >
                    <i class="ph" :class="repliesExpanded ? 'ph-caret-up' : 'ph-caret-down'" aria-hidden="true"></i>
                    {{ repliesExpanded ? 'Skryť odpovede' : 'Zobraziť odpovede' }}
                    <span>{{ replies.length }}</span>
                </button>
                <div
                    v-if="!isReply && !waiting && ((replies.length && repliesExpanded) || replyTo)"
                    class="discussion-thread"
                >
                    <comment-item
                        v-for="item in (repliesExpanded ? replies : [])"
                        :key="item.id"
                        :comment="item"
                        :post="post"
                        is-reply
                        @reply="reply"
                        @deleted="removeReply"
                    ></comment-item>

                    <new-reply
                        v-if="replyTo"
                        ref="replyForm"
                        :key="replyTo.id"
                        :post="post"
                        :parent-id="replyTo.id"
                        :reply-to-name="replyTo.id === comment.id ? '' : replyTo.user_name"
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
            repliesExpanded: true,
            confirmingDelete: false,
            deleting: false,
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
            return ! this.comment.published;
        },

        replies: function () {
            return this.comment.replies || [];
        },
    },

    methods: {
        avatarFailed: function (event) {
            if (event.target.dataset.fallback) {
                return;
            }
            event.target.dataset.fallback = "1";
            event.target.src = "/images/avatar.png";
        },

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
            // Opakované kliknutie formulár nezavrie (stratil by sa rozpísaný
            // text), len naň vráti fokus; zatvára ho „Zrušiť".
            this.replyTo = target;
            this.$nextTick(() => this.$refs.replyForm?.focus());
        },

        addReply: function (reply) {
            if (!this.comment.replies) {
                this.comment.replies = [];
            }
            this.comment.replies.push(reply);
            this.repliesExpanded = true;
            this.replyTo = null;

            bus.$emit("flash", { body: reply.published ? "Odpoveď je pridaná!" : "Odpoveď bola skrytá automatickou kontrolou. Podrobnosti dostanete e-mailom." });
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
            this.deleting = true;

            // Komentár sa skryje až po úspešnej odpovedi; pri 403/500 ostáva
            // na obrazovke a používateľ dostane hlásenie.
            axios
                .delete(this.comment.url.destroy)
                .then(() => {
                    // Bolo to jQuery $(el).fadeOut(300). Kvôli tomuto jedinému volaniu
                    // sa do bundlu ťahalo celé jQuery.
                    this.$el.style.transition = "opacity 300ms";
                    this.$el.style.opacity = 0;
                    setTimeout(() => this.$emit("deleted", this.comment.id), 300);
                })
                .catch((error) => {
                    this.deleting = false;
                    this.confirmingDelete = false;
                    bus.$emit("flash", {
                        body: error.response?.data?.message || "Komentár sa nepodarilo zmazať.",
                        type: "danger",
                    });
                });
        },

        updateComment: function () {
            var body = this.draft;

            // Text sa prepisoval hneď, bez ohľadu na odpoveď servera — pri
            // odmietnutí (napr. menej ako 3 znaky) tak komentár vyzeral uložený,
            // no po obnovení stránky mal starý text.
            axios
                .put(this.comment.url.update, { body: body })
                .then((response) => {
                    this.comment.published = response.data.published;
                    this.comment.body = body;
                    this.editComment = false;
                })
                .catch((error) => {
                    const errors = error.response?.data?.errors;
                    bus.$emit("flash", {
                        body: errors ? Object.values(errors).flat()[0] : "Komentár sa nepodarilo uložiť.",
                        type: "danger",
                    });
                });
        },
    },
};
</script>
