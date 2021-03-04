<template>
    <div class="flex justify-between w-full">

        <button @click="publishVideo" class="btn-small hover:bg-green-300">Zverejniť</button>
        <button @click="youtubeBlocked" class="btn-small hover:bg-green-300">Blokovať</button>
        <!--        <a href="{{ route('admin.bufferedVideosPublish', [$post->id]) }}">Zverejniť</a>-->
        <!--        @can('admin')-->
        <!--        <a href="{{ route('admin.youtubeBlocked', [$post->id]) }}">Blokovať</a>-->
        <!--        @endcan-->
    </div>
</template>

<script>
    export default {
        props: ['post'],
        methods: {
            publishVideo() {
                axios.post('/admin/publish/buffer', { post: this.post.id}).then(
                    location.reload()
                )
            },

            youtubeBlocked() {
                axios.post('/admin/youtubeBlocked/blocked', { post: this.post.id}).then(
                    location.reload()
                )
            }
        }

    }
</script>
