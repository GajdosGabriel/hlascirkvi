<template>
    <div class="flex flex-wrap items-center gap-1.5">
        <!-- Zverejnenie je hlavná akcia; blokovanie video natrvalo skryje, preto
             je tiché a pýta si potvrdenie. -->
        <button type="button" class="ar-btn ar-btn--accent" :disabled="busy" @click="publishVideo">
            <i class="ph ph-paper-plane-tilt" aria-hidden="true"></i> Zverejniť
        </button>
        <button type="button" class="ar-act ar-act--danger" :disabled="busy" @click="youtubeBlocked">
            <i class="ph ph-prohibit" aria-hidden="true"></i> Blokovať
        </button>
        <span v-if="error" role="alert" class="ar-error">{{ error }}</span>
    </div>
</template>

<script>
    export default {
        props: ['post'],
        data: () => ({ busy: false, error: '' }),
        methods: {
            // Do 10/2026 `.then(location.reload())` — reload sa spustil hneď,
            // ešte pred dokončením požiadavky, a chyba sa nikde neukázala.
            send(data) {
                this.busy = true;
                this.error = '';
                return axios.put('/api/posts/' + this.post.id, data)
                    .then(() => {
                        const row = this.$el.closest('[data-buffer-row]');
                        if (!row) return location.reload();
                        row.style.transition = 'opacity 250ms';
                        row.style.opacity = 0;
                        setTimeout(() => row.remove(), 250);
                    })
                    .catch(() => {
                        this.busy = false;
                        this.error = 'Nepodarilo sa uložiť.';
                    });
            },

            publishVideo() {
                this.send({ publish: 1 });
            },

            youtubeBlocked() {
                if (!window.confirm('Zablokovať video? Na webe sa už nezobrazí.')) return;
                this.send({ youtube_blocked: 1 });
            }
        }
    }
</script>
