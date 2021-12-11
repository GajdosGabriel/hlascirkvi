<template>
    <div>
        <h4 style="margin: 2rem 0rem; cursor: pointer" @click="toggle">
            Nová organizácia
            <i v-if="!showForm" class="far fa-plus-square"></i>
            <i v-if="showForm" class="far fa-minus-square"></i>
        </h4>

        <form @submit.prevent="saveOrganization" v-if="showForm">
            <div class="form-group">
                <label>Meno novej organizácie</label>
                <input
                    type="text"
                    v-model="form.title"
                    class="form-control"
                    placeholder="Názov organizácie"
                    required
                />
            </div>

            <div class="form-group">
                <label>Ulica a číslo</label>
                <input
                    type="text"
                    v-model="form.street"
                    class="form-control"
                    placeholder="Ulica a číslo"
                />
            </div>

            <div class="form-group">
                <label for="">Mesto</label>
                <select v-model="form.village_id" class="form-control input-sm">
                    <option label="Vybrať"></option>
                    <option
                        v-for="village in villages"
                        :key="village.id"
                        :value="village.id"
                        >{{ village.fullname }} {{ village.zip }}</option
                    >
                </select>
            </div>

            <div class="form-group">
                <label>Telefón</label>
                <input
                    type="number"
                    v-model="form.phone"
                    class="form-control"
                    placeholder="Potrebné v prípade vytvorenia akcie"
                />
            </div>

            <span style="font-weight: 600">Zaradená do zoznamu</span><br />

            <div v-for="updater in updaters" :key="updater.id">
                <input
                    type="radio"
                    required
                    name="updaters"
                    v-model="formupdaters"
                    :id="updater.id"
                />
                 <label :for="updater.id">{{ updater.title }}</label>
            </div>

            <!-- @forelse(\App\Updater::all() as $updater)
                                @if ($updater->type == 'denomination')
                                    <input type="radio" required name="updaters[]" value="{{ $updater->id }}">
                                    {{ $updater->title }}<br>
                                @endif
                            @empty
                                žiadny tag
                            @endforelse -->

            <div class="form-group">
                <button class="btn btn-primary">Uložiť</button>
            </div>
        </form>
    </div>
</template>
<script>
import axios from "axios";
export default {
    data: function() {
        return {
            showForm: true,
            user: "",
            villages: [],
            updaters: [],
            formupdaters:[],
            form: {
                title: "",
                street: "",
                city: "",
                village: "",
                phone: "",
                village_id: "",
            }
        };
    },

    methods: {
        toggle: function() {
            this.showForm = !this.showForm;
        },

        fetchVillage: function() {
            axios.get("/api/villages").then(response => {
                this.villages = response.data;
            });
        },

        fetchUser: function() {
            axios.get("/api/user").then(response => {
                this.user = response.data;
            });
        },

        fetchUpdaters: function() {
            axios.get("/api/updaters").then(response => {
                this.updaters = response.data;
            });
        },

        saveOrganization: function() {
            axios
                .post(
                    "/api/users/" + this.user.id + "/organizations",
                    this.form
                )
                .then(function() {
                    // bus.$emit("flash", { body: "Organizácia bola uložená!" });
                    // location.reload();
                });

            this.clearForm();
        },

        clearForm: function() {
            this.form = {};
        }
    },
    created() {
        this.fetchVillage();
        this.fetchUser();
        this.fetchUpdaters();
    }
};
</script>
