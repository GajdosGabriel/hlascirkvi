<template>
    <div>
        <img
            v-if="organization.avatar"
            class="rounded-full w-16 h-16"
            :src="
                domain +
                    'storage/organizations/' +
                    organization.id +
                    '/' +
                    organization.avatar
            "
        />

        <div
            v-else
            class="h-12 w-12 bg-gray-300 rounded-full flex items-center justify-center font-semibold text-2xl"
        >
            {{ organization.initialName }}
        </div>
    </div>
</template>

<script>
export default {
    props: ["organization"],
    data: function() {
        return {
            domain: window.App.baseUrl
        };
    }
};
</script>

<style>
.slide-fade-enter-active,
.slide-fade-leave-to {
    transition: opacity 0.5s;
}

.slide-fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
 {
    opacity: 0;
}
</style>
