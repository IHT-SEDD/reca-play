// ========== COLORS LIST ==========
const colorNames = [
    // Shades of white
    "white-chalk",
    "white-owl",
    "white-edgar",
    "christmas-silver",
    "orochimaru",

    // Shades of grey
    "magnesium",
    "adhesion",
    "evening-inparis",
    "carbon",
    "reversed-grey",

    // Shades of black
    "after-midnight",
    "thamar-black",
    "eerie-black",
    "chaos-black",

    // Shades of red
    "sunday-best",
    "candy-heart",
    "vivaldi-red",

    // Shades of orange
    "temple-orange",
    "fuego",
    "hot-shot",
    "miami",
    "kin-gold",

    // Shades of yellow
    "creamy-korn",

    // Shades of green
    "lilliputian-lime",
    "exit-light",
    "toxic-essence",
    "winter-oasis",

    // Shades of daisy ui
    "base-100",
    "base-200",
    "base-300",
];

// Generate class list langsung (tidak pakai regex)
const colorSafelist = colorNames.flatMap((c) => [
    `bg-${c}`,
    `text-${c}`,
    `dark:bg-${c}`,
    `dark:text-${c}`,
    `hover:bg-${c}`,
    `hover:text-${c}`,
    `dark:hover:bg-${c}`,
    `dark:hover:text-${c}`,
]);

export default [
    ...colorSafelist,

    // ========== Font Weights ==========
    { pattern: /font-(medium|semibold|bold)/ },

    // ========== Text Size ==========
    { pattern: /text-(xs|sm|md|lg|xl|2xl|3xl|4xl|5xl)/ },

    // ========== Tracking ==========
    { pattern: /tracking-(wide)/ },

    // ========== Paddings ==========
    { pattern: /(p|px|py|pt|pr|pb|pl)-[0-9]+/ },

    // ========== Roundeds ==========
    { pattern: /rounded-(sm|md|lg|xl|2xl|3xl|4xl|5xl)/ },

    // ========== Flex ==========
    { pattern: /flex(-(col|row))?/ },

    // ========== Justify ==========
    { pattern: /justify-(center|between)/ },

    // ========== Item Placements ==========
    { pattern: /items-(center|start|end)/ },

    // ========== Placements ==========
    { pattern: /(bottom|right|top)-[0-9]+/ },

    // ========== Gap ==========
    { pattern: /gap-[0-9]+/ },

    // ========== Gap ==========
    { pattern: /col-span-[0-9]+/ },
];
