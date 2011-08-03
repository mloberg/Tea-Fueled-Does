// domReady event
window.onDomReady = DomReady;
function DomReady(fn){if(document.addEventListener){document.addEventListener("DOMContentLoaded", fn, false);}else{document.onreadystatechange = function(){readyState(fn);}}}
function readyState(fn){if(document.readyState == "interactive") fn();}