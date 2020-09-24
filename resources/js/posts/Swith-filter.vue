
<script>
    export default {
        data: function() {
            return {
                all: false

            }
        },

        created: function() {
            let self = this;

            window.addEventListener('click', function(e){
                // close dropdown when clicked outside
                if (!self.$el.contains(e.target)){
                    self.all = false
                }
            })
        },

        methods: {
            toggle: function() {
                this.all = ! this.all;
            }

        }
    }
</script>

<style>
    [v-cloak] {
        display: none;
    }
</style>