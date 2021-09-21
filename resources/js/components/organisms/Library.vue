<template>
    <section class="Library">
        <Album
            v-for="album in filteredAlbums"
            :key="album.id"
            :artist="album.artist"
            :title="album.title"
            :cover="`storage/${album.image}`"
        />
    </section>
</template>

<script>
import { computed, ref } from "vue";
import axios from "axios";
import Fuse from "fuse.js";
import Album from "@/components/molecules/Album.vue";
import { EVENTS, EventBus } from "@/events.js";

export default {
    components: { Album },
    setup() {
        let albums = ref([]);
        let fuse = new Fuse([], {
            threshold: 0.3,
            keys: ["artist", "title"],
        });
        async function loadAlbums() {
            let response = await axios.get("/api/albums");
            let result = response.data;
            albums.value = result.data;
            fuse.setCollection(result.data);
        }

        let query = ref("");
        EventBus.on(EVENTS.SEARCH_INPUT, (input) => (query.value = input));

        let filteredAlbums = computed(() => {
            if (query.value.length == 0) return albums.value;

            return fuse.search(query.value).map((result) => result.item);
        });
        // watch(filteredAlbums, () => console.log(filteredAlbums.value));

        loadAlbums();

        return { filteredAlbums };
    },
};
</script>