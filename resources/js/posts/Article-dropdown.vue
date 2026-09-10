<template>
    <div class="relative z-10">
        <button
            type="button"
            @click="toggle"
            title="Spravovať článok"
            aria-label="Spravovať článok"
            class="flex h-8 w-8 items-center justify-center rounded-full border border-[color:var(--ar-line)] bg-white text-gray-400 transition-colors hover:border-[color:var(--ar-accent)] hover:text-[color:var(--ar-accent)]"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <ul class="dropdown-menu z-50 mt-1" :class="menuClass" v-if="open">
            <a :href="'/dashboard/posts/' + post.id + '/edit'">
                <li class="dropdown-item">upraviť</li>
            </a>

            <li @click="deletePost" class="dropdown-item cursor-pointer">zmazať</li>

            <li
                @click="updatePost"
                v-if="$auth.isAdmin()"
                class="dropdown-item cursor-pointer whitespace-nowrap"
            >
                Do buffer
            </li>
        </ul>
    </div>
</template>
<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],
    props: {
        post: { type: Object, required: true },
        /*
         * Na ktorú stranu sa ponuka rozvinie. Pri ikone na ľavom okraji
         * obsahu musí ísť doprava, pri ikone na pravom okraji doľava — inak
         * zoznam vylezie mimo stránku.
         */
        align: { type: String, default: "left" },
    },
    data: function () {
        return {
            open: false,
        };
    },

    computed: {
        menuClass: function () {
            return this.align === "left" ? "dropdown-menu--left" : "";
        },
    },

    methods: {
        toggle: function () {
            this.open = !this.open;
        },

        deletePost: function () {
            if (!window.confirm("Skutočne vymazať!")) {
                return;
            }
            axios
                .delete("/dashboard/posts/" + this.post.id)
                .then((window.location.href = "/dashboard/posts"));
        },

        updatePost: function () {
            axios
                .put("/api/postSupport/" + this.post.id, {})
                .then((window.location.href = "/"));
        },
    },
};
</script>
