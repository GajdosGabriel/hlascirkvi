<template>
    <div>
        <div class="px-3">
            <h4 @click="showForm" class="mb-4 font-semibold mt-2">
                Komentáre <i class="far fa-comment-dots"></i>
                <span style="font-size: 70%; cursor: pointer">pridať nový</span>
            </h4>

            <div v-for="reply in comments" :key="reply.id">
                <reply :data="reply" @deleted="remove(reply.id)"></reply>
            </div>

            <div class="flex justify-end">
                <p
                    v-text="formOpenClose"
                    @click="showForm"
                    class="mb-1"
                    style="font-size: 70%; cursor: pointer"
                ></p>
            </div>

            <new-comment v-if="show" :post="post" @newComment="addNewComment" />
        </div>
    </div>
</template>

<script>
import { bus } from "../app";
import Reply from "./Comment-Item.vue";
import NewComment from "./NewReply.vue";
export default {
    props: ["post"],
    components: { Reply, NewComment },
    data: function() {
        return {
            show: false,
            comments: this.post.comments
        };
    },

    computed: {
        signedIn: function() {
            return window.App.signedIn;
        },
        countComments: function() {
            return this.items.length + " Pozrieť diskusiu";
        },

        formOpenClose: function() {
            return this.show ? "Zavrieť" : "nový komentár";
        }
    },

    methods: {
        showForm: function() {
            this.show = !this.show;
        },
        remove: function(id) {
            this.data.splice(id, 1);
            bus.$emit("flash", { body: "Komentár je zmazaný", type: "danger" });
        },

        addNewComment: function(comment) {
            this.comments.push(comment);

            bus.$emit("flash", { body: "Komentár je pridaný!" });
        }
    }
};
</script>
