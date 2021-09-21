<template>
    <div class="Search">
        <input
            class="Search__Input"
            placeholder="Album, artist or songname..."
            @input="emitSearch"
        />
        <SearchIcon />
    </div>
</template>

<script>
import SearchIcon from "@/components/atoms/icons/Search.vue";
import { EVENTS, EventBus } from "@/events.js";

export default {
    components: { SearchIcon },
    setup() {
        const emitSearch = (event) => {
            EventBus.emit(EVENTS.SEARCH_INPUT, event.target.value);
        };

        return { emitSearch };
    },
};
</script>