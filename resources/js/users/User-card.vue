<template>
    <div class="card">
        <!--<user-card-header :user="user"></user-card-header>-->
        <span style="float: right; margin-right: 1rem" v-text="user.title"></span>
        <img v-if="user.avatar" style="width: 50%" :src=" this.domain + 'storage/users/' + user.id + '/' + user.avatar">
        <img v-else style="width: 40%" :src="this.domain + 'images/avatar.png'">
        <user-card-footer :user="user"></user-card-footer>
    </div>

</template>

<script>
    import UserCardHeader from './User-card-header.vue';
    import UserCardFooter from './User-card-footer.vue';
    export default {
        props: ['user'],
        components: {UserCardFooter, UserCardHeader},
        data: function() {
            return {
                domain: window.App.baseUrl

            }
        }
    }
</script>