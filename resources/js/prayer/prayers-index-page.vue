<template>
    <section class="">
        <div class="page_title">
            <h2 class="text-2xl">Modlitebný múr</h2>

            <button
                class="flex items-center px-2 py-1 border-2 border-gray-300 rounded-md hover:bg-blue-300"
                @click="openModal"
            >
                <i class="fas fa-praying-hands mr-1"></i>
                <span>Nová modlitba</span>
            </button>
        </div>

        <ul class="mt-3">
            <li
                v-for="prayer in prayers.data"
                :key="prayer.id"
                class="hover:bg-gray-200"
            >
                <prayers-index-item :prayer="prayer"></prayers-index-item>
            </li>
        </ul>

        <pagination
            :meta="meta"
            :links="links"
            @fetchUrl="paginator"
        ></pagination>

        <modal-new-prayer></modal-new-prayer>
        <modal-show-prayer></modal-show-prayer>
    </section>
</template>

<script>
import Axios from "axios";
import { bus } from "../app";
import prayersIndexItem from "../prayer/prayers-index-item";
import pagination from "./pagination";
import modalNewPrayer from "../prayer/ModalNewPrayer";
import modalShowPrayer from "../prayer/ModalShowPrayer";

export default {
    components: {
        prayersIndexItem,
        pagination,
        modalNewPrayer,
        modalShowPrayer
    },
    data() {
        return {
            links: "",
            meta: "",
            prayers: [],
            url: "/api/modlitby?page=1"
        };
    },
    created() {
        this.getPrayers();
    },

    watch: {
        url() {
            this.getPrayers();
        }
    },
    methods: {
        getPrayers() {
            Axios.get(this.url).then(response => {
                this.prayers = response.data;
                this.meta = response.data.meta;
                this.links = response.data.links;
            });
        },
        openModal() {
            bus.$emit("openModalPrayer", () => {
                true;
            });
        },
        paginator(url) {
            this.url = url;
        }
    }
};
</script>
