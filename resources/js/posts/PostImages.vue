<template>
    <div class="post-images">
        <!-- Uložené obrázky článku -->
        <div v-if="saved.length || items.length" class="post-images__grid">
            <div v-for="image in saved" :key="'s-' + image.id" class="post-images__item">
                <img :src="image.thumb || image.url" alt="" @click="openOriginal(image)" />
                <button type="button" class="post-images__remove" :disabled="deleting === image.id"
                    title="Zmazať obrázok" @click="removeSaved(image)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Vybrané, zatiaľ neodoslané súbory -->
            <div v-for="(item, i) in items" :key="item.preview" class="post-images__item is-pending">
                <img :src="item.preview" :alt="item.file.name" />
                <button type="button" class="post-images__remove" title="Odobrať" @click="removePending(i)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <span class="post-images__name">{{ item.file.name }}</span>
            </div>
        </div>

        <div class="post-images__drop" :class="{ 'is-over': dragging }" role="button" tabindex="0"
            @click="picker?.click()" @keydown.enter.prevent="picker?.click()" @keydown.space.prevent="picker?.click()"
            @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="onDrop">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            <span class="post-images__drop-title">Presuňte obrázky sem alebo kliknite a vyberte</span>
            <span class="post-images__drop-hint">JPG, PNG, WEBP, GIF · najviac {{ maxFiles }} naraz · do 15 MB</span>
        </div>

        <p v-if="error" class="post-images__error">{{ error }}</p>

        <!-- Výber súborov bez odoslania -->
        <input ref="picker" type="file" :accept="ACCEPT" multiple hidden @change="onPick" />
        <!-- Súbory, ktoré odchádzajú s formulárom (PostSaveRequest: pictures[]) -->
        <input ref="field" type="file" :name="name" multiple hidden />
    </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue';

const props = defineProps({
    name: { type: String, default: 'pictures[]' },
    // [{ id, thumb, url }]
    images: { type: Array, default: () => [] },
    maxFiles: { type: Number, default: 20 },
});

const ACCEPT = 'image/jpeg,image/png,image/webp,image/gif';
const MAX_BYTES = 15 * 1024 * 1024;

const picker = ref(null);
const field = ref(null);
const dragging = ref(false);
const error = ref(null);
const deleting = ref(null);
const saved = ref([...props.images]);
const items = ref([]);

/*
 * Súbory idú na server spolu s formulárom, nie hneď — článok pri vytváraní
 * ešte nemá id. Vybraný zoznam sa preto zapisuje do skutočného file inputu.
 */
function syncField() {
    if (!field.value) return;
    const dt = new DataTransfer();
    items.value.forEach((it) => dt.items.add(it.file));
    field.value.files = dt.files;
}

function addFiles(files) {
    error.value = null;
    const rejected = [];

    for (const file of Array.from(files)) {
        if (!ACCEPT.split(',').includes(file.type)) {
            rejected.push(`${file.name}: nepodporovaný formát`);
            continue;
        }
        if (file.size > MAX_BYTES) {
            rejected.push(`${file.name}: viac ako 15 MB`);
            continue;
        }
        if (items.value.length >= props.maxFiles) {
            rejected.push(`Naraz je možné pridať najviac ${props.maxFiles} obrázkov.`);
            break;
        }
        items.value.push({ file, preview: URL.createObjectURL(file) });
    }

    if (rejected.length) error.value = rejected.join(' · ');
    syncField();
}

function onPick(e) {
    addFiles(e.target.files ?? []);
    e.target.value = '';
}

function onDrop(e) {
    dragging.value = false;
    addFiles(e.dataTransfer?.files ?? []);
}

function removePending(i) {
    URL.revokeObjectURL(items.value[i].preview);
    items.value.splice(i, 1);
    syncField();
}

async function removeSaved(image) {
    if (!confirm('Naozaj zmazať obrázok?')) return;
    deleting.value = image.id;
    try {
        await axios.delete('/images/' + image.id);
        saved.value = saved.value.filter((i) => i.id !== image.id);
    } catch {
        error.value = 'Obrázok sa nepodarilo zmazať.';
    } finally {
        deleting.value = null;
    }
}

function openOriginal(image) {
    window.open(image.url, '_blank', 'noopener');
}

onBeforeUnmount(() => items.value.forEach((it) => URL.revokeObjectURL(it.preview)));
</script>

<style scoped>
.post-images { display: flex; flex-direction: column; gap: .75rem; }

.post-images__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(8.5rem, 1fr));
    gap: .625rem;
}

.post-images__item {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    border: 1px solid var(--ar-line, #e2e8f0);
    border-radius: .625rem;
    background: #f1f5f9;
}
.post-images__item.is-pending { border-style: dashed; border-color: var(--ar-accent, #3b82f6); }
.post-images__item img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
.post-images__item.is-pending img { cursor: default; }

.post-images__remove {
    position: absolute;
    top: .35rem;
    right: .35rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 9999px;
    background: rgba(0, 0, 0, .6);
    color: #fff;
}
.post-images__remove:hover { background: rgba(0, 0, 0, .85); }
.post-images__remove:disabled { opacity: .5; }
.post-images__remove svg { width: .75rem; height: .75rem; }

.post-images__name {
    position: absolute;
    inset-inline: 0;
    bottom: 0;
    overflow: hidden;
    padding: .15rem .5rem;
    background: rgba(0, 0, 0, .45);
    color: #fff;
    font-size: .6875rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.post-images__drop {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .25rem;
    padding: 1.25rem 1rem;
    border: 2px dashed var(--ar-line, #cbd5e1);
    border-radius: .75rem;
    background: var(--ar-paper, #f8fafc);
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, background-color .15s;
}
.post-images__drop:hover,
.post-images__drop.is-over { border-color: var(--ar-accent, #3b82f6); }
.post-images__drop.is-over { background: rgba(var(--ar-accent-rgb, 59, 130, 246), .06); }
.post-images__drop svg { width: 1.5rem; height: 1.5rem; color: var(--ar-ink-soft, #94a3b8); }
.post-images__drop-title { color: var(--ar-ink, #334155); font-size: .875rem; }
.post-images__drop-hint { color: var(--ar-ink-soft, #94a3b8); font-size: .75rem; }

.post-images__error { color: #b91c1c; font-size: .8125rem; }
</style>
