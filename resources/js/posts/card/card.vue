<template>
    <div
        class="border-2 bg-white m-2 rounded-md shadow-md relative md:text-sm"
        :class="isPublished"
    >
        <div style="max-height: 11rem; overflow: hidden; position: relative">
            <div
                v-if="post.favoritesCount"
                class="absolute bottom-0 right-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200"
            >
                Doporúčené
            </div>
            <a :href="post.url">
                <img
                    :alt="post.organization.title + '/' + post.title"
                    :data-src="post.thumbImage"
                    class="lazyload w-full"
                    data-sizes="auto"
                />
            </a>
        </div>

        <h6 class="pb-2 px-2 font-semibold mb-7" :title="post.title">
            <a :href="post.url">{{ shorttitle }}</a>
        </h6>

        <div
            class="text-gray-500 px-2 italic absolute bottom-0 flex flex-col text-xs md:text-sm"
        >
            <a
                :href="'/organizations/' + post.organization.id"
                v-text="post.organization.title"
            >
            </a>
            <time v-if="createdat" :datetime="post.created_at">{{
                post.createdAtHuman
            }}</time>

            <card-published-blocked v-if="!post.hasUpdater" :post="post" />
            <card-published-button v-if="!post.published" :post="post" />
        </div>
    </div>
</template>

<script>
import cardPublishedBlocked from "./card-publishedBlocked";
import cardPublishedButton from "./card-publishedButton";

export default {
    props: {
        post: {
            type: Object,
            default: "",
        },
        createdat: {
            type: Boolean,
            required: false,
            default: true,
        },
        shortertext: {
            type: Boolean,
            required: false,
            default: true,
        },
    },
    computed: {
        shorttitle: function () {
            if (this.shortertext) return this.post.title;
            return this.post.title.slice(0, 36);
        },

        isPublished: function () {
            return this.post.published ? "" : "border-red-600";
        },
    },
    components: { cardPublishedBlocked, cardPublishedButton },
};
</script>
