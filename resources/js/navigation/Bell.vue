<template>
    <div @click="resetNotifyBell" class="relative mr-3">
        <div class="flex">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 cursor-pointer"
                :class="bellClass"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <div
                class="w-5 h-5 bg-red-500 text-white rounded-full flex justify-center items-center"
                v-if="user.countNotifycation > 0"
            >
                <span class="pb-1">{{ user.countNotifycation }}</span>
            </div>
        </div>

        <div v-if="open" class="fixed inset-0 h-full w-full z-10"></div>

        <ul
            v-if="open"
            class="absolute right-0 mt-2 bg-white rounded-md shadow-lg overflow-hidden z-20"
            style="width:20rem;"
        >
            <li
                  class=" px-4 py-3 border-b hover:bg-gray-100 -mx-2"
                v-for="notification in user.notifications"
                :key="notification.id"
            >
                <a
                    :href="notification.data.link"
                    @click="markAsRead(notification)"
                  
                >
                    <!-- Initial name -->
                    <div
                        v-if="notification.data.logo"
                        class="mr-3 h-12 w-12 text-gray-700 bg-gray-300 rounded-full flex items-center justify-center font-semibold text-lg float-left"
                    >
                        {{ notification.data.logo }}
                    </div>

                    <!-- <img v-else
                        class="h-10 w-20 rounded-full object-cover mx-1"
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=334&q=80"
                        alt="avatar"
                    /> -->

                    <div
                        class="text-gray-600 text-sm mx-2 "
                        :class="{ 'font-semibold': !notification.read_at }"
                        v-text="notification.data.message"
                    ></div>
                </a>
            </li>
            <button
                class="block bg-gray-800 text-white text-center font-bold py-2 w-full"
            >
                See all notifications
            </button>
        </ul>
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
        }
    }
};
</script>
