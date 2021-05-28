<template>
    <div class="relative z-10">
        <a id="navbarDropdown" class="nav-link radio" href="#">
            <li @click="toggle" class="whitespace-nowrap">
                <span class="nav-link">
                    {{ authUser.user.fullname }}
                </span>
                <i class="fas fa-caret-down"></i>
            </li>
        </a>

        <ul v-if="open" @click="toggle" class="dropdown-menu hidden">
            <a :href="'/admin/home'" v-if="$auth.isAdmin()">
                <li class="dropdown-item">
                    Admin
                </li>
            </a>
            <a
                :href="
                    '/profiles/' +
                        authUser.user.id +
                        '/home'
                "
            >
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
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],
    data() {
        return {
            open: false
        };
    },

    methods: {
        toggle: function() {
            this.open = !this.open;
        }
    },
    computed: {
        authUser: function() {
            return window.App;
        }
    }
};
</script>
