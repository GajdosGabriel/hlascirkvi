<template>
    <section class="event">

        <div class="card">
            <div style="padding: 1rem; background: #6c6c6c; color:white" @click="openModal">

                <div class="level" style="cursor: pointer">
                    <h4>Modlitebný múr</h4>
                     <i class="fas fa-praying-hands"></i>
                </div>

                <!-- <div class="level">
                    <a @click="domace('domov')" :class="{'active': isDomace }" class="menu">domáce</a>
                    <a @click="domace('zahranicie')" :class="{active: isZahranicie }" class="menu">zahraničné</a>
                    <a @click="domace('press')" :class="{active: isTlacove }" class="menu">tlačové</a>
                </div> -->

            </div>

            <ul style="padding: 1rem">
                <li v-for="prayer in prayers" :key="prayer.id">
                    <div class="hover" style="line-height: initial; cursor: pointer;color: #5a5a5a">
                        Pridal:
                        <span style="font-weight: 600;">
                        {{ prayer.user_name }}
                        </span>
                    </div>
                    <div style="margin-bottom: .4rem">{{ prayer.body }}

                    </div>
                    <div style="margin-bottom: .5rem" class="date">{{ prayer.pubdate | dateTime }}</div>
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
    import modalNewPrayer from '../prayer/Modal';

    export default {
        components: { modalNewPrayer},
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


        filters: {
            dateTime: function (value) {
                return moment(value).subtract(1, 'hours').format('lll');
//                return moment(value).format('lll');
//                return moment(value).format('LT D.M.Y');
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
