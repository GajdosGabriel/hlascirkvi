<template>
    <div>
        <transition name="fade">
            <div v-if="show">
                <div  class="form-group" style="padding: 0rem">
                    <textarea v-model="body" class="form" rows="3" placeholder="Poslať právu"></textarea>
                </div>
                <div v-for="error in errors" :key="error" style="color: red">{{ error }}</div>

                <button class="btn btn-small"  @click="sendmessage">Odoslať</button>
            </div>
        </transition>

        <transition name="fade">
            <div v-if="annotation" style="background: brown; color: whitesmoke; padding: 1.7rem">
                <h5>Správa bola odoslaná!</h5>
            </div>
        </transition>

        <div v-show="!show" class="left" @click="toggle">Máte typ na vysielanie?</div>
        <div v-show="show" class="left" @click="toggle">Zavrieť</div>

    </div>
</template>

<script>
    import {bus} from '../eventBus';
    export default {
        // Pečiatka z App\Support\HumanCheck. Vykresliť ju vie len server,
        // takže sem príde z blade šablóny (<messenger :stamp="..."/>) — bez
        // nej neprejde odoslanie od neprihláseného návštevníka.
        props: {
            stamp: { type: String, default: '' }
        },

        data: function() {
            return {
                errors: [],
                show: false,
                annotation: false,
                body: ''
            }
        },

        methods: {
            // Správa sa hlásila ako odoslaná hneď, bez ohľadu na odpoveď servera —
            // aj keď ju odmietol (kratšia ako 3 znaky, chýbajúca pečiatka).
            sendmessage: function() {
                this.errors = [];

                if (this.body.trim().length < 3) {
                    this.errors.push('Správa musí mať aspoň 3 znaky.');
                    return;
                }

                axios.post('/store/message', { body: this.body, form_ts: this.stamp })
                    .then(() => {
                        this.body = '';
                        this.show = false;
                        this.annotation = true;
                        this.hide();
                    })
                    .catch((error) => {
                        const errors = error.response?.data?.errors;
                        this.errors = errors
                            ? Object.values(errors).flat()
                            : ['Správu sa nepodarilo odoslať. Skúste to znova.'];
                    });
            },

            hide: function() {
                setTimeout(() => {
                    this.annotation = false
                }, 3000);
            },

            toggle: function() {
                this.show = ! this.show;
            }
        }

    }
</script>

<style>
    .left{ float: right}
</style>
