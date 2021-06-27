<template>
    <div>
        <div class="flex text-sm space-x-3 text-gray-400 mt-1">
            <div class="cursor-pointer hover:text-gray-600 text-gray-500">
                Pridal: {{ seminar.organization.title }}
            </div>
            <div class="flex  space-x-3" v-if="can">
                <div
                    @click="publishedfunction"
                    class="cursor-pointer hover:underline hover:text-gray-600 px-2"
                    v-text="publishedButton"
                ></div>

                <a
                    v-if="seminar.youtube_playlist"
                    :href="'/seminars/' + seminar.id + '/upload'"
                    class="cursor-pointer hover:bg-gray-300 hover:text-gray-600 border-gray-500 rounded-md px-2"
                    >Načítať z youTube zoznamu</a
                >

                <a
                    v-else
                    :href="'/seminars/' + seminar.id + '/edit'"
                    class="cursor-pointer hover:bg-gray-300 hover:text-gray-600 border-gray-500 rounded-md px-2"
                >
                    Nevyplnený playlist
                </a>

                <a
                    v-if="seminar.youtube_playlist"
                    :href="'/posts/create'"
                    class="cursor-pointer hover:bg-gray-300 hover:text-gray-600 border-gray-500 rounded-md px-2"
                    >Pridať video
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ["seminar"],

    // data: function() {
    //     return {
    //         arrs: [
    //             {
    //                 class: "text-red-600",
    //                 text: "Publikovať"
    //             },
    //             {
    //                 class: "",
    //                 text: "Nepublikovať"
    //             }
    //         ]
    //     }
    // },
    computed: {
        publishedButton: function() {
            if (this.seminar.published == null) {
                return "Publikovať";
            }
            return "Nepublikovať";
        },
        signedIn: function() {
            return window.App.signedIn;
        },
        user: function() {
            return window.App.user;
        },

        can: function() {
            if(this.signedIn) {
            return [this.user.org_id == this.seminar.organization_id ? false : true];
            }
            return false;
        }
    },

    methods: {
        publishedfunction: function() {
            axios.put("/seminars/" + this.seminar.id, {
                published: this.seminar.published ? "" : Date.now()
            });
        }
    }
};
</script>
