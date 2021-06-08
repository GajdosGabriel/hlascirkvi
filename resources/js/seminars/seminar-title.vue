<template>
    <div :class="publishedSeminar">
        {{ seminar.title }}
        <span v-if="seminar.posts_count" class="text-sm ml-2 text-gray-500">
            ({{ seminar.posts_count }})
        </span>
    </div>
</template>

<script>
export default {
    props: ["seminar"],
    computed: {
        publishedSeminar: function() {
            if (this.seminar.published == null) {
                return "text-red-700";
            }
        }
    }
};
</script>
