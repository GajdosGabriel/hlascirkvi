<template>
    <div class="w-16 ml-2 md:ml-6 flex md:flex-col text-2xl items-center">
        <i class="fas fa-praying-hands text-gray-400" title="modlitbu"></i>
        <span :class="visibleFavoritesCounter" class="text-gray-900">{{ prayer.favoritesCount }}</span>
        <button class="btn-small text-xsp">Modlitba</button>
    </div>
</template>

<script>
    export default {
        props:['prayer'],

        computed:{
            visibleFavoritesCounter(){
                return this.prayer.favoritesCount ? '' : 'invisible'
            }
        },
    }
</script>
