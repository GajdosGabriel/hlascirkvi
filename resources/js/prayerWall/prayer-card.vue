<template>
    <section class="event">

        <div class="card">
            <div  style="margin: 1rem;">

                <div class="level">
                    <h4>Aktuálne správy</h4>
                    <i class="fas fa-rss"></i>
                </div>

                <div class="level">
                    <a @click="domace('domov')" :class="{'active': isDomace }" class="menu">domáce</a>
                    <a @click="domace('zahranicie')" :class="{active: isZahranicie }" class="menu">zahraničné</a>
                    <a @click="domace('press')" :class="{active: isTlacove }" class="menu">tlačové</a>
                </div>

            </div>

            <ul style="padding: 1rem">
                <li v-for="(item, index) in itemWithActive" v-if="index <= 8" >
                    <div @click="showItem(index)" class="hover" style="line-height: initial;font-weight: 600; cursor: pointer;color: #5a5a5a">{{ item.header }}</div>
                    <div @click="showItem(index)" v-if="item.active" style="margin-bottom: .4rem">{{ item.body }}
                        <br>
                        <div class="date" style="font-style: italic" title="Tlačová kancelária konferencie biskupov Slovenska">Zdroj: TKKBS</div>
                    </div>
                    <div style="margin-bottom: .5rem" class="date">{{ item.pubdate | dateTime }}</div>
                </li>
            </ul>



        </div>

    </section>
</template>

<script>
    import moment from 'moment';
    export default {
        data: function() {
            return {
                items: [],
                isDomace: true,
                isZahranicie: false,
                isTlacove: false,
                computedItems:[]
            }
        },

        created: function() {
            this.domace('domov');
        },

        filters: {
            dateTime: function(value) {
                return moment(value).subtract(1, 'hours').format('lll');
//                return moment(value).format('lll');
//                return moment(value).format('LT D.M.Y');
            }
        },

        computed: {
            itemWithActive: function() {
                this.computedItems = this.items.map((item)=> {
                            return {
                                header: item.title,
                                body: item.description,
                                pubdate: item.pubDate,
                                active:false
                            }
                        });
                return this.computedItems;
            }

        },

        methods: {
            //domáce správy //domov
            domace: function(canal) {
                axios.get('/api/rss-reader-canal/' + canal)
                        .then(response => (this.items = response.data.channel.item));

                this.activeNav(canal);
            },
            activeNav: function(canal) {
                if(canal == 'domov') {
                    this.isTlacove = false;
                    this.isDomace = true;
                    this.isZahranicie = false;
                }

                if(canal == 'zahranicie') {
                    this.isTlacove = false;
                    this.isDomace = false;
                    this.isZahranicie = true;
                }

                if(canal == 'press') {
                    this.isTlacove = true;
                    this.isDomace = false;
                    this.isZahranicie = false;
                }
            },

            showItem: function(index) {
                this.computedItems[index].active =! this.computedItems[index].active;
                this.computedItems.forEach((item,ind)=> {
                    if(ind !== index) {
                    item.active = false;
                }
            })
            }
        }

    }
</script>

<style scoped>
    .menu {
        cursor: pointer;
        padding: 0rem .4rem;
        border: 1px solid white;

    }

    a:hover {
        border: 1px solid red;
        color: red;
        border-radius: .5rem;

    }

    .active {
        background: white;
        /*color: whitesmoke;*/
        border-radius: .5rem;
        border: 1px solid red;

    }

    .date {
        color: #838383;
        text-align: right;
        font-style: italic;
        font-size: 85%;
    }
    .hover:hover {
        cursor: pointer;
        background: rgba(231, 231, 231, 0.38);
    }


</style>