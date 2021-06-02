<template>
    <div class="flex justify-between mb-6 mt-6 items-center">
        <h2 class="text-2xl">
            {{ seminar.title }}
            <span class="text-sm ml-2 text-gray-500">
                ({{ seminar.posts.length }})</span
            >
        </h2>

        <div
            @click="published"
            class="cursor-pointer"
            v-text="publishedButton"
        ></div>

        <div class="flex items-center">
            <c-article-dropdown
                :post="seminar"
                :model="'/seminars/'"
                :redirect="'seminars'"
            >
            </c-article-dropdown>

            <a
                v-if="seminar.youtube_playlist"
                :href="'/seminars/' + seminar.id + '/upload'"
                class="btn btn-default"
                >Načítať z youTube zoznamu</a
            >

            <a
                v-else
                :href="'/seminars/' + seminar.id + '/edit'"
                class="btn btn-default"
            >
                Nevyplnený playlist
            </a>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ["seminar"],

    computed: {
        publishedButton: function() {
            if (this.seminar.published == null) {
                return "Publikovať";
            }
            return "Nepublikovať";
        }
    },

    methods: {
        published: function() {
            axios.put("/seminars/" + this.seminar.id, {
                published: Date.now()
            });
        }
    }
};
</script>
