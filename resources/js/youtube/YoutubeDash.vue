<template>
    <div class="YoutubeDash__wrapper">
        <!--<finder></finder>-->

        <video-group :videos="videos"></video-group>

    </div>
</template>

<script>
    import VideoGroup from './VideoGroup.vue';
    import Search from './Search';

    export default {
        props:['user'],
        components: {
            VideoGroup
        },

        created: function() {
            Search({
                apiKey: 'AIzaSyBy0rsl9DUYGGKT7OoEDiXb8_7V7hWL7cE',
                term: this.user,
                items: 20
            }, response => this.handleSearchResults(response));

        },

        data:function() {
            return {
                videos: null,
                loading: true
            }
        },

        methods: {
            handleSearchResults: function(result) {
                this.loading = false;
                this.videos = result;
            }
        }
    }
</script>