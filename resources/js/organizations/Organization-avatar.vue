<template>
    <div class="shrink-0">
        <img
            v-if="organization.avatar"
            :alt="organization.title"
            class="h-12 w-12 rounded-full object-cover ring-1 ring-[color:var(--ar-line)]"
            :src="
                domain +
                'storage/organizations/' +
                organization.id +
                '/' +
                organization.avatar
            "
        />

        <!-- Bez fotky nesie kanál iniciálky; rozmer musí sedieť s obrázkom,
             inak riadok pri každom kanáli poskočí. -->
        <div
            v-else
            class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:var(--ar-paper-deep)] text-xl font-semibold text-[color:var(--ar-ink-soft)]"
        >
            {{ organization.initialName }}
        </div>
    </div>
</template>

<script>
export default {
    props: ["organization"],
    data: function () {
        return {
            domain: window.App.baseUrl,
        };
    },
};
</script>
