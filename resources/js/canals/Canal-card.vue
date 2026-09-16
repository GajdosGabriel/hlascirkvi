<template>
    <div class="card">
        <canal-card-header :user="user"></canal-card-header>
        <!--<div style="font-weight: 600; text-align: center" v-text="user.title"></div>-->
        <div style="text-align: center" class="card-body">
            <img v-if="user.avatar" style="width: 50%" :src=" this.domain + 'storage/users/' + user.id + '/' + user.avatar">
            <i v-else style="font-size: 18rem; color: silver" class="far fa-image"></i>
        </div>

        <canal-card-footer :user="user"></canal-card-footer>
    </div>

</template>

<script>
    import CanalCardHeader from './Canal-card-header.vue';
    import CanalCardFooter from './Canal-card-footer.vue';
    export default {
        props: ['user'],
        components: {CanalCardFooter, CanalCardHeader},
        data: function() {
            return {
                domain: window.App.baseUrl

            }
        }
    }
</script>