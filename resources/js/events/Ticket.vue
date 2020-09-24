
<script>
    export default {
        data:function() {
            return {
                quantities: ''
            }
        },

        computed: {
            disabled: function() {
                if(this.quantities < 1) {
                    return 'disabled';
                }
            },
        }
    }
</script>

<style scoped>
    .inline {
        display: inline-flex;
    }
    label {
        background: #898989;
        padding: 0 .5rem;
        color: whitesmoke;
    }

</style>