import mitt from "mitt";

const EventBus = mitt();
const EVENTS = {
    SEARCH_INPUT: "SEARCH_INPUT",
};

export { EventBus, EVENTS };