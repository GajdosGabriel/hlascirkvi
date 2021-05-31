<template>
    <section class="">

        <div class="page_title">
            <h2 class="text-2xl">Vypočuté modlitby</h2>
        </div>


        <ul class="mt-3">
            <li v-for="prayer in prayers.data" :key="prayer.id" class="hover:bg-gray-200">
                <prayers-index-item :prayer="prayer"></prayers-index-item>
            </li>
        </ul>

        <pagination :meta="meta" :links="links" @fetchUrl="paginator"></pagination>

        <modal-new-prayer></modal-new-prayer>
        <modal-show-prayer></modal-show-prayer>
    </section>
</template>

<script>
    import Axios from 'axios';
    import {bus} from "../app";
    import prayersIndexItem from '../prayer/prayers-index-item';
    import pagination from "./pagination";
    import modalNewPrayer from '../prayer/ModalNewPrayer';
    import modalShowPrayer from '../prayer/ModalShowPrayer';


    export default {
        components: {prayersIndexItem, pagination, modalNewPrayer, modalShowPrayer},
        data() {
            return {
                links: '',
                meta: '',
                prayers: [],
                url: '/api/prayers/fulfilled?page=1'
            }
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
                Axios.get(this.url).then(
                    (response) => {
                        this.prayers = response.data;
                        this.meta = response.data.meta;
                        this.links = response.data.links;
                    }
                )
            },
            openModal() {
                bus.$emit('openModalPrayer', () => {
                    true
                });
            },
            paginator(url) {
                this.url = url;
            }
        }

    }
</script>


