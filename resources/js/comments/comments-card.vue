<template>
    <section class="card">
        <header class="card_header">
            <h4>Posledné komentáre</h4>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                    clip-rule="evenodd"
                />
            </svg>
        </header>

        <ul class="">
            <li
                v-for="comment in comments"
                :key="comment.id"
                class="cursor-pointer hover:bg-gray-100 px-2 py-1"
            >
                <a
                    :href="
                        '/post/' +
                            comment.comment_post.id +
                            '/' +
                            comment.comment_post.slug +
                            '#'+ comment.id
                            
                    "
                >
                    <div class="">
                        <div class="block text-gray-800 ">{{ comment.body.slice(0, 40) }}</div>
                        <div class="text-xs ">{{ comment.created_at_human }}</div>
                    </div>
                </a>
            </li>
        </ul>
    </section>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            comments: []
        };
    },

    created() {
        this.getComments();
    },
    methods: {
        getComments() {
            axios.get("/api/comments").then(response => {
                this.comments = response.data;
            });
        }
    }
};
</script>
