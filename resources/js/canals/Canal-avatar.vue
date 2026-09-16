<template>
    <div class="shrink-0">
        <img
            v-if="canal.avatar"
            :alt="canal.title"
            class="h-12 w-12 rounded-full object-cover ring-1 ring-[color:var(--ar-line)]"
            :src="
                domain +
                'storage/organizations/' +
                canal.id +
                '/' +
                canal.avatar
            "
        />

        <!-- Bez fotky nesie kanál iniciálky; rozmer musí sedieť s obrázkom,
             inak riadok pri každom kanáli poskočí. -->
        <div
            v-else
            class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:var(--ar-paper-deep)] text-xl font-semibold text-[color:var(--ar-ink-soft)]"
        >
            {{ canal.initialName }}
        </div>
    </div>
</template>

<script>
export default {
    props: ["canal"],
    data: function () {
        return {
            domain: window.App.baseUrl,
        };
    },
};
</script>
