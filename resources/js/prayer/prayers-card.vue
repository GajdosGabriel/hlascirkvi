<template>
    <section class="card">

        <header class="card_header cursor-pointer" @click="openModal">
            <h4>Modlitebný múr</h4>
            <i class="ph ph-hands-praying"></i>
        </header>


            <ul class="">
                <li v-for="prayer in visiblePrayers" :key="prayer.id">
                    <prayers-card-item :prayer="prayer"></prayers-card-item>
                </li>
            </ul>

            <p v-if="prayers.length > collapsedCount" class="border-t border-gray-100 px-4 py-2 text-sm">
                <button type="button" class="font-semibold" :aria-expanded="expanded ? 'true' : 'false'" @click="expanded = !expanded">
                    {{ expanded ? 'Zobraziť menej' : 'Zobraziť ďalšie (' + (prayers.length - collapsedCount) + ')' }}
                    <i class="ph" :class="expanded ? 'ph-caret-up' : 'ph-caret-down'" aria-hidden="true"></i>
                </button>
            </p>



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
                url: '/api/prayers?page=1',
                // Zbalený modul ukazuje prvé tri, zvyšok načítanej strany až po rozkliknutí.
                collapsedCount: 3,
                expanded: false
            }
        },

        computed: {
            visiblePrayers() {
                return this.expanded ? this.prayers : this.prayers.slice(0, this.collapsedCount);
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
