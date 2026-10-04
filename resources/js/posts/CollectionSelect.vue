<template>
    <fieldset ref="root" class="form-group">
        <legend class="post-form__section-label">Kolekcie a semináre · nepovinné</legend>
        <input type="hidden" name="collections_present" value="1">
        <p class="text-sm text-gray-600 mb-3">Video môže zostať bez zaradenia alebo patriť do viacerých kolekcií.</p>
        <label for="collection-search" class="sr-only">Hľadať kolekciu</label>
        <input v-if="available.length" id="collection-search" v-model="search" type="search" class="form-control mb-2" placeholder="Hľadať kolekciu">
        <div class="max-h-64 overflow-y-auto">
            <label v-for="item in visible" :key="item.id" class="flex items-center gap-2 py-2">
                <input v-model="chosen" type="checkbox" name="collections[]" :value="String(item.id)">
                <span>{{ item.title }} <small class="text-gray-500">{{ item.kind === 'collection' ? 'Kolekcia' : 'Seminár' }}</small></span>
            </label>
        </div>
        <!-- Vybrané položky zostávajú súčasťou formulára aj pri vyhľadávaní. -->
        <input v-for="id in hiddenSelected" :key="id" type="hidden" name="collections[]" :value="id">
        <p v-if="!available.length" class="text-sm text-gray-500">Tento kanál zatiaľ nemá kolekcie.</p>
        <p v-else-if="!visible.length" role="status" class="text-sm text-gray-500">Žiadna kolekcia nezodpovedá vyhľadávaniu.</p>
        <p class="text-sm mt-2" role="status">Vybrané kolekcie: {{ chosen.length }}</p>
        <a v-if="canal" :href="manageBase + '/' + canal + '/seminars'" target="_blank" rel="noopener" class="ar-link">Spravovať kolekcie (nové okno)</a>
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
const normalize = text => String(text).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('sk').trim();
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
