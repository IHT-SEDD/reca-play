import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import daisyui from "daisyui";

// ========== IMPORT FILES FROM TAILWIND FOLDER ==========
import safelist from "./tailwind/safelist.js";
import customColors from "./tailwind/extend.colors.js";
import customSizes from "./tailwind/extend.sizes.js";
import daisyuiConfig from "./tailwind/daisyui-config.js";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            // ========== FONTS ==========
            fontFamily: {
                sans: ["Montserrat", ...defaultTheme.fontFamily.sans],
            },
            // ========== CUSTOM COLORS ==========
            colors: customColors,
            // ========== CUSTOM SIZES ==========
            maxWidth: customSizes,
        },
    },
    plugins: [forms, daisyui],
    // ========== DAISY UI CONFIG ==========
    daisyui: daisyuiConfig,
    // ========== SAFELIST FOR CLASSES ==========
    safelist,
};
