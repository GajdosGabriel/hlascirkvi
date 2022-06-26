<template>
    <div
        class="text-gray-600 mb-6 shadow-md border-2 border-gray-100 rounded-md"
    >
        <div
            class="flex justify-between py-2 border-b border-gray-200 pl-3 pr-3 bg-gray-100"
        >
            <strong
                v-if="comment.user_name"
                v-text="comment.user_name"
            ></strong>
            <strong v-else v-text="fullName"></strong>

            <favorite :reply="comment"></favorite>
        </div>

        <div class="flex">
            <img
                v-if="!editComment"
                :src="comment.user_avatar"
                class="h-14 py-2 rounded-full ml-2"
            />

            <div v-if="!editComment" class="px-3 mb-2" :class="redText">
                {{ cakanaschvalenie }}
            </div>
        </div>

        <div v-if="editComment" class="p-3">
            <textarea
                class="w-full p-2 border-2 border-gray-400 rounded-md"
                v-model="comment.body"
                rows="2"
                placeholder="Pridajte nový komentár ..."
                required
            ></textarea>
            <div class="flex justify-between mt-2" v-if="editComment">
                <button
                    class="btn btn-small"
                    @click.prevent="editComment = false"
                >
                    Zrušiť
                </button>

                <button class="btn btn-primary" @click.prevent="updateComment">
                    Uložiť
                </button>
            </div>
        </div>
        <div class="text-right text-xs px-2 mb-2" v-if="canUpdate">
            <span
                class="hover:bg-gray-200 px-2 py-1 rounded-md cursor-pointer"
                @click.prevent="editComment = true"
            >
                Upraviť
            </span>
            <span
                class="hover:bg-gray-200 px-2 py-1 rounded-md cursor-pointer"
                @click.prevent="destroy()"
                >Zmazať</span
            >
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
                (user) =>
                    window.App.user.id == 1 ||
                    this.comment.user.id == window.App.user.id
            );
        },

        fullName: function () {
            return this.comment.user_name
                ? this.comment.user_name
                : this.comment.user.first_name +
                      " " +
                      this.comment.user.last_name;
        },

        cakanaschvalenie: function () {
            if (this.comment.deleted_at != null) {
                return "Váš komentár čaká na schválenie.";
            }
            return this.comment.body;
        },

        redText: function () {
            return this.comment.deleted_at != null ? "redText" : "";
        },
    },

    methods: {
        destroy: function () {
            if (!window.confirm("Skutočne vymazať!")) {
                return;
            }
            axios.delete(
                "/api/posts/" +
                    this.comment.commentable_id +
                    "/comments/" +
                    this.comment.id
            );

            $(this.$el).fadeOut(300, () => {
                this.$emit("deleted", this.comment.id);
            });
        },

        updateComment: function () {
            axios
                .put(
                    "/api/posts/" +
                        this.comment.commentable_id +
                        "/comments/" +
                        this.comment.id,
                    this.comment
                )
                .then((response) => {
                    this.comment = response.comment;
                });
                
            this.editComment = false;
        },
    },
};
</script>
