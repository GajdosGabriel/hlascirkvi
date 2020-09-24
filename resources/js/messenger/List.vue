<template>
    <div class="other">
        <div class="avatar">
            <img src="" />
        </div>
        <div class="messages">
            <p v-text="data.body"> </p>
            <time datetime="data.created_at">{{ data.created_at }}</time>
        </div>
    </div>

</template>

<script>
    export default {
        props:['data'],
        data: function() {
            return {

            }
        }
    }
</script>