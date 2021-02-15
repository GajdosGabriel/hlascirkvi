<template>
    <div>

        <!-- Modal -->
        <div v-if="show" class="modal" id="modal-name">
            <div class="modal-sandbox"></div>
            <div class="modal-box">
                <div class="modal-header level">
                    <h4>Pridať modlitebný úmysel</h4>
                    <div @click="toggle" class="close-modal">&#10006;</div>
                </div>

                <div class="modal-body">


                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {bus} from '../app';
    import Form from "../messenger/form";
    import Axios from 'axios';


    export default {
        components: {Form},
        data: function () {
            return {
                show: false,
                annotation: false,
                form: {}
            }
        },

        created: function () {
            bus.$on('openModalPrayer', () => {
                this.show = true
            });
        },

        methods: {
            toggle: function () {
                this.show = !this.show;
            },

            savePrayer: function () {
                Axios.post('modlitby', this.form);
                // this.form = {};
                // this.show = false;
            }
        }


    }
</script>
