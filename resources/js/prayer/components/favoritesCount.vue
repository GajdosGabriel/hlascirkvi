<template>
    <button type="button" class="ar-btn ar-btn--quiet" title="Pripojiť sa k modlitbe">
        <i class="fas fa-praying-hands"></i>
        <span>Modlím sa</span>
        <span v-if="prayer.favoritesCount > 0" class="ar-badge ar-badge--count">
            {{ prayer.favoritesCount }}
        </span>
    </button>
</template>

<script>
export default {
    props: ["prayer"],
};
</script>
