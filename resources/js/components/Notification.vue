<template>
    <div>
        <transition name="fade">
            <div :class="addClass" v-if="banner">
                <i class="fas fa-info-circle fa-lg"></i>
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
            bus.$on('flash', (data) => {
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
              return ['level', this.type ? 'danger' : 'success'];
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
    .success {
        z-index: 999;
        position: fixed;
        font-size: 111%;
        color: whitesmoke;
        right: 25px;
        bottom: 4rem;
        background: #53994f;
        padding: 1.3rem;
        border: .1rem solid #335530;
        border-radius: .7rem;
    }
    .danger {
        z-index: 999;
        position: fixed;
        font-size: 111%;
        color: whitesmoke;
        right: 25px;
        bottom: 4rem;
        background: #ff9393;
        padding: 1.3rem;
        border: .1rem solid #4d4d4d;
        border-radius: .7rem;
    }

    i {
        margin-right: 1.5rem;
    }


    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }
</style>