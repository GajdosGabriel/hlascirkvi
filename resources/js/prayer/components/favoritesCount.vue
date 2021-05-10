<template>
    <div>
        <!-- <i class="fas fa-praying-hands text-gray-400" title="modlitbu"></i> -->

        <button class="btn-small text-xs whitespace-nowrap">Modlím sa
            <span v-if="prayer.favoritesCount > 0" :class="visibleFavoritesCounter" class="text-gray-900">{{ prayer.favoritesCount }}</span>
            </button>
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
