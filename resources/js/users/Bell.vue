<template>
    <li v-if="notifications" @click="toggle" class="relative mr-3">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 "
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
            v-if="dropDown"
            @click="dropdownOpen = false"
            class="fixed inset-0 h-full w-full z-10"
        ></div>

        <ul
            v-if="dropDown"
            class="absolute right-0 mt-2 bg-white rounded-md shadow-lg overflow-hidden z-20"
            style="width:20rem;"
        >
            <li
                class="py-2"
                v-for="notification in notifications"
                :key="notification.id"
            >
                <a
                    :href="notification.data.link"
                    @click="markAsRead(notification)"
                    class="flex items-center px-4 py-3 border-b hover:bg-gray-100 -mx-2"
                >
                    <!-- Initial name -->
                    <div
                    v-if="notification.data.logo"
                        class="h-12 w-12 text-gray-700 bg-gray-300 rounded-full flex items-center justify-center font-semibold text-2xl"
                    >
                        {{ notification.data.logo }}
                    </div>

                    <img v-else
                        class="h-10 w-20 rounded-full object-cover mx-1"
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=334&q=80"
                        alt="avatar"
                    />

                    <p
                        class="text-gray-600 text-sm mx-2"
                        v-text="notification.data.message"
                    >
                        <span class="font-bold" href="#">Sara Salah</span>
                        replied on the
                        <span class="font-bold text-blue-500" href="#"
                            >Upload Image</span
                        >
                        artical . 2m
                    </p>
                </a>
            </li>
            <a
                href="#"
                class="block bg-gray-800 text-white text-center font-bold py-2"
                >See all notifications</a
            >
        </ul>
    </li>
</template>

<script>
export default {
    data: function() {
        return {
            notifications: [],
            dropDown: false
        };
    },
    created: function() {
        axios
            .get("/user/profiles/notification/unread")
            .then(response => (this.notifications = response.data));

        let self = this;
        window.addEventListener("click", function(e) {
            // close dropdown when clicked outside
            if (!self.$el.contains(e.target)) {
                self.dropDown = false;
            }
        });
    },
    computed: {
        bellClass: function() {
            return [this.notifications.length ? " text-red-400" : ""];
        }
    },

    methods: {
        toggle: function() {
            this.dropDown = !this.dropDown;
        },

        markAsRead: function(notification) {
            axios.get("/user/profiles/" + notification.id + "/markAsRead");
        }
    }
};
</script>
<style>
.favorited {
    color: rgb(255, 49, 14);
}
.ul-style {
    color: #7d7d7d;
    font-size: 85%;
    margin-top: -1rem;
    cursor: pointer;
}
</style>
