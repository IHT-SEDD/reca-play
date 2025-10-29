import "./bootstrap";
import Alpine from "alpinejs";
import { createIcons, icons } from "lucide";
import { Notyf } from "notyf";
import "notyf/notyf.min.css";
import "flowbite";
import '@lottiefiles/dotlottie-wc';

window.Alpine = Alpine;
Alpine.start();
window.lucide = { createIcons, icons };
createIcons({ icons });
window.notyf = new Notyf({
    duration: 3000,
    position: { x: "right", y: "top" },
});
