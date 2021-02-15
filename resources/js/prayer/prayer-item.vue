<template>
    <div>
        <div class="hover" style="line-height: initial; cursor: pointer;color: #5a5a5a" @click="toggle">

                        <span style="font-weight: 600;">
                        {{ prayer.user_name }}
                        </span>

            žiada o modlitbu

            <i class="fas fa-praying-hands" title="modlitbu"></i>

            <div style="font-weight: bold" v-if="prayer.title">{{ prayer.title }}</div>
            <div style="margin-bottom: .4rem">{{ prayer.body }}</div>
            <div style="margin-bottom: .5rem" class="date">{{ prayer.created_at | dateTime }}</div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal" id="modal-name">
            <div class="modal-sandbox"></div>
            <div class="modal-box">
                <div class="modal-header level">
                    <h4>Modlitbová prosba</h4>
                    <div @click="toggle" class="close-modal">&#10006;</div>
                </div>

                <div class="modal-body" style="font-size: 15px">

                    <span style="font-weight: 600;">
                        {{ prayer.user_name }}
                        </span>

                    žiada o

                    <i class="fas fa-praying-hands"></i>


                    <div style="margin-bottom: .4rem; font-size: 19px; margin-top: 15px; margin-bottom: 20px">{{
                        prayer.body }}
                    </div>
                    <div style="margin-bottom: .5rem" class="date">
                        <span style="font-weight: bold">Modlitba je stále aktuálna </span>

                        Zverejnená dňa: {{ prayer.created_at | dateTime }}
                    </div>

                </div>

                <div class="modal-header" >
                    <button @click="toggle" style="color: whitesmoke; border-radius: 10px; border-color: whitesmoke; border-size: 1rem">Zavrieť</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import moment from "moment";
    import modalShowPrayer from '../prayer/ModalShowPrayer';
    import modalNewPrayer from "./ModalNewPrayer";

    export default {
        props: ['prayer'],
        components: {modalShowPrayer},

        data() {
            return {
                showModal: false
            }
        },

        methods: {
            toggle() {
                this.showModal = !this.showModal
            }
        },
        filters: {
            dateTime: function (value) {
               return moment(value).format('D.M.Y, h:mm');
            }
        }

    }
</script>
