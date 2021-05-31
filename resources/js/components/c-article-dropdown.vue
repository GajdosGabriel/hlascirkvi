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
        <ul class="dropdown-menu" v-if="open">
            <a :href="model + post.id + '/edit'">
                <li class="dropdown-item">
                    upraviť
                </li>
            </a>

            <li @click="deletePost" class="dropdown-item">
                zmazať
            </li>
        </ul>
    </div>
</template>
<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],
    // props: ["post", "model"],

    props:{
        post: {
            type: Object,
            require: true
        },

        model: {
            type: String,
            require: true
        },
        redirect: {
            type: String,
            default:'/',
            require: false
        }
    },
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
                .delete(this.model + this.post.id)
                .then( window.location.href = this.redirect);
        }
    },

    computed: {
        authUser: function() {
            return window.App;
        }
    }
};
</script>
