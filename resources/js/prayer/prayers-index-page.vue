<template>
    <section class="">

        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-semibold"> Modlitebný múr</h2>

            <button class="flex items-center px-2 py-1 border-2 border-gray-300 rounded-md hover:bg-blue-300"
                    @click="openModal">
                <i class="fas fa-praying-hands mr-1"></i>
                <span>Nová modlitba</span>
            </button>
        </div>


        <ul class="mt-3">
            <li v-for="prayer in prayers.data" :key="prayer.id" class="hover:bg-gray-200">
                <prayers-index-item :prayer="prayer"></prayers-index-item>
            </li>
        </ul>


        <pagination :data="prayers" @fetchUrl="paginator"></pagination>

        <modal-new-prayer></modal-new-prayer>
        <modal-show-prayer></modal-show-prayer>
    </section>
</template>

<script>
    import moment from 'moment';
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
                prayers: [],
                url: '/modlitby/create?page=1'
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
                        this.prayers = response.data
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


