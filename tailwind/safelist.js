export default [
    // ========== Background Colors ==========
    { pattern: /bg-toxic-essence(\/[0-9]+)?/ },
    { pattern: /bg-lilliputian-lime(\/[0-9]+)?/ },
    { pattern: /bg-vivaldi-red(\/[0-9]+)?/ },
    { pattern: /bg-hot-shot(\/[0-9]+)?/ },
    { pattern: /bg-base-(100|200|300)/ },
    "bg-candy-heart",

    // ========== Text Colors ==========
    { pattern: /text-white(\/[0-9]+)?/ },
    { pattern: /text-lilliputian-lime(\/[0-9]+)?/ },
    { pattern: /text-vivaldi-red(\/[0-9]+)?/ },
    { pattern: /text-base-(100|200|300)/ },
    { pattern: /text-carbon(\/[0-9]+)?/ },

    // ========== Font Weights ==========
    { pattern: /font-(medium|semibold|bold)/ },

    // ========== Text Size ==========
    { pattern: /text-(xs|sm|md|lg|xl|2xl|3xl|4xl|5xl)/ },

    // ========== Paddings ==========
    { pattern: /px-(2)/ },
    { pattern: /py-(1|2)/ },

    // ========== Roundeds ==========
    { pattern: /rounded-(sm|md|lg|xl|2xl|3xl|4xl|5xl)/ },

    // ========== Flex's ==========
    { pattern: /flex-(col|row)/ },

    // ========== Justify's ==========
    { pattern: /justify-(center|between)/ },

    // ========== Item Placements ==========
    { pattern: /items-(center|start|end)/ },

    // ========== Gap's ==========
    { pattern: /gap-(1)/ },

    // ========== Hover's ==========
    { pattern: /hover:bg-hot-shot(\/[0-9]+)?/ },
];
