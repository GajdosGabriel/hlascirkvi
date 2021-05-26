
<script>
export default{

   data(){
       return {
           showButton: false
       }
   },
   methods: {
       showModal(){
           this.showButton = ! this.showButton;
       }
   },

   created: function(){
        let that = this;
        document.addEventListener('keyup', function (evt) {
            if (evt.keyCode === 27) {
                that.showButton = false;
            }
        });
   }

}
</script>

