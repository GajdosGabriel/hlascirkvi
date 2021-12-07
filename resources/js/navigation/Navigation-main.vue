<template>
    <div class="relative z-10">
        <a id="navbarDropdown" class="nav-link radio" href="#">
            <li @click="toggle" class="whitespace-nowrap">
                <span class="nav-link">
                    {{ user.full_name }}
                </span>
                <i class="fas fa-caret-down"></i>
            </li>
        </a>

        <ul v-if="open" @click="toggle" class="dropdown-menu hidden">
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

export default {
    mixins: [createdMixin],
    data() {
        return {
            open: false,
            user: "",
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
        this.getUser()
    }
};
</script>
