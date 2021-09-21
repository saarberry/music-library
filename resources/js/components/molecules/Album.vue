<template>
    <div class="Album">
        <img :src="cover" loading="lazy" />
        <div class="Album__Details">
            <div class="Album__Title">{{ title }}</div>
            <div class="Album__Artist">{{ artist }}</div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
        artist: String,
        cover: String,
    },
};
</script>