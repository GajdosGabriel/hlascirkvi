<template>
    <div>
        <!--<div v-for="video in videos" >-->
            <!--<h4 v-if="show">Toto video</h4>-->
            <!--<img @click="toggle" :src="video.snippet.thumbnails.high.url" :key="video.snippet.resourceId.videoId">-->
            <!--<a><p v-text="video.snippet.title">bb</p></a>-->
        <!--</div>-->

        <div v-for="video in videos" >
            <div v-if="show">
                <p>nahľad</p>
                <img @click="toggle(video.snippet.resourceId.videoId)" :src="video.snippet.thumbnails.high.url" :key="video.snippet.resourceId.videoId">
                <a><p v-text="video.snippet.title">bb</p></a>
            </div>

            <div v-if="!show">
                <p>video</p>
                <img @click="toggle(video.snippet.resourceId.videoId)" :src="video.snippet.thumbnails.high.url" :key="video.snippet.resourceId.videoId">
                <a><p v-text="video.snippet.title">bb</p></a>
            </div>

        </div>



    </div>
</template>
<script>
    export default {
        data: function() {
            return {
                videos: {},
                show: true

            }
        },
        created: function() {
                    axios.get('/hanusove-dni/')
                    .then((response) => this.videos = response.data)
                    .catch((error) => console.log(error));
        },

        methods: {
            toggle: function(videoID) {
                alert(videoID);
                this.show = ! this.show;
            }
        }
    }
</script>