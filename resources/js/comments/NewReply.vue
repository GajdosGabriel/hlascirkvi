<template>
    <!-- Komentár bez overenej adresy čaká na potvrdenie z e-mailu
         (App\Models\PendingComment); do zoznamu sa preto zatiaľ nepridá. -->
    <div
        v-if="pending"
        class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4"
        role="status"
    >
        <p class="font-semibold">Ďakujeme, komentár sme prijali.</p>
        <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">
            Zverejní sa, keď potvrdíte svoju e-mailovú adresu. Poslali sme vám na ňu
            odkaz — skontrolujte, prosím, aj priečinok so spamom.
        </p>
        <div class="mt-3 flex justify-end">
            <button type="button" class="ar-btn ar-btn--quiet" @click="closeNotice">Rozumiem</button>
        </div>
    </div>

    <form
        v-else
        @submit.prevent="storeComment"
        class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4"
    >
        <label class="ar-label" :for="uid + '-body'">
            {{ parentId ? "Vaša odpoveď" : "Váš komentár" }}
        </label>
        <textarea
            :id="uid + '-body'"
            ref="body"
            class="ar-field"
            rows="3"
            v-model="body"
            :placeholder="parentId ? 'Napíšte odpoveď…' : 'Napíšte, čo si o príspevku myslíte…'"
            required
        ></textarea>
        <p v-for="error in errors" :key="error" class="mt-1 text-sm font-semibold text-red-700" role="alert">{{ error }}</p>

        <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
            <!-- Bez účtu treba e-mail; pole má zmysel len vtedy, inak by v
                 riadku ostalo prázdne miesto pred tlačidlom. -->
            <div v-if="! signedIn" class="w-full sm:w-64">
                <label class="ar-label" :for="uid + '-email'">Váš e-mail</label>
                <input
                    :id="uid + '-email'"
                    type="email"
                    class="ar-field"
                    v-model="email"
                    placeholder="meno@example.sk"
                    required
                />
                <p class="ar-hint">
                    E-mail nebude nikde zverejnený. Komentár sa zobrazí až po jeho potvrdení.
                </p>
            </div>

            <div class="ml-auto flex gap-2">
                <button
                    v-if="parentId"
                    type="button"
                    class="ar-btn ar-btn--quiet"
                    @click="$emit('cancel')"
                >
                    Zrušiť
                </button>
                <button type="submit" class="ar-btn ar-btn--accent">
                    <i class="far fa-paper-plane"></i>
                    {{ parentId ? "Odoslať odpoveď" : "Odoslať komentár" }}
                </button>
            </div>
        </div>
    </form>
</template>

<script>
var counter = 0;

export default {
    props: {
        post: { required: true },
        // Komentár, na ktorý sa odpovedá; bez neho ide o nový hlavný komentár.
        parentId: { default: null },
        initialBody: { type: String, default: "" },
    },
    data: function () {
        return {
            // Formulárov je na stránke naraz viac, id pre <label for> musia byť jedinečné.
            uid: "comment-form-" + ++counter,
            body: this.initialBody,
            email: "",
            errors: [],
            // Server komentár prijal, ale zverejní ho až po overení e-mailu.
            pending: false,
        };
    },

    mounted() {
        if (this.parentId) {
            var el = this.$refs.body;
            el.focus();
            el.setSelectionRange(el.value.length, el.value.length);
        }
    },

    computed: {
        signedIn: function () {
            return window.App.signedIn;
        },
    },

    methods: {
        // Odpoveď formulár zavrie (ako Zrušiť), hlavný komentár ho ukáže znova.
        closeNotice: function () {
            this.pending = false;
            if (this.parentId) this.$emit("cancel");
        },

        storeComment: function () {
            axios
                .post("/api/posts/" + this.post.id + "/comments", {
                    body: this.body,
                    email: this.email,
                    parent_id: this.parentId,
                })
                .then(({ data }) => {
                    this.body = "";
                    this.email = "";
                    this.errors = [];

                    if (data.pending) {
                        this.pending = true;
                        return;
                    }

                    this.$emit("newComment", data);
                })
                // Odmietnutý komentár (kratší ako 3 znaky, zlý e-mail) predtým
                // formulár len ticho nechal tak, ako bol.
                .catch((error) => {
                    const errors = error.response?.data?.errors;
                    this.errors = errors
                        ? Object.values(errors).flat()
                        : ["Komentár sa nepodarilo odoslať. Skúste to znova."];
                });
        },
    },
};
</script>
