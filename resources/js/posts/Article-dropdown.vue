<template>
    <dropdown-slot :align="align" label="Spravovať článok">
        <a :href="'/dashboard/posts/' + post.id + '/edit'">
            <i class="fas fa-pen" aria-hidden="true"></i> Upraviť
        </a>

        <button v-if="$auth.isAdmin()" type="button" @click="updatePost">
            <i class="fas fa-inbox" aria-hidden="true"></i> Do buffera
        </button>

        <button v-if="$auth.isAdmin()" type="button" :disabled="summarizing" @click="summarize">
            <i class="fas fa-magic" aria-hidden="true"></i>
            {{ summarizing ? "Vytváram zhrnutie…" : "AI zhrnutie" }}
        </button>

        <hr class="ui-dropdown__divider">

        <button type="button" class="ui-dropdown__item--danger" @click="deletePost">
            <i class="far fa-trash-alt" aria-hidden="true"></i> Zmazať
        </button>
    </dropdown-slot>
</template>
<script>
import DropdownSlot from "../components/DropdownSlot.vue";
import { bus } from "../eventBus";

export default {
    components: { DropdownSlot },
    props: {
        post: { type: Object, required: true },
        align: { type: String, default: "right" },
    },

    data() {
        return { summarizing: false };
    },

    methods: {
        // Vynútené zhrnutie (Admin\AiController@summarize). Po úspechu sa
        // stránka načíta znova, aby sa „V skratke" ukázalo nad textom.
        summarize() {
            this.summarizing = true;
            axios
                .post("/admin/ai/summarize", { post: String(this.post.id) })
                .then((response) => {
                    if (response.data.ok) {
                        window.location.reload();
                        return;
                    }
                    bus.$emit("flash", { body: response.data.message });
                })
                .catch(() => bus.$emit("flash", { body: "Zhrnutie sa nepodarilo vytvoriť." }))
                .finally(() => (this.summarizing = false));
        },

        deletePost() {
            if (!window.confirm("Skutočne vymazať?")) {
                return;
            }
            axios
                .delete("/dashboard/posts/" + this.post.id)
                .then(() => (window.location.href = "/dashboard/posts"));
        },

        updatePost() {
            axios
                .put("/api/postSupport/" + this.post.id, {})
                .then(() => (window.location.href = "/"));
        },
    },
};
</script>
