<template>
    <div @click="store(reply)">
        <div class="flex">
            <svg
                class="cursor-pointer hover:bg-red-300 text-gray-400 rounded-full h-7 w-7 p-1 "
                @click="store(reply)"
                :class="replyClass"
                title="Hlasovať za komentár"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                />
            </svg>
            <span>{{ reply.favorites_count }}</span>
        </div>
    </div>
</template>

<script>
export default {
    props: ["reply"],
    computed: {
        signedIn: function() {
            return window.App.signedIn;
        },

        replyManager: function() {
            if (this.reply.favorites_count > 0) {
                return ["bg-red-700 text-white"];
            }
        },

        replyClass: function() {
            if (this.reply.is_favorited) {
                return ["bg-red-700 text-white rounded-full h-7 w-7 p-1"];
            }
        },

        replyCounter: function() {
            return ["fas fa-heart fa-lg fa-disabled"];
        }
    },

    methods: {
        store: function(reply) {
            if (!this.signedIn) {
                return alert("Najprv sa prihláste!");
            }
            axios.put("/favorites/" + this.reply.id, {
                model: "Comment",
                model_id: this.reply.id
            });
            this.replyisFavorited = true;
            bus.$emit("flash", { body: "Pridaný hlas komentáru." });
        }
    }
};
</script>
<style>
.fa-disabled {
    opacity: 0.6;
    pointer-events: none;
    color: red;
}

.fa-active {
    color: red;
}
</style>
