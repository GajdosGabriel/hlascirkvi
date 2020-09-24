<template>
    <div class="card">
        <organization-card-header :user="user"></organization-card-header>
        <!--<div style="font-weight: 600; text-align: center" v-text="user.title"></div>-->
        <div style="text-align: center" class="card-body">
            <img v-if="user.avatar" style="width: 50%" :src=" this.domain + 'storage/users/' + user.id + '/' + user.avatar">
            <i v-else style="font-size: 18rem; color: silver" class="far fa-image"></i>
        </div>

        <organization-card-footer :user="user"></organization-card-footer>
    </div>

</template>

<script>
    import OrganizationCardHeader from './Organization-card-header.vue';
    import OrganizationCardFooter from './Organization-card-footer.vue';
    export default {
        props: ['user'],
        components: {OrganizationCardFooter, OrganizationCardHeader},
        data: function() {
            return {
                domain: window.App.baseUrl

            }
        }
    }
</script>