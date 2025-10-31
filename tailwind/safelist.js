import colors from "./extend.colors.js";

const colorNames = Object.keys(colors);

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

    // ========== Column span ==========
    { pattern: /col-span-[0-9]+/ },

    // ========== Cursor States ==========
    {
        pattern:
            /cursor-(auto|default|pointer|wait|text|move|not-allowed|crosshair|help|grab|grabbing)/,
    },

    // ========== Opacity Levels ==========
    { pattern: /opacity-(0|5|10|20|25|30|40|50|60|70|75|80|90|95|100)/ },

    // ========== Badge colors ==========
    { pattern: /badge-(primary|secondary|accent|success|error|info|outline)/ },

    // ========== Badge ==========
    "badge",
];
