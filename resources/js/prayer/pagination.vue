<template>
    <nav
        v-if="meta && meta.last_page > 1"
        class="mt-6 flex items-center justify-center gap-3"
        aria-label="Stránkovanie"
    >
        <button
            type="button"
            class="ar-btn ar-btn--quiet"
            :disabled="!links.prev"
            @click="fetchPaginate(links.prev)"
        >
            <i class="ph ph-caret-left"></i>
            <span>Novšie</span>
        </button>

        <span class="text-xs font-semibold tabular-nums text-gray-500">
            {{ meta.current_page }} / {{ meta.last_page }}
        </span>

        <button
            type="button"
            class="ar-btn ar-btn--quiet"
            :disabled="!links.next"
            @click="fetchPaginate(links.next)"
        >
            <span>Staršie</span>
            <i class="ph ph-caret-right"></i>
        </button>
    </nav>
</template>

<script>
export default {
    props: ["meta", "links"],

    methods: {
        fetchPaginate(url) {
            if (!url) return;

            this.$emit("fetchUrl", url);
        },
    },
};
</script>
