<template>
    <div>
        <label for="post-canal-search">Vyhľadať kanál</label>
        <input id="post-canal-search" v-model="search" type="search" class="form-control" placeholder="Začnite písať názov kanála" autocomplete="off" aria-controls="post-canal-id" />
        <label for="post-canal-id" class="sr-only">Kanál článku</label>
        <select id="post-canal-id" v-model="value" name="canal_id" class="form-control mt-2" required>
            <option value="" disabled>Vybrať kanál</option>
            <option v-for="canal in visible" :key="canal.id" :value="String(canal.id)">{{ canal.title }}</option>
        </select>
        <p v-if="search" role="status" class="text-sm mt-2">{{ matches.length ? 'Nájdené kanály: ' + matches.length : 'Žiadny kanál nezodpovedá vyhľadávaniu.' }} Aktuálny výber zostáva zachovaný.</p>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
const props = defineProps({ canals: { type: Array, required: true }, selected: { type: [String, Number], default: '' } });
const search = ref('');
const value = ref(props.canals.some(c => String(c.id) === String(props.selected)) ? String(props.selected) : '');
const normalize = text => String(text).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('sk').trim();
const matches = computed(() => props.canals.filter(c => normalize(c.title).includes(normalize(search.value))));
const visible = computed(() => props.canals.filter(c => String(c.id) === value.value || matches.value.includes(c)));
</script>
