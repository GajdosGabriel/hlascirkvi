<template>
    <div v-if="user" class="relative z-10 flex items-center gap-1">
        <bell :user="user" />

        <button
            type="button"
            class="flex items-center gap-1 rounded-md px-2 py-1.5 text-sm font-medium text-blue-100 transition-colors hover:bg-blue-800 hover:text-white"
            :aria-expanded="open ? 'true' : 'false'"
            @click="toggle"
        >
            <span class="max-w-[9rem] truncate">{{ organization.title }}</span>
            <svg
                class="h-4 w-4 shrink-0 transition-transform"
                :class="{ 'rotate-180': open }"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>

        <div
            v-show="open"
            class="absolute right-0 top-full z-50 mt-2 w-48 overflow-hidden rounded-md border border-gray-200 bg-white py-1 text-gray-700 shadow-xl"
        >
            <a
                v-if="user.isSuperadmin"
                href="/admin/home"
                class="block px-4 py-2 text-sm transition-colors hover:bg-gray-100 hover:text-gray-900"
            >
                Admin
            </a>

            <a
                href="/profile"
                class="block px-4 py-2 text-sm transition-colors hover:bg-gray-100 hover:text-gray-900"
            >
                Profil
            </a>

            <hr class="my-1 border-gray-200" />

            <a
                href="/logout"
                class="block px-4 py-2 text-sm transition-colors hover:bg-gray-100 hover:text-gray-900"
                @click.prevent="logout"
            >
                Odhlásiť
            </a>
        </div>
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
            user: "",
            organization: ""
        };
    },

    methods: {
        toggle: function() {
            this.open = !this.open;
        },

        logout() {
            document.getElementById("logout-form").submit();
        },

        getUser() {
            axios.get("/api/user").then(response => {
                this.user = response.data;
                this.organization = response.data.organization;
            });
        }
    },

    created() {
        this.getUser();
    }
};
</script>
