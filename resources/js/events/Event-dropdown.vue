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
            <a :href="'/organization/'+ post.organization_id + '/event/' + post.id + '/edit'">
                <li class="dropdown-item">
                    upraviť
                </li>
            </a>

            <li @click="deletePost" class="dropdown-item">
                zmazať
            </li>

            <a
                v-if="$auth.isAdmin()"
                :href="'/event/' + post.id + '/subscribe'"
            >
                <li class="dropdown-item whitespace-nowrap">
                    Administrácia
                </li>
            </a>

            <a
                v-if="$auth.isAdmin() && post.orginal_source"
                :href="'/api/eventServices/' + post.id + '/newReolad'"
            >
                <li class="dropdown-item whitespace-nowrap">
                    New reload
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
                .delete('/organization/'+ this.post.organization_id +'/event/' + this.post.id)
                .than((window.location.href = '/organization/'+ this.post.organization_id +'/event/'), this.toggle());
        }
    },

    computed: {
        authUser: function() {
            return window.App;
        }
    }
};
</script>
