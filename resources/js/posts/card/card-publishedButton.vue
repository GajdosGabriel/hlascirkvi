<template>
    <div class="flex justify-between w-full">
        <button @click="publishVideo" class="btn-small hover:bg-red-400 bg-red-500 text-gray-100">Zverejniť</button>
    </div>
</template>

<script>
    export default {
        props: ['post'],
        methods: {
            publishVideo() {
                axios.put('/api/posts/' + this.post.id, { published: Date.now() }).then(
                    location.reload()
                )
            }
        }

    }
</script>
