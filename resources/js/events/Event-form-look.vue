<template>

    <form @submit.prevent="sendComment">
        <div class="form-group">
            <textarea class="border-2 border-gray-500 rounded-md p-2 block w-full" v-model="body" rows="3" placeholder="Hľadám na akciu, napr. dopravu, ubytovanie, pomoc s deťmi, sponzora a pod." required></textarea>
        </div>

        <div v-if="emptyBody" style="color: red;margin-top: -2rem;font-size: 90%;">
            <span>Text je príliš krátky min. 7 znakov.</span>
        </div>

        <div v-if="showEmail" >
            <input type="email" v-model="email" placeholder="Email na odpoveď..." required> <br>
            <!--<span>Uvedte svoj email, pre odpoveď!</span>-->
        </div>

        <button class="px-2 border-2 border-gray-500 rounded-md mt-3">Uložiť</button>

    </form>
</template>

<script>

    export default {
          props: ['event'],
        data: function() {
            return {
                // endpoint:location.pathname + '/newComment',
                body:'',
                type: '',
                disabled: window.App.signedIn,
                emptyBody: false,
                email: ''
            }
        },

        computed: {
            signedIn: function() {
                if( window.App.signedIn) {
                    return 'btn btn-small';
                } else {
                    return 'btn btn-small disabled';
                }
            },

            showEmail: function() {
                return !window.App.signedIn && this.body.length >5
            }
        },

        methods:{
            sendComment: function() {
                if(this.body.length < 7) {
                   this.emptyBody = true;
                    return false;
                }

                this.emptyBody = false;

                if(!this.disabled) {
                    this.emptyEmail = true;
                }

//                if(this.emailInput.length < 5 ) {
//                    return false;
//                }


//                this.emptyEmail = false;

                axios.post('/comments', {body: this.body, type: 'look', model:'Event', model_id: this.event.id , email:this.email})
                        .then( ({data}) => {

                    this.body='';
                    this.$emit('created' , data)
            })
            }
        }
    }
</script>
