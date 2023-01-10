<template>
    <div class="relative z-10 px-2">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-7 w-7 cursor-pointer bg-gray-100 rounded-full text-gray-400 hover:bg-gray-200 p-1"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            @click="toggle"
            title="Spravovať článok"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
            />
        </svg>
        <ul class="dropdown-menu z-50" v-if="open">
            <a
                :href="
                    '/organization/' +
                    post.organization_id +
                    '/post/' +
                    post.id +
                    '/edit'
                "
            >
                <li class="dropdown-item">upraviť</li>
            </a>

            <li @click="deletePost" class="dropdown-item">zmazať</li>

            <li
                @click="updatePost"
                v-if="$auth.isAdmin()"
                class="dropdown-item whitespace-nowrap"
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
    props: ["post"],
    data: function () {
        return {
            open: false,
        };
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
                .delete(
                    "/organization/" +
                        this.post.organization_id +
                        "/post/" +
                        this.post.id
                )
                .then(
                    (window.location.href =
                        "/organization/" + this.post.organization_id + "/post/")
                );
        },

        updatePost: function () {
            axios
                .put("/api/postSupport/" + this.post.id, {})
                .then((window.location.href = "/"));
        },
    },

    computed: {
        authUser: function () {
            return window.App;
        },
    },
};
</script>
