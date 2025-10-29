import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import daisyui from "daisyui";
import flowbiteplugin from "flowbite/plugin";

// ========== IMPORT FILES FROM TAILWIND FOLDER ==========
import safelist from "./tailwind/safelist.js";
import customColors from "./tailwind/extend.colors.js";
import customSizes from "./tailwind/extend.sizes.js";
import daisyuiConfig from "./tailwind/daisyui-config.js";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./public/assets/js/**/*.js",
        "./node_modules/flowbite/**/*.js",
    ],

    // ========== SAFELIST FOR CLASSES ==========
    safelist,

    theme: {
        extend: {
            // ========== FONTS ==========
            fontFamily: {
                sans: ["Montserrat", ...defaultTheme.fontFamily.sans],
            },
            // ========== CUSTOM COLORS ==========
            colors: {
                ...customColors,
            },
            // ========== CUSTOM SIZES ==========
            maxWidth: {
                ...customSizes,
            },
        },
    },
    plugins: [forms, daisyui, flowbiteplugin],
    // ========== DAISY UI CONFIG ==========
    daisyui: daisyuiConfig,
};
