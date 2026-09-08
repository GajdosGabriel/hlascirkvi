<template>
    <form
        @submit.prevent="storeComment"
        class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4"
    >
        <label class="ar-label" for="comment-body">Váš komentár</label>
        <textarea
            id="comment-body"
            class="ar-field"
            rows="3"
            v-model="body"
            placeholder="Napíšte, čo si o príspevku myslíte…"
            required
        ></textarea>

        <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
            <!-- Bez účtu treba e-mail; pole má zmysel len vtedy, inak by v
                 riadku ostalo prázdne miesto pred tlačidlom. -->
            <div v-if="! signedIn" class="w-full sm:w-64">
                <label class="ar-label" for="comment-email">Váš e-mail</label>
                <input
                    id="comment-email"
                    type="email"
                    class="ar-field"
                    v-model="email"
                    placeholder="meno@example.sk"
                    required
                />
                <p class="ar-hint">E-mail nebude nikde zverejnený.</p>
            </div>

            <button type="submit" class="ar-btn ar-btn--accent ml-auto">
                <i class="far fa-paper-plane"></i> Odoslať komentár
            </button>
        </div>
    </form>
</template>

<script>
export default {
    props: ["post"],
    data: function () {
        return {
            body: "",
            email: "",
        };
    },

    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },
    },

    methods: {
        storeComment: function () {
            axios
                .post("/api/posts/" + this.post.id + "/comments", {
                    body: this.body,
                    email: this.email,
                })
                .then(({ data }) => {
                    this.body = "";
                    this.email = "";
                    this.$emit("newComment", data);
                });
        },
    },
};
</script>
