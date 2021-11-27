import mitt from "mitt";

const EventBus = mitt();
const EVENTS = {
    SEARCH_INPUT: "SEARCH_INPUT",
    SEARCH_FOCUS: "SEARCH_FOCUS",
    SEARCH_BLUR: "SEARCH_BLUR",
};

export { EventBus, EVENTS };