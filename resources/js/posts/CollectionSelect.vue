<template>
    <fieldset ref="root">
        <legend class="ar-label">Kolekcie a semináre</legend>
        <input type="hidden" name="collections_present" value="1">
        <p class="ar-hint mb-3">Nepovinné. Video môže zostať bez zaradenia alebo patriť do viacerých kolekcií.</p>

        <!-- Kanál bez kolekcií: jedna veta s odkazom namiesto prázdneho zoznamu a počtu „0". -->
        <p v-if="!available.length" class="text-sm text-[color:var(--ar-ink-soft)]">
            Tento kanál zatiaľ nemá kolekcie.
            <a v-if="canal" :href="manageBase + '/' + canal + '/seminars/create'" target="_blank" rel="noopener" class="ar-link font-medium">Vytvoriť kolekciu</a>
        </p>

        <template v-else>
            <label for="collection-search" class="sr-only">Hľadať kolekciu</label>
            <input v-if="available.length > 6" id="collection-search" v-model="search" type="search" class="ar-field mb-3" placeholder="Hľadať kolekciu">
            <div class="ar-checks max-h-64 overflow-y-auto">
                <label v-for="item in visible" :key="item.id" class="ar-check">
                    <input v-model="chosen" type="checkbox" name="collections[]" :value="String(item.id)">
                    <span>{{ item.title }} <span class="text-[color:var(--ar-muted)]">· {{ item.kind === 'collection' ? 'kolekcia' : 'seminár' }}</span></span>
                </label>
            </div>
            <p v-if="!visible.length" role="status" class="ar-hint">Žiadna kolekcia nezodpovedá vyhľadávaniu.</p>
            <p class="ar-hint mt-2" role="status">
                <template v-if="chosen.length">Vybrané: {{ chosen.length }} · </template>
                <a v-if="canal" :href="manageBase + '/' + canal + '/seminars'" target="_blank" rel="noopener" class="ar-link">Spravovať kolekcie <span class="sr-only">(otvorí sa v novom okne)</span><i class="ph ph-arrow-square-out ml-0.5" aria-hidden="true"></i></a>
            </p>
        </template>

        <!-- Vybrané položky zostávajú súčasťou formulára aj pri vyhľadávaní. -->
        <input v-for="id in hiddenSelected" :key="id" type="hidden" name="collections[]" :value="id">
    </fieldset>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
const props = defineProps({ items: { type: Array, default: () => [] }, selected: { type: Array, default: () => [] }, canalId: { type: [String, Number], default: '' }, manageBase: { type: String, required: true } });
const root = ref(null);
const canal = ref(String(props.canalId));
const search = ref('');
const available = computed(() => props.items.filter(item => String(item.canal_id) === canal.value));
const chosen = ref(props.selected.map(String).filter(id => available.value.some(item => String(item.id) === id)));
const normalize = text => String(text).normalize('NFD').replace(/[̀-ͯ]/g, '').toLocaleLowerCase('sk').trim();
const visible = computed(() => available.value.filter(item => normalize(item.title).includes(normalize(search.value))));
const hiddenSelected = computed(() => chosen.value.filter(id => !visible.value.some(item => String(item.id) === id)));
let form;
const changeCanal = event => {
    if (event.target.name !== 'canal_id') return;
    canal.value = String(event.target.value);
    chosen.value = [];
    search.value = '';
};
onMounted(() => { form = root.value.closest('form'); form?.addEventListener('change', changeCanal); });
onBeforeUnmount(() => form?.removeEventListener('change', changeCanal));
</script>
