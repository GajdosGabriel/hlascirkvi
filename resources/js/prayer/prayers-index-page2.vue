<template>
    <section>
        <header class="mb-4">
            <h2 class="ar-display flex items-center gap-2 text-lg font-extrabold">
                Vypočuté modlitby
                <span v-if="meta.total" class="ar-badge ar-badge--ok">
                    {{ total }}
                </span>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Prosby, pri ktorých sa ľudia dočkali odpovede. Ďakujeme za každé
                svedectvo.
            </p>
        </header>

        <p v-if="loading" class="py-10 text-center text-sm text-gray-400">
            Načítavam…
        </p>

        <p
            v-else-if="!prayers.length"
            class="rounded-lg border border-dashed border-[color:var(--ar-line)] bg-white px-4 py-10 text-center text-sm text-gray-500"
        >
            Zatiaľ tu nie je žiadne svedectvo.
        </p>

        <ul v-else class="space-y-4">
            <li v-for="prayer in prayers" :key="prayer.id">
                <prayers-index-item :prayer="prayer"></prayers-index-item>
            </li>
        </ul>

        <pagination :meta="meta" :links="links" @fetchUrl="paginator"></pagination>

        <modal-new-prayer></modal-new-prayer>
        <modal-show-prayer></modal-show-prayer>
    </section>
</template>

<script>
import Axios from "axios";
import prayersIndexItem from "./prayers-index-item";
import pagination from "./pagination";
import modalNewPrayer from "./ModalNewPrayer";
import modalShowPrayer from "./ModalShowPrayer";

export default {
    components: { prayersIndexItem, pagination, modalNewPrayer, modalShowPrayer },

    data() {
        return {
            links: {},
            meta: {},
            prayers: [],
            loading: true,
            url: "/api/prayers/fulfilled?page=1",
        };
    },

    computed: {
        total() {
            return new Intl.NumberFormat("sk-SK").format(this.meta.total);
        },
    },

    created() {
        this.getPrayers();
    },

    watch: {
        url() {
            this.getPrayers(true);
        },
    },

    methods: {
        getPrayers(scroll = false) {
            this.loading = true;

            Axios.get(this.url).then((response) => {
                this.prayers = response.data.data;
                this.meta = response.data.meta;
                this.links = response.data.links;
                this.loading = false;

                if (scroll) {
                    this.$nextTick(() => {
                        this.$el.scrollIntoView({ behavior: "smooth", block: "start" });
                    });
                }
            });
        },

        paginator(url) {
            this.url = url;
        },
    },
};
</script>
