import jointJs from "./joint-js/joint-js.js";
import link from "./joint-js/link.js";

document.addEventListener('alpine:init', () => {
    Alpine.data('jointJs', jointJs)
    Alpine.data('jjLink', link)
});
