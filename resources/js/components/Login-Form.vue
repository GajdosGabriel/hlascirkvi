<template>
    <div>
        <transition name="slide">
            <div  v-if="show" class="fullWindow">
                <div class="close" @click="toggle">Zavrieť X</div>
                <h1>Hlas Cirkvi.sk</h1>
                <h5>Registrácia</h5>
            </div>
        </transition>
    </div>

</template>

<script>
    import {bus} from '../app';
    export default {
        data: function() {
            return {
                show: false
            }
        },

        mounted: function () {
        bus.$on('open', () => {
                this.toggle();
            });
        },

        methods: {
            toggle: function() {
                this.show = ! this.show;
            }

        }
    }
</script>
<style scoped>
    .fullWindow {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        overflow: auto;
        background: #6589ff; /* Just to visualize the extent */
        padding: 6rem;
        z-index: 999;
    }

    .slide-leave-active,
    .slide-enter-active {
        transition: 1s;
    }
    .slide-enter {
        transform: translate(100%, 0%);
    }
    .slide-leave-to {
        transform: translate(-100%, 0);
    }
    .close {
        float:right;
        cursor: pointer;
    }

</style>