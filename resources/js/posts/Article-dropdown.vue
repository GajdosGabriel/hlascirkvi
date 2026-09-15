<template>
    <dropdown-slot :align="align" label="Spravovať článok">
        <a :href="'/dashboard/posts/' + post.id + '/edit'">
            <i class="fas fa-pen" aria-hidden="true"></i> Upraviť
        </a>

        <button v-if="$auth.isAdmin()" type="button" @click="updatePost">
            <i class="fas fa-inbox" aria-hidden="true"></i> Do buffera
        </button>

        <hr class="ui-dropdown__divider">

        <button type="button" class="ui-dropdown__item--danger" @click="deletePost">
            <i class="far fa-trash-alt" aria-hidden="true"></i> Zmazať
        </button>
    </dropdown-slot>
</template>
<script>
import DropdownSlot from "../components/DropdownSlot.vue";

export default {
    components: { DropdownSlot },
    props: {
        post: { type: Object, required: true },
        align: { type: String, default: "right" },
    },

    methods: {
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
