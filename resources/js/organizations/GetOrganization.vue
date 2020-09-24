<template>
    <div>

        <div class="form-group">
            <label>Organizátor podujatia vue</label>
            <select class="form-control" v-model="selected" required>
                <option disabled value="" selected hidden>---Vybrať---</option>
                <option v-for="organization in organizations" :value="organization">{{ organization.title }}</option>

            </select>
        </div>

        <div class="form-group">
            <input hidden type="number" v-model="selected.id" name="organization_id" class="form-control input-sm" >

        </div>

        <div class="form-group">
            <label for="">Ulica, miesto konania</label>
            <input type="text" v-model="selected.street" name="street"  placeholder="Adresa konania" class="form-control input-sm" >
        </div>

        <div class="form-group">
        <label for="">Mesto</label>
        <input type="text" v-model="selected.city" name="city" placeholder="Mesto/obec konania" class="form-control input-sm" >
        </div>





        <!--<div>{{ selected }}</div>-->
        <!--<div>{{ street }}</div>-->
        <!--<div>{{ city }}</div>-->
    </div>
</template>

<script>
    export default {
        props: ['organizations'],
        data: function() {
            return {
                street: null,
                city: null,
                title: null,
                selected: ''
            }
        },

        methods: {
            insert: function(organization) {
                this.title = organization.title;
                this.street = organization.street;
                this.city = organization.city;
            },
            test: function(event) {
                console.log(organization);
            }
        }
    }
</script>