<template>
    <section class="card">
        <header class="card_header">
            <h4>Aktuálne správy</h4>
            <i class="fas fa-rss"></i>
        </header>

        <div>
            <div class="flex flex-wrap space-x-4 p-2">
                <a
                    @click="domace('domov')"
                    :class="{
                        'border-red-300 bg-red-600 text-gray-100': isDomace
                    }"
                    class="border-2 px-2 border-gray-300 rounded-md cursor-pointer hover:bg-red-300"
                    >domáce</a
                >
                <a
                    @click="domace('zahranicie')"
                    :class="{
                        'border-red-300 bg-red-600 text-gray-100': isZahranicie
                    }"
                    class="border-2 px-2 border-gray-300 rounded-md cursor-pointer hover:bg-red-300"
                    >zahraničné</a
                >
                <a
                    @click="domace('press')"
                    :class="{
                        'border-red-300 bg-red-600 text-gray-100': isTlacove
                    }"
                    class="border-2 px-2 border-gray-300 rounded-md cursor-pointer hover:bg-red-300"
                    >tlačové</a
                >
            </div>

            <ul class="p-3 text-sm">
                <li
                    v-for="(item, index) in itemWithActive"
                    :key="index"
                    v-if="index <= 8"
                    class="mb-3"
                >
                    <div
                        @click="showItem(index)"
                        class=""
                        style="line-height: initial;font-weight: 600; cursor: pointer;color: #5a5a5a"
                    >
                        {{ item.header }}
                    </div>
                    <div @click="showItem(index)" v-if="item.active">
                        {{ item.body }}
                        <br />
                        <div
                            class=""
                            title="Tlačová kancelária konferencie biskupov Slovenska"
                        >
                            Zdroj: TKKBS
                        </div>
                    </div>
                    <div class="text-xs itealic">
                        {{ item.pubdate | dateTime }}
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<script>
import moment from "moment";

export default {
    data: function() {
        return {
            items: [],
            isDomace: true,
            isZahranicie: false,
            isTlacove: false,
            computedItems: []
        };
    },

    created: function() {
        // this.domace("press");
        this.domace("domov");
    },

    filters: {
        dateTime: function(value) {
            return moment(value)
                .subtract(1, "hours")
                .format("D.M.Y, H:mm");
            //                return moment(value).format('lll');
            //                return moment(value).format('LT D.M.Y');
        }
    },

    computed: {
        itemWithActive: function() {
            this.computedItems = this.items.map(item => {
                return {
                    header: item.title,
                    body: item.description,
                    pubdate: item.pubDate,
                    active: false
                };
            });
            return this.computedItems;
        }
    },

    methods: {
        //domáce správy //domov
        domace: function(canal) {
            axios
                .get("/api/rss-reader-canal/" + canal)
                .then(response => (this.items = response.data.channel.item));

            this.activeNav(canal);
        },
        activeNav: function(canal) {
            if (canal == "domov") {
                this.isTlacove = false;
                this.isDomace = true;
                this.isZahranicie = false;
            }

            if (canal == "zahranicie") {
                this.isTlacove = false;
                this.isDomace = false;
                this.isZahranicie = true;
            }

            if (canal == "press") {
                this.isTlacove = true;
                this.isDomace = false;
                this.isZahranicie = false;
            }
        },

        showItem: function(index) {
            this.computedItems[index].active = !this.computedItems[index]
                .active;
            this.computedItems.forEach((item, ind) => {
                if (ind !== index) {
                    item.active = false;
                }
            });
        }
    }
};
</script>
