<template>
    <div>

        <a class="btn btn-small" href="#" @click="openModal">
            <i class="fas fa-praying-hands"></i>
            Nová modlitba
        </a>
        <modal-new-prayer v-if="show"/>
    </div>

</template>

<script>
    import modalNewPrayer from './ModalNewPrayer'
    import {bus} from "../app";

    export default {
        components: {modalNewPrayer},
        data(){
            return {
                show: false
            }
        },

        methods:{
            openModal(){
                bus.$emit('openModalPrayer', () => {true});
            }
        },
    }
</script>
