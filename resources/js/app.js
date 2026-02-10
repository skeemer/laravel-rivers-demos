import jointJs from "./joint-js/joint-js.js";
import cell from "./joint-js/cell.js";
import link from "./joint-js/link.js";

document.addEventListener('alpine:init', () => {
    Alpine.data('jointJs', jointJs)
    Alpine.data('jjCell', cell)
    Alpine.data('jjLink', link)
});
