<template>

    <form @submit.prevent="sendMessage">
        <div class="level">
            <input style="width: 100%" name="body" v-model="body" class="form-control" placeholder="Sem píšte text" required>
            <button class="btn btn-small">Odoslať</button>
        </div>
    </form>

</template>
<script>
    export default {
        props:['user'],
        data: function() {
            return {
                body: this.body

            }
        },

        methods: {
            sendMessage: function() {
                axios.post('/user/message/' + this.user.id + '/newMessage', { body:this.body } )
                        .then(({data}) => {
                    this.$emit('created' , data);
                    this.body='';
            })
            }

        }
    }
</script>