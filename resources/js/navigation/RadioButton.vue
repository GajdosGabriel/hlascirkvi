<template>
    <!-- Rozbaľovacia sekcia v mobilnom menu -->
    <div v-if="variant === 'mobile'">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-md px-3 py-2.5 text-base font-medium text-blue-100 transition-colors hover:bg-blue-800 hover:text-white"
            :aria-expanded="open ? 'true' : 'false'"
            @click="toggle"
        >
            <span class="flex items-center gap-3">
                <i class="fas fa-volume-up w-5 text-center" aria-hidden="true"></i>
                Rádiá
            </span>
            <svg
                class="h-4 w-4 transition-transform"
                :class="{ 'rotate-180': open }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div v-show="open" class="mt-1 space-y-1 pl-6">
            <a
                v-for="radio in radios"
                :key="radio.name"
                href="#"
                :title="radio.title || radio.name"
                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-blue-100 transition-colors hover:bg-blue-800 hover:text-white"
                @click.prevent="play(radio)"
            >
                {{ radio.name }}
                <img v-if="radio.flag" :src="radio.flag" class="h-4 rounded-sm" alt="" />
            </a>
        </div>
    </div>

    <!-- Rozbaľovacie menu v hlavnej lište -->
    <div v-else class="relative">
        <button
            type="button"
            class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-blue-100 transition-colors hover:bg-blue-800 hover:text-white"
            :aria-expanded="open ? 'true' : 'false'"
            @click="toggle"
        >
            <i class="fas fa-volume-up" aria-hidden="true"></i>
            Rádiá
            <svg
                class="h-4 w-4 transition-transform"
                :class="{ 'rotate-180': open }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            v-show="open"
            class="absolute left-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-md border border-gray-200 bg-white py-1 text-gray-700 shadow-xl"
        >
            <a
                v-for="radio in radios"
                :key="radio.name"
                href="#"
                :title="radio.title || radio.name"
                class="flex items-center justify-between gap-2 px-4 py-2 text-sm transition-colors hover:bg-gray-100 hover:text-gray-900"
                @click.prevent="play(radio)"
            >
                {{ radio.name }}
                <img v-if="radio.flag" :src="radio.flag" class="h-4 rounded-sm" alt="" />
            </a>
        </div>
    </div>
</template>

<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    mixins: [createdMixin],

    props: {
        variant: {
            type: String,
            default: "desktop"
        }
    },

    data() {
        return {
            open: false,
            radios: [
                {
                    name: "Rádio 7 sk",
                    title: "Rádio 7 Slovenská redakcia",
                    url: "http://radio7.sk/live-vysielanie",
                    flag: "/images/flag-sk.jpg"
                },
                {
                    name: "Rádio 7 cz",
                    title: "Rádio 7 Česká redakcia",
                    url:
                        "http://listen.play.cz/player.html?shortcut=radio7&format=&v=20200120",
                    flag: "/images/flag-cz.jpg"
                },
                {
                    name: "Rádio Lumen",
                    url: "https://www.lumen.sk/radio-streaming.html?liveplayer=1"
                },
                {
                    name: "Lumen Gospel",
                    url: "https://www.lumen.sk/radio-streaming.html?liveplayer=2"
                },
                {
                    name: "Rádio Mária",
                    url: "https://dreamsiteradioplayer.it/plrm/rmslovakia/"
                }
            ]
        };
    },

    methods: {
        toggle() {
            this.open = !this.open;
        },

        play(radio) {
            window.open(
                radio.url,
                "pagename",
                "resizable,height=500,width=470"
            );
            this.open = false;
        }
    }
};
</script>

<style>
/* Pozor: tieto pravidlá sú globálne a spoliehajú sa na ne aj iné časti webu. */
[v-cloak] > * {
    display: none;
}

li {
    display: block;
    transition-duration: 0.2s;
}

li:hover {
    cursor: pointer;
}

ul li ul {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    transition: all 0.3s ease;
    margin-top: 1rem;
    left: 0;
    display: none;
}

ul li:hover > ul,
ul li ul:hover {
    visibility: visible;
    opacity: 1;
    display: block;
}

ul li ul li {
    clear: both;
    width: 100%;
}
</style>
