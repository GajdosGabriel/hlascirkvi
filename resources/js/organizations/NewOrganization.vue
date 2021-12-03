<template>
                   <div>
                        <h4 style="margin: 2rem 0rem; cursor: pointer" @click="toggle">Nová organizácia
                            <i v-if="!showForm" class="far fa-plus-square"></i>
                            <i v-if="showForm" class="far fa-minus-square"></i>
                        </h4>

                        <form method="post" action="" v-if="showForm">
                            <div class="form-group">
                                <label>Meno novej organizácie</label>
                                <input type="text" name="title" class="form-control" placeholder="Názov organizácie" required>
                            </div>

                            <div class="form-group">
                                <label>Ulica a číslo</label>
                                <input type="text" name="street" class="form-control" placeholder="Ulica a číslo">
                            </div>

                            <div class="form-group">
                                <label for="">Mesto</label>
                                <select name="village_id" class="form-control input-sm">
                                    <option label="Vybrať"></option>

                                    <!-- @foreach(\App\Village::all() as $village)
                                    <option value="{{ $village->id }}">{{ $village->fullname }}  {{ $village->zip }}</option>
                                    @endforeach -->
                                </select>
                            </div>


                            <div class="form-group">
                                <label>Telefón</label>
                                <input type="number" name="phone" class="form-control"
                                    placeholder="Potrebné v prípade vytvorenia akcie">
                            </div>


                            <span style="font-weight: 600">Zaradená do zoznamu</span><br>
                            <!-- @forelse(\App\Updater::all() as $updater)
                                @if ($updater->type == 'denomination')
                                    <input type="radio" required name="updaters[]" value="{{ $updater->id }}">
                                    {{ $updater->title }}<br>
                                @endif
                            @empty
                                žiadny tag
                            @endforelse -->


                            <div class="form-group" style="text-align: right">
                                <button class="btn btn-small">Uložiť</button>
                            </div>

                        </form>
                    </div>
</template>
<script>
    import {bus} from '../app';
    export default {
        props: ['user'],
        data: function() {
            return {
                showForm: false,
                title: '',
                street: '',
                city: '',
                village: '',
                phone: ''
            }
        },

        methods: {
            toggle: function () {
              this.showForm = ! this.showForm;
            },

            saveOrganization: function() {
                axios.post('/organization/new/' + this.user.id + '/store', {
                    title: this.title,
                    street: this.street,
                    village_id: this.village_id,
                    phone: this.phone
                }).then( function () {
                    bus.$emit('flash', {body:'Organizácia bola uložená!'});
                    location.reload();

                });

                this.clearForm();
            },

            clearForm: function() {
                this.title='',
                this.street= '',
                this.village_id='',
                this.phone= '',
                this.showForm = false
            }
        }
    }
</script>
