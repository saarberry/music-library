<template>
    <section class="Library">
        <transition-group
            :name="shouldAnimate ? 'album-list' : 'nop'"
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
            <span class="Hint" v-if="shouldDisplayHint">
                This search is meant to animate, but since fading 500+ elements
                is heavy on the browser, I figured I'd save you the processing
                power by displaying this message instead. Go ahead though, type
                something!
            </span>
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
            debounce((input) => (query.value = input), 100)
        );

        let focus = ref(false);
        EventBus.on(EVENTS.SEARCH_FOCUS, () => (focus.value = true));
        EventBus.on(EVENTS.SEARCH_BLUR, () => (focus.value = false));

        let filteredAlbums = computed(() => {
            if (query.value.length == 0) {
                return focus.value ? [] : albums.value;
            }

            return fuse.search(query.value).map((result) => result.item);
        });

        let shouldDisplayHint = computed(
            () => query.value.length == 0 && focus.value
        );
        let shouldAnimate = computed(() => query.value.length > 0);

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

        return {
            query,
            filteredAlbums,
            forceSize,
            revertSize,
            shouldDisplayHint,
            shouldAnimate,
        };
    },
};
</script>