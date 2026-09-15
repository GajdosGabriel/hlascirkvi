<template>
    <div class="ui-dropdown" @click.stop>
        <button
            type="button"
            ref="trigger"
            class="ui-dropdown__trigger"
            :class="{ 'is-open': open }"
            aria-haspopup="menu"
            :aria-expanded="String(open)"
            :aria-label="label"
            :title="label"
            @click="toggle"
            @keydown.down.prevent="openAndFocus(0)"
            @keydown.up.prevent="openAndFocus(-1)"
        >
            <slot name="trigger">
                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <circle cx="4" cy="10" r="1.6" />
                    <circle cx="10" cy="10" r="1.6" />
                    <circle cx="16" cy="10" r="1.6" />
                </svg>
            </slot>
        </button>

        <!--
            Ponuka sa presúva do <body>: panely, tabuľky aj karty majú často
            overflow: hidden/auto a absolútne umiestnený zoznam by orezali.
            v-show namiesto v-if — formulár v ponuke musí ostať v DOM, kým
            prehliadač po kliknutí dokončí jeho odoslanie.
        -->
        <Teleport to="body">
            <Transition name="ui-dropdown">
                <div
                    v-show="open"
                    ref="menu"
                    class="ui-dropdown__menu"
                    :class="'ui-dropdown__menu--' + placement"
                    :style="menuStyle"
                    role="menu"
                    @click="onMenuClick"
                    @keydown.esc.prevent="close(true)"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.tab="close(false)"
                >
                    <slot></slot>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script>
const GAP = 6;
const EDGE = 8;

export default {
    props: {
        // Na ktorú stranu od tlačidla sa ponuka zarovná.
        align: { type: String, default: "right" },
        label: { type: String, default: "Možnosti" },
    },

    data() {
        return {
            open: false,
            placement: "bottom",
            menuStyle: { top: "0px", left: "0px" },
        };
    },

    beforeUnmount() {
        this.unbind();
    },

    methods: {
        toggle() {
            this.open ? this.close(false) : this.show();
        },

        show() {
            this.open = true;
            this.bind();
            this.$nextTick(this.position);
        },

        close(returnFocus) {
            if (!this.open) return;
            this.open = false;
            this.unbind();
            if (returnFocus) this.$refs.trigger.focus();
        },

        openAndFocus(index) {
            if (!this.open) this.show();
            this.$nextTick(() => this.focusItem(index));
        },

        items() {
            return this.$refs.menu
                ? [...this.$refs.menu.querySelectorAll("a[href], button:not([disabled])")]
                : [];
        },

        focusItem(index) {
            const items = this.items();
            if (!items.length) return;
            items[(index + items.length) % items.length].focus();
        },

        move(step) {
            const items = this.items();
            this.focusItem(items.indexOf(document.activeElement) + step);
        },

        onMenuClick(event) {
            // Zatvára až akcia, nie klik na nadpis či oddeľovač.
            if (event.target.closest("a[href], button")) this.close(false);
        },

        position() {
            const trigger = this.$refs.trigger;
            const menu = this.$refs.menu;
            if (!trigger || !menu) return;

            const rect = trigger.getBoundingClientRect();
            const width = menu.offsetWidth;
            const height = menu.offsetHeight;
            const vw = document.documentElement.clientWidth;
            const vh = window.innerHeight;

            let left = this.align === "left" ? rect.left : rect.right - width;
            left = Math.min(Math.max(left, EDGE), vw - width - EDGE);

            const fitsBelow = rect.bottom + GAP + height <= vh - EDGE;
            const fitsAbove = rect.top - GAP - height >= EDGE;
            this.placement = !fitsBelow && fitsAbove ? "top" : "bottom";

            const top = this.placement === "top"
                ? rect.top - GAP - height
                : rect.bottom + GAP;

            this.menuStyle = { top: top + "px", left: left + "px" };
        },

        onOutside(event) {
            const inTrigger = this.$el.contains(event.target);
            const inMenu = this.$refs.menu && this.$refs.menu.contains(event.target);
            if (!inTrigger && !inMenu) this.close(false);
        },

        onKey(event) {
            if (event.key === "Escape") this.close(true);
        },

        bind() {
            document.addEventListener("pointerdown", this.onOutside, true);
            document.addEventListener("keydown", this.onKey);
            window.addEventListener("resize", this.position);
            window.addEventListener("scroll", this.position, true);
        },

        unbind() {
            document.removeEventListener("pointerdown", this.onOutside, true);
            document.removeEventListener("keydown", this.onKey);
            window.removeEventListener("resize", this.position);
            window.removeEventListener("scroll", this.position, true);
        },
    },
};
</script>
