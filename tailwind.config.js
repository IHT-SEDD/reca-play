import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import daisyui from "daisyui";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            // FONTS
            fontFamily: {
                sans: ["Montserrat", ...defaultTheme.fontFamily.sans],
            },

            // COLORS
            colors: {
                "white-chalk": "#F7F4F1", // Background

                // Shades of white
                "white-owl": "#f5f4f4",
                "white-edgar": "#EEEDED",
                orochimaru: "#D9D9D9",
                magnesium: "#C2C2C2",

                // Shades of black
                "after-midnight": "#38383F",
                "eerie-black": "#1C1C1C",
                "chaos-black": "#0F0F0F",

                // Shades of red
                "vivaldi-red": "#EA3A3A",
                "hot-shot": "#EC5228",
                miami: "#f6921e",
            },

            // MAX-W
            maxWidth: {
                "8xl": "88rem",
                "9xl": "96rem",
                "10xl": "102rem",
                "11xl": "110rem",
            },
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        themes: ["bumblebee"],
        darkTheme: "bumblebee",
    },
};
