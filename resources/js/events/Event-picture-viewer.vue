<script>
import { createdMixin } from "../mixins/createdMixin";
export default {
    mixins: [createdMixin],
    data() {
        return {
            open: false
        };
    },
    methods: {
        showModal() {
            this.open = !this.open;
        }
    }
};
</script>
