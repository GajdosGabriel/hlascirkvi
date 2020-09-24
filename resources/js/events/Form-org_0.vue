<template>
    <div>

        <div class="form-group">
            <label for="village">Obec podujatia</label>
            <input @input="getVillages" type="text" name="village" v-model="searchVillage" id="village" placeholder="Vyberte obec" class="form-control" required>
        </div>

        <ul v-if="list">
            <li :key="village.id" v-for="village in villages">{{ village.fullname }} {{ village.zip }}</li>
        </ul>

    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                list:true,
                villages: '',
                searchVillage:''
            }
        },

        methods: {
            toggle: function() {
                this.list = ! this.list;
            },

            getVillages: function() {
                if(this.searchVillage.length < 1 ) {
                    this.list = false;
                    return;
                }
                this.list = true;
                axios.get('/village/' + this.searchVillage)
                .then( response => { this.villages = response.data
                    })
            }

        }
    }
</script>
