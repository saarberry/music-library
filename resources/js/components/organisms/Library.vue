<template>
    <section class="Library">
        <Album
            v-for="album in albums"
            :key="album.id"
            :artist="album.artist"
            :title="album.title"
            :cover="`storage/${album.image}`"
        />
    </section>
</template>

<script>
import { ref } from "vue";
import axios from "axios";
import Album from "@/components/molecules/Album.vue";

export default {
    components: { Album },
    setup() {
        let albums = ref([]);
        async function loadAlbums() {
            let response = await axios.get("/api/albums");
            let result = response.data;
            albums.value = result.data;
        }

        loadAlbums();

        return { albums };
    },
};
</script>