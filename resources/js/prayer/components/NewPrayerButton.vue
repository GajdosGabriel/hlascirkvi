<template>
    <button type="button" class="ar-btn ar-btn--accent" @click="openModal">
        <i class="ph ph-hands-praying"></i>
        <span>Poprosiť o modlitbu</span>
    </button>
</template>

<script>
import { bus } from "../../eventBus";

export default {
    methods: {
        // Okno s formulárom visí v prayers-index-page2; zbernica ho otvorí
        // odkiaľkoľvek zo stránky, aj z hlavičky v šablóne.
        openModal() {
            bus.$emit("openModalPrayer");
        },
    },
};
</script>
