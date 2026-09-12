<template>
    <div class="relative">
        <button
            type="button"
            class="relative flex h-9 w-9 items-center justify-center rounded-md text-blue-100 transition-colors hover:bg-blue-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300"
            :aria-expanded="open ? 'true' : 'false'"
            :aria-label="ariaLabel"
            @click="resetNotifyBell"
        >
            <svg
                class="h-5 w-5"
                :class="bellClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <span
                v-if="user.countNotifycation > 0"
                class="absolute right-0.5 top-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-blue-900"
            >
                {{ badgeLabel }}
            </span>
        </button>

        <div v-if="open" class="fixed inset-0 z-10" @click="open = false"></div>

        <div
            v-if="open"
            class="absolute right-0 top-full z-20 mt-2 w-80 max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-md border border-gray-200 bg-white shadow-xl"
        >
            <ul class="max-h-96 divide-y divide-gray-100 overflow-y-auto">
                <li
                    v-for="notification in user.notifications"
                    :key="notification.id"
                >
                    <a
                        :href="notification.data.link"
                        class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-gray-50"
                        @click="markAsRead(notification)"
                    >
                        <!-- Iniciály organizácie -->
                        <span
                            v-if="notification.data.logo"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-700"
                        >
                            {{ notification.data.logo }}
                        </span>

                        <span
                            class="text-sm leading-snug text-gray-600"
                            :class="{ 'font-semibold text-gray-900': !notification.read_at }"
                            v-text="notification.data.message"
                        ></span>
                    </a>
                </li>

                <li
                    v-if="!user.notifications || user.notifications.length === 0"
                    class="px-4 py-6 text-center text-sm text-gray-500"
                >
                    Žiadne notifikácie
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { createdMixin } from "../mixins/createdMixin";
export default {
    props: ["user"],
    mixins: [createdMixin],
    data: function() {
        return {
            open: false
        };
    },
    methods: {
        toggle: function() {
            this.open = !this.open;
        },

        markAsRead: function(notification) {
            axios.put("/api/notifications/" + notification.id);
        },

        resetNotifyBell: function() {
            // Bežne date zapisuje o 2 hod. menej
            var dt = new Date();
            dt.setHours(dt.getHours() + 2);

            if (this.user.countNotifycation == 0) {
                return this.toggle();
            }

            axios.put("/api/users/" + this.user.id, {
                notify_bell: dt
            });
            //     .then(response => (this.notifications = response.data));
            this.toggle();
        }
    },

    computed: {
        bellClass: function() {
            return [this.user.notify_bell > 0 ? " text-red-400" : ""];
        },

        badgeLabel: function() {
            return this.user.countNotifycation > 99
                ? "99+"
                : this.user.countNotifycation;
        },

        ariaLabel: function() {
            return this.user.countNotifycation > 0
                ? "Notifikácie (" + this.user.countNotifycation + " nových)"
                : "Notifikácie";
        }
    }
};
</script>
