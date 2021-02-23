<script>
    import {createdMixin} from "../mixins/createdMixin";

    export default {
        mixins:[createdMixin],
        data: function() {
            return {
                open:false
            }
        },

        methods: {
            toggle: function() {
                this.open = ! this.open;
            }

        }
    }
</script>

<style>

    [v-cloak] > * { display:none }

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
