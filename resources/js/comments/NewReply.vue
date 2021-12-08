<template>
    <form @submit.prevent="storeComment">
        <div class="form-group">
            <input
                class="w-full p-2 border-2 form-control border-gray-400 rounded-md"
                v-model="body"
                placeholder="Pridajte komentár ..."
                required
            />
        </div>

        <div class="flex justify-between">
            <div v-if="! signedIn" class="max-w-36 ">
                <input
                    required
                    type="email"
                    v-model="email"
                    class="p-1 px-2 border-2 border-gray-300 rounded-md"
                    placeholder="Váš email"
                />

                <div class="text-xs block">Email nebude nikde zverejnený.</div>
            </div>

            <div class="flex w-full justify-end">
                <button class="btn btn-primary hover:bg-blue-600">
                    Uložiť
                </button>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    props: ["post"],
    data: function() {
        return {
            body: "",
            email: "",
            endpoint: "/comment" + location.pathname,
            //                endpoint:'/store/comment/xx/qwe' + location.pathname
        };
    },

    computed: {
        signedIn: function() {
            return window.App.signedIn;
        }
    },

    methods: {

        sendComment: function() {
            if (!this.signedIn) {
                return this.toggle();
            }
            this.storeComment();
        },

        storeComment: function() {
            axios
                .post("/api/posts/" + this.post.id + "/comments", {
                    body: this.body,
                    email: this.email
                })
                .then(({ data }) => {
                    this.body = "";
                    this.email = "";
                    this.$emit("newComment", data);
                    this.modal = false;
                });
        }


    }
};
</script>

<style>
.form-group {
    padding: 0rem;
}
</style>
