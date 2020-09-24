<template>

    <li v-if="notifications.length" @click="toggle" title="Máte nové upozornenia." class="nav-item dropdown"><i style="cursor: pointer" :class="bellClass"></i>
        <ul v-if="dropDown" class="dropdown-menu" >
            <span class="ul-style">Označiť ako prečítané!</span>
            <li v-for="notification in notifications">
                <span class="ul-style"><i class="fas fa-circle"></i></span>
                <a class="dropdown-item" :href="notification.data.link" v-text="notification.data.message" @click="markAsRead(notification)"></a>

            </li>
        </ul>
    </li>

</template>

<script>
    export default {
        data: function() {
            return {
                notifications: false,
                dropDown: false
            }
        },
        created: function() {
            axios.get('/user/profiles/notification/unread')
                    .then(response => this.notifications = response.data)


            let self = this;
            window.addEventListener('click', function(e){
                // close dropdown when clicked outside
                if (!self.$el.contains(e.target)){
                    self.dropDown = false
                }
            })
        },
        computed: {
            bellClass: function() {
                return ['fas fa-bell', this.notifications.length ? ' favorited' : '' ]
            }
        },

        methods: {
            toggle: function () {
                this.dropDown = ! this.dropDown;
            },

            markAsRead: function(notification) {
                axios.get('/user/profiles/' + notification.id + '/markAsRead');
            }

        }
    };;
</script>
<style>
    .favorited {
        color: rgb(255, 49, 14);
    }
    .ul-style {
        color: #7d7d7d; font-size: 85%; margin-top: -1rem; cursor: pointer
    }

</style>