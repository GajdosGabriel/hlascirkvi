
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
   }

}
</script>

