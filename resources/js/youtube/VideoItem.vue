<template>
    <div class="card">
        <a :href="/post/ + videoUrl">
            <img class="card-img-top" v-bind:src="videoImage" v-bind:alt="videoTitle">
        </a>
        <div class="card-body">
            <span class="card-title" v-text="videoTitle"></span>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['video'],

        computed: {
                videoTitle: function() {
            return this.video.snippet.title;
            },
            videoDescription: function() {
                return this.video.snippet.description;
            },
            videoImage: function() {
                return this.video.snippet.thumbnails.high.url;
            },
            videoUrl: function() {
                return this.video.id.videoId;
            }
        }
    }
</script>