<template>
    <section class="card">

        <header class="card_header cursor-pointer" @click="openModal">
            <h4>Modlitebný múr</h4>
            <i class="fas fa-praying-hands"></i>
        </header>


            <ul class="">
                <li v-for="prayer in prayers.slice(0, 7)" :key="prayer.id">
                    <prayers-card-item :prayer="prayer"></prayers-card-item>
                </li>
            </ul>



        <modal-show-prayer></modal-show-prayer>

    </section>
</template>

<script>
    import Axios from 'axios';
    import prayersCardItem from '../prayer/prayers-card-item';
    import modalShowPrayer from '../prayer/ModalShowPrayer';


    export default {
        components: {prayersCardItem,  modalShowPrayer},
        data() {
            return {
                prayers: [],
                url: '/api/prayers?page=1'
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
                        this.prayers = response.data.data
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
