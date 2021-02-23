<template>
    <div>
        <li @click="toggle" class="border-2 rounded-md px-2">
            <a id="navbarDropdown" class="" href="#">
                <i class="fa fa-volume-up"></i> Rádia
                <i class="fas fa-caret-down"></i>
            </a>
        </li>

        <ul v-cloak v-if="open" @click="toggle"
            class="absolute z-10 flex flex-col bg-white rounded-md border-2 border-gray-500 text-gray-700">
            <li class="p-2 px-6 hover:bg-gray-300">
                <a title="Rádio 7 Slovenská redakcia" class="hover:text-gray-700 no-underline" href="#"
                   onClick="window.open('http://radio7.sk/live-vysielanie','pagename','resizable,height=500,width=470')">
                    <div class="flex">
                        Rádio 7 sk
                        <img class="h-5 ml-2" :src="'/images/flag-sk.jpg'">
                    </div>
                </a>
            </li>
            <li class="p-2 px-6 hover:bg-gray-300">
                <a title="Rádio 7 Česká redakcia" class="hover:text-gray-700 no-underline" href="#"
                   onClick="window.open('http://listen.play.cz/player.html?shortcut=radio7&format=&v=20200120','pagename','resizable,height=500,width=470')">
                    <div class="flex">
                        Rádio 7 cz
                        <img class="h-5 ml-2" :src="'/images/flag-cz.jpg'">
                    </div>
                </a></li>
            <li class="p-2 px-6 hover:bg-gray-300">
                <a class="hover:text-gray-700 no-underline" href="#"
                   onClick="window.open('https://www.lumen.sk/radio-streaming.html?liveplayer=1','pagename','resizable,height=500,width=470')">Rádio
                    Lumen</a>
            </li>
            <li class="p-2 px-6 hover:bg-gray-300">
                <a class="hover:text-gray-700 no-underline" href="#"
                   onClick="window.open('https://www.lumen.sk/radio-streaming.html?liveplayer=2','pagename','resizable,height=500,width=470')">Lumen
                    Gospel</a>
            </li>
            <li class="p-2 px-6 hover:bg-gray-300">
                <a class="hover:text-gray-700 no-underline" href="#"
                   onClick="window.open('https://dreamsiteradioplayer.it/plrm/rmslovakia/','pagename','resizable,height=500,width=470')">Rádio
                    Mária</a>
            </li>
        </ul>
    </div>
</template>
<script>
    import {createdMixin} from "../mixins/createdMixin";

    export default {
        mixins:[createdMixin],
        data: function() {
            return {
                open:false
            }
        },

        methods: {
            toggle: function() {
                this.open = ! this.open;
            }

        }
    }
</script>

<style>

    [v-cloak] > * { display:none }

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
