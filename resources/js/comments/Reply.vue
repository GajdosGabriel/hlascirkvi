<template>
    <div
        class="text-gray-600 mb-6 shadow-md border-2 border-gray-100 rounded-md"
    >
        <div class="flex justify-between py-2 border-b border-gray-200">
            <strong v-text="getShortName"></strong>

            <div class="flex text-sm text-gray-500" v-if="canUpdate">
                <a class="mr-2" href="#" @click.prevent="destroy()">Zmazať</a>
                <a class="mr-2" href="#" @click.prevent="editComment = true">
                    Upraviť
                </a>
            </div>

            <favorite :reply="data"></favorite>
        </div>

        <div
            v-if="!editComment"
            v-text="cakanaschvalenie"
            class="px-3 mb-2"
            :class="redText"
        ></div>

        <div v-if="editComment" class="p-3">
            <textarea
                class="w-full p-2 border-2 border-gray-400 rounded-md"
                v-model="body"
                rows="3"
                placeholder="Pridajte nový komentár ..."
                required
            ></textarea>
            <button
                class="btn btn-small mt-2 bg-blue-300 w-full hover:bg-gray-300"
                @click.prevent="updateComment"
                v-if="editComment"
            >
                Uložiť
            </button>
        </div>
    </div>
</template>

<script>
import Favorite from "./Favorite.vue";

export default {
    props: ["data"],
    components: { Favorite },
    data: function() {
        return {
            editComment: false,
            body: this.data.body
        };
    },

    computed: {
        signedIn: function() {
            return window.App.signedIn;
        },

        canUpdate: function() {
            return this.authorize(
                user => this.data.user_id == window.App.user.id
            );
        },

        getShortName: function() {
            return (
                this.data.user.first_name +
                " " +
                this.data.user.last_name.charAt(0) +
                "."
            );
        },

        cakanaschvalenie: function() {
            if (this.data.deleted_at != null) {
                return "Váš komentár čaká na schválenie.";
            }
            return this.data.body;
        },

        redText: function() {
            return this.data.deleted_at != null ? "redText" : "";
        }
    },

    methods: {
        destroy: function() {
            axios.delete("/comments/" + this.data.id);

            $(this.$el).fadeOut(300, () => {
                this.$emit("deleted", this.data.id);
            });
        },

        updateComment: function() {
            axios
                .put("/comments/" + this.data.id, {
                    body: this.body
                })
                .then(response => {
                    this.body = response.data.body;
                });
            this.editComment = false;
        }
    }
};
</script>

<style>
.redText {
    color: red;
}
</style>
