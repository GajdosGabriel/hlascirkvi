<template>
    <div class="border-2 border-gray-400 rounded-md shadow-md relative text-xs md:text-sm">

        <div style="max-height: 11rem; overflow: hidden; position: relative">
            <div v-if="post.favoritesCount"
                 class="absolute bottom-0 right-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200">
                Doporúčené
            </div>
            <a :href="post.url">
                <img :alt="post.organization.title +  '/' + post.title" :data-src="post.thumbImage"
                     class="lazyload w-full" data-sizes="auto">
            </a>
        </div>

        <a :href="post.url">
            <h6 class="pb-2 px-2 font-semibold mb-10" :title="post.title">{{ post.title }}</h6>
        </a>


        <div class="text-gray-500 px-2 italic absolute bottom-0 flex flex-col text-xs md:text-sm">
            <a :href="'/user/' + post.organization.id + '/' +  post.organization.slug"
               v-text="post.organization.title">
            </a>
            <time :datetime="post.created_at">{{ post.createdAtHuman }}</time>
        </div>

    </div>

</template>

<script>
    export default {
        props: ['post'],

    }
</script>
