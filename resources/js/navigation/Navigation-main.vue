<template>
    <div class="relative z-10 flex">
        <bell :user="user" />

        <button id="navbarDropdown" class="nav-link radio">
            <li @click="toggle" class="whitespace-nowrap flex">
                <span class="nav-link">
                    {{ user.organization.title }}
                </span>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 mt-1"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </li>
        </button>

        <ul
            v-if="open"
            @click="toggle"
            class="dropdown-menu hidden absolute top-7"
        >
            <a :href="'/admin/home'" v-if="user.isSuperadmin">
                <li class="dropdown-item">
                    Admin
                </li>
            </a>
            <a :href="'/profile/home'">
                <li class="dropdown-item">
                    Profil
                </li>
            </a>

            <li title="divider"></li>

            <li class="dropdown-item">
                <a
                    :href="'logout'"
                    onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();"
                >
                    Odhlásiť
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
import axios from "axios";
import { createdMixin } from "../mixins/createdMixin";
import Bell from "./Bell.vue";

export default {
    components: { Bell },
    mixins: [createdMixin],
    data() {
        return {
            open: false,
            user: ""
        };
    },
    methods: {
        toggle: function() {
            this.open = !this.open;
        },
        getUser() {
            axios.get("/api/user").then(response => {
                this.user = response.data;
            });
        }
    },

    created() {
        this.getUser();
    }
};
</script>
