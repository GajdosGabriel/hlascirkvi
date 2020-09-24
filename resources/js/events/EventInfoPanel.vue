<script>
    export default {
        data: function() {
            return {
                show:false
            }
        },

        methods: {
            toggle: function() {
                this.show = ! this.show;
            }

        }
    }
</script>