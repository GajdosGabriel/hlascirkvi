<script>
    export default {
        data: function() {
            return {
                all:false
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

    li {
        display: block;
        transition-duration: 0.2s;
    }

    li:hover {
        cursor: pointer;
    }

    ul li ul {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        transition: all 0.3s ease;
        margin-top: 1rem;
        left: 0;
        display: none;
    }

    ul li:hover > ul,
    ul li ul:hover {
        visibility: visible;
        opacity: 1;
        display: block;
    }

    ul li ul li {
        clear: both;
        width: 100%;
    }

</style>
