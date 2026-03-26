import jointJs from "./joint-js/joint-js.js";

document.addEventListener('alpine:init', () => {
    Alpine.data('jointJs', jointJs)
});
