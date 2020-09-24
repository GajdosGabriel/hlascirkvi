<template>
    <section v-if="showModul" class="module">
        <div class="top-bar">

            <div class="level">
                <h1>Konverzácia s {{user.title }}</h1>
                <span style="cursor: pointer" @click="toggle">x</span>
            </div>

        </div>

        <ol class="discussion">

            <li class="other" v-for="message in messagers">
               <mlist :data="message"></mlist>
            </li>

            <!--<li class="self">-->
                <!--<div class="avatar">-->
                    <!--<img src="" />-->
                <!--</div>-->
                <!--<div class="messages">-->
                    <!--<p>body</p>-->
                    <!--<time datetime=""></time>-->
                <!--</div>-->
            <!--</li>-->

        </ol>

        <mform :user="user" @created="add"></mform>

    </section>

</template>

<script>
    import {bus} from '../app';

    import Mlist from './List.vue';
    import Mform from './form.vue';

    export default {
        props: ['user', 'messages'],
        components: { Mlist, Mform },
        data: function() {
            return {
                showModul: false,
                messagers: this.messages
            }
        },

        created: function() {

            bus.$on('openMessenger', this.toggle);
        },

        methods: {
            toggle: function() {
                this.showModul = ! this.showModul;
            },

            add: function(newMessage){
                this.messagers.push(newMessage);
                console.log(newMessage);
            }

        }
    }
</script>