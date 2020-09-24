<script>
    // Počíta návštevy na online prenosy.
    export default {
        props: ['post', 'idpost'],
        data: function() {
            return {
                linksrc:"//www.youtube.com/embed/" + this.post + "?rel=0;modestbranding=1?rel=0;modestbranding=1;autoplay=1",
                showvideo: false
            }
        },
        methods: {

            toggle: function() {
              this.showvideo = ! this.showvideo;
            },
            sendPostShow: function() {
                this.toggle();
                axios.post('/post/' + this.idpost + '/slug-to-video/' );
            }
        }
    }
</script>