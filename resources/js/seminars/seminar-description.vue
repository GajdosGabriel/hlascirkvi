<template>
    <div v-if="seminar.description">
        {{ seminar.description }}
    </div>
</template>

<script>
export default {
    props: ["seminar"]
};
</script>
