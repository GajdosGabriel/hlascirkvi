<template>
    <div class="relative z-10 px-2">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-7 w-7 cursor-pointer bg-gray-100 rounded-full text-gray-400 hover:bg-gray-200 p-1"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
             @click="toggle"
            title="Spravovať článok"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
            />
        </svg>
        <ul class="dropdown-menu bg-red-800 z-20 " v-if="open">
            <a :href="'/tags/' + post.id + '/edit'">
                <li class="dropdown-item  bg-white z-20 ">
                    upraviť
                </li>
            </a>

            <li @click="deletePost" class="dropdown-item bg-white z-20">
                zmazať
            </li>

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
                .delete("/tags/" + this.post.id)
                .then( window.location.href = "/tags" );
        }
    },

    computed: {
        authUser: function() {
            return window.App;
        }
    }
};
</script>
