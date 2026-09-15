<template>
    <dropdown-slot label="Spravovať">
        <a :href="model + post.id + '/edit'">
            <i class="fas fa-pen" aria-hidden="true"></i> Upraviť
        </a>

        <hr class="ui-dropdown__divider">

        <button type="button" class="ui-dropdown__item--danger" @click="deletePost">
            <i class="far fa-trash-alt" aria-hidden="true"></i> Zmazať
        </button>
    </dropdown-slot>
</template>
<script>
import DropdownSlot from "./DropdownSlot.vue";

export default {
    components: { DropdownSlot },
    props: {
        post: { type: Object, required: true },
        model: { type: String, required: true },
        redirect: { type: String, default: "/" },
    },

    methods: {
        deletePost() {
            if (!window.confirm("Skutočne vymazať?")) {
                return;
            }
            axios
                .delete(this.model + this.post.id)
                .then(() => (window.location.href = this.redirect));
        },
    },
};
</script>
