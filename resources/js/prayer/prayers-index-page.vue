<template>
    <section>
        <header class="mb-4">
            <h2 class="ar-display flex items-center gap-2 text-lg font-extrabold">
                Prosby o modlitbu
                <span v-if="meta.total" class="ar-badge ar-badge--count">
                    {{ total }}
                </span>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Kliknutím na prosbu sa k modlitbe pripojíte. Prosiaci sa dozvie,
                že sa zaňho niekto modlí.
            </p>
        </header>

        <p
            v-if="notice"
            class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"
            role="status"
        >
            {{ notice }}
        </p>

        <p v-if="loading" class="py-10 text-center text-sm text-gray-400">
            Načítavam prosby…
        </p>

        <p
            v-else-if="!prayers.length"
            class="rounded-lg border border-dashed border-[color:var(--ar-line)] bg-white px-4 py-10 text-center text-sm text-gray-500"
        >
            Zatiaľ tu nie je žiadna prosba. Buďte prvý.
        </p>

        <ul v-else class="space-y-4">
            <li v-for="prayer in prayers" :key="prayer.id">
                <prayers-index-item :prayer="prayer"></prayers-index-item>
            </li>
        </ul>

        <pagination :meta="meta" :links="links" @fetchUrl="paginator"></pagination>
    </section>
</template>

<script>
import Axios from "axios";
import prayersIndexItem from "./prayers-index-item";
import pagination from "./pagination";
import { bus } from "../eventBus";

export default {
    components: { prayersIndexItem, pagination },

    data() {
        return {
            links: {},
            meta: {},
            prayers: [],
            loading: true,
            notice: "",
            url: "/api/prayers?page=1",
        };
    },

    computed: {
        // Múr má desaťtisíce prosieb, bez oddeľovača je číslo nečitateľné.
        total() {
            return new Intl.NumberFormat("sk-SK").format(this.meta.total);
        },
    },

    created() {
        this.getPrayers();

        bus.$on("prayerSaved", ({ prayer, edited }) => {
            if (edited) {
                this.prayers = this.prayers.map((item) => (item.id === prayer.id ? prayer : item));
                this.notice = "Zmeny boli uložené.";
            } else {
                this.prayers.unshift(prayer);
                this.meta.total = (this.meta.total || 0) + 1;
                this.notice = "Prosba bola pridaná.";
            }

            clearTimeout(this.noticeTimer);
            this.noticeTimer = setTimeout(() => (this.notice = ""), 6000);
        });
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

                // Ďalšia strana sa načíta na mieste, takže bez posunu by
                // čitateľ ostal v strede zoznamu, ktorý sa medzitým vymenil.
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
