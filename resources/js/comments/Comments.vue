<template>
    <div>
        <!--
            Nadpis a pridanie stáli v jednom riadku ako "Komentáre pridať nový"
            a druhý prepínač visel ešte pod zoznamom. Teraz je akcia jedna a
            na jednom mieste — vedľa nadpisu, v rovnakej lište ako archív.
        -->
        <div class="ar-rule mb-5">
            <h2 class="ar-display flex items-center gap-2 text-lg font-bold">
                Komentáre
                <span v-if="comments.length" class="ar-badge ar-badge--count">
                    {{ comments.length }}
                </span>
            </h2>

            <button
                type="button"
                @click="showForm"
                :class="show ? 'ar-btn--quiet' : 'ar-btn--accent'"
                class="ar-btn shrink-0"
            >
                <i class="far" :class="show ? 'fa-times-circle' : 'fa-comment-dots'"></i>
                {{ show ? "Zavrieť" : "Pridať komentár" }}
            </button>
        </div>

        <new-reply
            v-if="show"
            :post="post"
            class="mb-6"
            @newComment="addNewComment"
        />

        <div v-if="comments.length" class="space-y-4">
            <comment-item
                v-for="comment in comments"
                :key="comment.id"
                :comment="comment"
                @deleted="remove"
            ></comment-item>
        </div>

        <p v-else-if="! show" class="text-sm text-gray-500">
            Zatiaľ tu nie je žiadny komentár. Napíšte prvý.
        </p>
    </div>
</template>

<script>
import { bus } from "../app";
import CommentItem from "./Comment-Item.vue";
import NewReply from "./NewReply.vue";

export default {
    props: ["post"],
    components: { CommentItem, NewReply },
    data: function () {
        return {
            show: false,
            comments: [],
        };
    },

    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },
    },

    created() {
        axios.get("/api/posts/" + this.post.id + "/comments").then((response) => {
            this.comments = response.data;
        });
    },

    methods: {
        showForm: function () {
            this.show = !this.show;
        },

        // Prišlo id zmazaného komentára, nie jeho poradie — splice(id) mazal
        // od tej pozície v poli, teda spravidla cudzí riadok.
        remove: function (id) {
            var index = this.comments.findIndex(function (comment) {
                return comment.id === id;
            });

            if (index !== -1) {
                this.comments.splice(index, 1);
            }

            bus.$emit("flash", { body: "Komentár je zmazaný", type: "danger" });
        },

        addNewComment: function (comment) {
            this.comments.push(comment);
            this.show = false;

            bus.$emit("flash", { body: "Komentár je pridaný!" });
        },
    },
};
</script>
