<template>
    <li v-if="notifications" @click="toggle" class="relative mr-3">
        <i class="cursor-pointer" :class="bellClass"></i>
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
                     :href="notification.data.link" @click="markAsRead(notification)"
                    class="flex items-center px-4 py-3 border-b hover:bg-gray-100 -mx-2"
                >
                    <img
                        class="h-8 w-8 rounded-full object-cover mx-1"
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=334&q=80"
                        alt="avatar"
                    />

                    <!-- <span class="ul-style"><i class="fas fa-circle"></i></span> -->

                    <p class="text-gray-600 text-sm mx-2" v-text="notification.data.message" >
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
            notifications: false,
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
            return [
                "fas fa-bell",
                this.notifications.length ? " favorited" : ""
            ];
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
