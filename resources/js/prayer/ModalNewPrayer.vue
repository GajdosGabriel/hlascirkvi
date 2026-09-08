<template>
    <div
        v-if="show"
        class="fixed inset-0 z-40 overflow-y-auto bg-[rgba(16,24,40,.55)] p-4 sm:p-6"
        @click.self="close"
    >
        <div class="flex min-h-full items-center justify-center" @click.self="close">
            <div class="ar-modal" role="dialog" aria-modal="true" aria-labelledby="prayer-new-title">
                <header class="ar-modal__head">
                    <div class="min-w-0">
                        <p class="ar-kicker">Modlitebný múr</p>
                        <h3 id="prayer-new-title" class="ar-display mt-1 text-lg font-extrabold leading-snug">
                            {{ isEdit ? "Upraviť prosbu" : "Poprosiť o modlitbu" }}
                        </h3>
                    </div>

                    <button type="button" class="ar-modal__close" title="Zavrieť" @click="close">
                        <i class="fas fa-times"></i>
                    </button>
                </header>

                <form class="px-5 py-5" @submit.prevent="savePrayer">
                    <div>
                        <label class="ar-label" for="prayer-title">Modlitba za</label>
                        <input
                            id="prayer-title"
                            v-model="form.title"
                            type="text"
                            class="ar-field"
                            placeholder="napr. za uzdravenie manžela"
                            required
                            autofocus
                        />
                        <p class="ar-hint">
                            Krátko o vašom modlitbovom úmysle, napr. za uzdravenie manžela,
                            za prácu a podobne.
                        </p>
                    </div>

                    <div class="mt-4">
                        <label class="ar-label" for="prayer-body">Viac o úmysle</label>
                        <textarea
                            id="prayer-body"
                            v-model="form.body"
                            class="ar-field"
                            rows="5"
                            required
                        ></textarea>
                        <p class="ar-hint">
                            Napíšte viac o dôvode modlitby, aby vám ostatní mohli lepšie
                            porozumieť.
                        </p>
                    </div>

                    <div class="mt-4">
                        <label class="ar-label" for="prayer-user-name">Prezývka</label>
                        <input
                            id="prayer-user-name"
                            v-model="form.user_name"
                            type="text"
                            class="ar-field"
                            placeholder="Prezývka"
                            required
                        />
                        <p class="ar-hint">
                            Pre ostatných, aby vedeli, ako vás osloviť v modlitbe.
                        </p>
                    </div>

                    <div class="mt-4" v-if="!authUser">
                        <label class="ar-label" for="prayer-email">E-mailová adresa</label>
                        <input
                            id="prayer-email"
                            v-model="form.email"
                            type="email"
                            class="ar-field"
                            placeholder="meno@email.sk"
                            required
                        />
                        <p class="ar-hint">
                            E-mail sa nikde nezverejňuje a je potrebný na overenie. Zároveň
                            vám prídu oznámenia, keď sa za vás niekto pomodlí alebo napíše.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-2 border-t border-[color:var(--ar-line)] pt-4 sm:flex-row sm:justify-end"
                    >
                        <button type="button" class="ar-btn ar-btn--quiet" @click="close">
                            Zrušiť
                        </button>
                        <button type="submit" class="ar-btn ar-btn--accent" :disabled="saving">
                            <i class="fas fa-praying-hands"></i>
                            {{ isEdit ? "Uložiť zmeny" : "Odoslať prosbu" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { bus } from "../app";
import Axios from "axios";

export default {
    data: function () {
        return {
            show: false,
            saving: false,
            authUser: window.App.user,
            form: {
                user_name: "",
            },
        };
    },

    computed: {
        isEdit() {
            return !!this.form.id;
        },
    },

    created: function () {
        bus.$on("openModalPrayer", () => {
            this.form = { user_name: "" };
            this.show = true;
        });

        bus.$on("passToModalEdit", (prayer) => {
            this.form = Object.assign({}, prayer);
            this.show = true;
        });

        // Okno visí v komponente na celú životnosť stránky, poslucháč sa
        // preto neodpája — zaniká až s ňou.
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") this.close();
        });
    },

    methods: {
        close: function () {
            this.show = false;
        },

        savePrayer: function () {
            this.saving = true;

            // Úprava existujúcej prosby posiela len polia z formulára; zvyšok
            // (počty, dátum vypočutia) patrí modelu, nie tomuto oknu.
            const request = this.isEdit
                ? Axios.put("/api/prayers/" + this.form.id, {
                      title: this.form.title,
                      body: this.form.body,
                      user_name: this.form.user_name,
                  })
                : Axios.post("/api/prayers", this.form);

            request
                .then(() => {
                    this.form = { user_name: "" };
                    this.show = false;
                    window.location.reload();
                })
                .catch(() => {
                    this.saving = false;
                });
        },
    },
};
</script>
