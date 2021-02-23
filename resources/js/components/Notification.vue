<template>
    <div class="fixed bottom-10 right-3">
        <transition name="fade">
            <div :class="addClass" v-if="banner" class="flex items-center  p-5 rounded-md border-2 border-gray-500">
                <i class="fas fa-info-circle fa-lg mr-4"></i>
                {{ body }}
            </div>
        </transition>
    </div>
</template>

<script>
    import {bus} from '../app';
    export default {
        props: ['message'],
        data: function() {
            return {
                banner: false,
                body: '',
                type: false

            }
        },
        created: function() {
            bus.$on('flex justify-between', (data) => {
                this.showNotify(data);
            });

            if(this.message) {
                this.body = this.message;
                this.banner = true;
                this.hide();
            }

        },

        computed: {
          addClass: function() {
              return ['flex justify-between', this.type ? 'bg-red-300' : 'bg-green-300'];
          }
        },

        methods: {
            showNotify: function(data) {

                this.body = data.body;
                this.type = data.type;
                this.banner = true;

                this.hide();
            },

            hide: function() {
                setTimeout(() => {
                    this.banner= false;
                }, 5000);
            }
        }
    }
</script>

<style scoped>

    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }
</style>
