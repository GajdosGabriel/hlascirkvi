<template>
    <section class="event">

        <div class="card">
            <div style="padding: 1rem; background: #6c6c6c; color:white" @click="openModal">

                <div class="level" style="cursor: pointer">
                    <h4>Modlitebný múr</h4>
                     <i class="fas fa-praying-hands"  style="color: whitesmoke"></i>
                </div>

                <!-- <div class="level">
                    <a @click="domace('domov')" :class="{'active': isDomace }" class="menu">domáce</a>
                    <a @click="domace('zahranicie')" :class="{active: isZahranicie }" class="menu">zahraničné</a>
                    <a @click="domace('press')" :class="{active: isTlacove }" class="menu">tlačové</a>
                </div> -->

            </div>

            <ul style="padding: 1rem">
                <li v-for="prayer in prayers" :key="prayer.id">
                    <prayer-item :prayer="prayer"></prayer-item>
                </li>

            </ul>
        </div>

        <modal-new-prayer></modal-new-prayer>

    </section>
</template>

<script>
    import moment from 'moment';
    import Axios from 'axios';
    import {bus} from "../app";
    import prayerItem from '../prayer/prayer-item';
    import modalNewPrayer from '../prayer/ModalNewPrayer';


    export default {
        components: { prayerItem ,modalNewPrayer},
        data() {
            return {
                prayers: null
            }
        },

        created() {

            Axios.get('/prayers/create').then(
                (response) => {
                    this.prayers = response.data.data
                }
            )

        },
        methods:{
          openModal(){
              bus.$emit('openModalPrayer', () => {true});
          }
        },




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
