<template>
    <div class="VideoGroup__wrapper">

        <video-item
                v-images-loaded:on.progress="imageProgress"
                v-for="video in videos"
                :video="video"
                :key="video.id.videoId"
        >
        </video-item>

    </div>
</template>

<script>
    import Isotope from 'isotope-layout';
    import imagesLoaded from 'vue-images-loaded';
    import VideoItem from './VideoItem.vue'
    export default {
        props: ['videos'],
        components: {
            VideoItem
        },
        directives: {
            imagesLoaded
        },
        created: function() {
    },
    data: function() {
        return {
            isotope: null,
            counter: 0
        }
    },
    methods: {
        relayoutTheGrid: function() {
            setTimeout(() => {
                    var elem = document.querySelector('.grid');
                    console.log(elem);
                    this.isotope = new Isotope( elem, {
                        itemSelector: '.card',
                        layoutMode: 'masonry'
                    });
                }, 1);
            },
            imageProgress: function(instance, img) {
                this.counter++;
                if (this.counter == this.videos.length) {
                    this.relayoutTheGrid();
                }
            }
        }
    }
</script>