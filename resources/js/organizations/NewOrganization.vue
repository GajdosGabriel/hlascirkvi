<template>
    <div>
        <h4 style="margin: 2rem 0rem; cursor: pointer" @click="toggle">
            Nový kanál
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
                <input
                    type="text"
                    v-model="search"
                    class="form-control"
                    placeholder="Potrebné v prípade vytvorenia akcie"
                    required
                />
            </div>

            <div
                v-for="village in villages"
                :key="village.id"
                @click="selectedVillage(village)"
                class="cursor-pointer mx-2"
            >
                <div class="hover:bg-gray-50 px-1">
                    {{ village.fullname }} {{ village.zip }}
                </div>
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

            <span class="font-semibold">Zaradená do zoznamu</span><br />

            <div v-for="updater in listDenominations" :key="updater.id">
                <input
                    type="radio"
                    required
                    v-model="form.updaters"
                    :id="updater.id"
                    :value="updater.id"
                />
                <label :for="updater.id">{{ updater.title }}</label>
            </div>

            <div class="form-group text-right ">
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
            showForm: false,
            user: "",
            search: "",
            villages: [],
            updaters: [],
            form: {
                title: "",
                street: "",
                phone: "",
                village_id: "",
                updaters: []
            }
        };
    },
    watch: {
        search: function() {
            this.fetchVillage();
        }
    },

    methods: {
        selectedVillage(village) {
            this.search = village.fullname + ", " + village.zip;
            this.form.village_id = village.id;
        },
        toggle: function() {
            this.showForm = !this.showForm;
        },

        fetchVillage: function() {
            axios
                .post("/api/villages", { name: this.search })
                .then(response => {
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
                    location.reload();
                });

            this.clearForm();
        },

        clearForm: function() {
            this.form = {};
        }
    },
    computed: {
        listDenominations: function() {
            return this.updaters.filter(
                updater => updater.type == "denomination"
            );
        }
    },
    created() {
        // this.fetchVillage();
        this.fetchUser();
        this.fetchUpdaters();
    }
};
</script>
