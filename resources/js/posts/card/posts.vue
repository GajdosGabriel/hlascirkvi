<template>
    <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
<!--        <div v-for="post in posts" :key="post.id"  class="border-2 border-gray-400 rounded-md shadow-md relative text-xs md:text-sm">-->
<!--            <card :post="post"></card>-->
<!--        </div>-->
    </div>
</template>

<script>
    import card from './card'

    export default {
        props:['posts'],
        components: {card},
        data() {
            return {
                // posts: []
            }
        },
        methods: {
            // getPosts() {
            //     axios.get('/api/posts').then(
            //         (response) => {
            //             this.posts = response.data.data
            //         }
            //     )
            // }
        },
        created() {
            // this.getPosts();
        }

    }
</script>
