<template>
    <div class="relative z-10 px-2">
        <i
            style="float: right"
            @click="toggle"
            title="Spravovať článok"
            class="fas fa-ellipsis-v cursor-pointer"
        ></i>
        <ul class="dropdown-menu" v-if="open">
            <a :href="'/posts/' + post.id + '/edit'">
                <li class="dropdown-item">
                    upraviť
                </li>
            </a>

            <li @click="deletePost" class="dropdown-item">
                zmazať
            </li>

            <a
                v-if="$auth.isAdmin()"
                :href="'/post/unpublished/' + post.id + '/video'"
            >
                <li class="dropdown-item whitespace-nowrap">
                    Do buffer
                </li>
            </a>
        </ul>
    </div>
</template>
<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],
    props: ["post"],
    data: function() {
        return {
            open: false
        };
    },

    methods: {
        toggle: function() {
            this.open = !this.open;
        },

        deletePost: function() {
            axios
                .delete("/posts/" + this.post.id)
                .than((window.location.href = "/"));
        }
    },

    computed: {
        authUser: function() {
            return window.App;
        }
    }
};
</script>
