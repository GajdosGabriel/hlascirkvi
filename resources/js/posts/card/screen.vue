<template>
    <h1>mobile card {{ windowHeight }}</h1>
</template>
<script>
    export default {
        data() {
            return {
                windowHeight: window.innerHeight,
                txt: ''
            }
        },

        // watch: {
        //     windowHeight(newHeight, oldHeight) {
        //         this.txt = `it changed to ${newHeight} from ${oldHeight}`;
        //     }
        // },

        mounted() {
            this.$nextTick(() => {
                window.addEventListener('resize', this.onResize);
            })
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.onResize);
        },

        methods: {
            onResize() {
                this.windowHeight = window.innerWidth
            }
        }
    }
</script>
