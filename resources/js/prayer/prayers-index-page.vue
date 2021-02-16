<template>
    <section class="border-2 border-gray-500 rouned-sm mb-8">

        <div class="">
            <div class="text-white cursor-pointer p-3 text-base" style="background: #6c6c6c" @click="openModal">
                <div class="flex justify-between items-center cursor-pointer">
                    <h4>Modlitebný múr</h4>
                    <i class="fas fa-praying-hands" style="color: whitesmoke"></i>
                </div>
            </div>

            <ul class="mt-3">
                <li v-for="prayer in prayers.data" :key="prayer.id" class="hover:bg-gray-200">
                    <prayers-index-item :prayer="prayer"></prayers-index-item>
                </li>
            </ul>
        </div>

        <pagination :data="prayers" @fetchUrl="paginator"></pagination>

        <modal-new-prayer></modal-new-prayer>
    </section>
</template>

<script>
    import moment from 'moment';
    import Axios from 'axios';
    import {bus} from "../app";
    import prayersIndexItem from '../prayer/prayers-index-item';
    import pagination from "./pagination";
    import modalNewPrayer from '../prayer/ModalNewPrayer';


    export default {
        components: {prayersIndexItem, pagination, modalNewPrayer},
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

<style scoped>
    .menu {
        cursor: pointer;
        padding: 0rem .4rem;
        border: 1px solid white;

    }

    a:hover {
        border: 1px solid red;
        color: red;
        border-radius: .5rem;

    }

    .active {
        background: white;
        /*color: whitesmoke;*/
        border-radius: .5rem;
        border: 1px solid red;

    }

    .date {
        color: #838383;
        text-align: right;
        font-style: italic;
        font-size: 85%;
    }

    .hover:hover {
        cursor: pointer;
        background: rgba(231, 231, 231, 0.38);
    }


</style>
