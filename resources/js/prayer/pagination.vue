<template>
    <div class="flex justify-center my-10 space-x-3">
        <button @click="fetchPaginate(data.prev_page_url)"
                class="flex items-center justify-center h-8 p-3 font-semibold bg-gray-400 border-1 border-gray-600 rounded-sm cursor-pointer"
                :disabled="! data.prev_page_url"> <<
        </button>
        <div
            class="flex items-center justify-center h-8 p-3 font-semibold bg-gray-400 border-1 border-gray-600 rounded-sm">
            {{ data.current_page}} / {{ data.last_page}}
        </div>
        <button @click="fetchPaginate(data.next_page_url)"
                class="flex items-center justify-center h-8 p-3 font-semibold bg-gray-400 border-1 border-gray-600 rounded-sm cursor-pointer"
                :disabled="! data.next_page_url"> >>
        </button>
    </div>

</template>
<script>

    export default {
        props: ['data'],

        methods: {
            fetchPaginate: function (url) {
                this.$emit('fetchUrl', url)
            }
        }

    }
</script>
