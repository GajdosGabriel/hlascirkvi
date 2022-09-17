<template>
    <div class="flex justify-between w-full">
        <button @click="publishVideo" class="btn-small hover:bg-green-300">Zverejniť</button>
        <button @click="youtubeBlocked" class="btn-small hover:bg-red-300">Blokovať</button>
    </div>
</template>

<script>
    export default {
        props: ['post'],
        methods: {
            publishVideo() {
                axios.put('/api/posts/' + this.post.id, { idUpdater: 15}).then(
                    location.reload()
                )
            },

            youtubeBlocked() {
                axios.put('/api/posts/' + this.post.id, { youtube_blocked: 1}).then(
                    location.reload()
                )
            }
        }

    }
</script>
