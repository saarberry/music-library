<template>
    <div class="Search">
        <input
            class="Search__Input"
            placeholder="Album, artist or songname..."
            v-model="query"
        />
        <ul class="Search__Results">
            <li v-for="(album, i) in albums" :key="i">
                {{ album.title }} by {{ album.artist }}
            </li>
        </ul>
    </div>
</template>

<script>
import { ref, watch } from "vue";
import { debounce } from "debounce";
import axios from "axios";

export default {
    setup() {
        let query = ref("");
        let albums = ref([]);

        async function search(query) {
            let response = await axios.get("/api/albums", {
                params: { query },
            });
            return response.data;
        }

        watch(
            () => query.value,
            debounce(async (query) => {
                try {
                    let result = await search(query);
                    albums.value = [...result.data];
                } catch (e) {
                    // Whatever
                }
            }, 250)
        );

        return {
            query,
            albums,
        };
    },
};
</script>