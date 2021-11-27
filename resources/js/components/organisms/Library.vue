<template>
    <section class="Library">
        <transition-group
            name="album-list"
            @before-leave="forceSize"
            @after-leave="revertSize"
        >
            <Album
                v-for="album in filteredAlbums"
                :key="album.id"
                :artist="album.artist"
                :title="album.title"
                :cover="`storage/${album.image}`"
            />
        </transition-group>
    </section>
</template>

<script>
import { computed, ref } from "vue";
import axios from "axios";
import Fuse from "fuse.js";
import debounce from "debounce";
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

        let query = ref("");
        EventBus.on(
            EVENTS.SEARCH_INPUT,
            debounce((input) => (query.value = input), 150)
        );

        let filteredAlbums = computed(() => {
            if (query.value.length == 0) return albums.value;

            return fuse.search(query.value).map((result) => result.item);
        });

        async function loadAlbums() {
            let response = await axios.get("/api/albums");
            let result = response.data;
            albums.value = result.data;
            fuse.setCollection(result.data);
        }
        loadAlbums();

        function forceSize(el) {
            el.style.width = `${el.offsetWidth}px`;
            el.style.height = `${el.offsetHeight}px`;
        }

        function revertSize(el) {
            el.style.width = null;
            el.style.height = null;
        }

        return { filteredAlbums, forceSize, revertSize };
    },
};
</script>