<?php

return [
    'patterns' => [
        'vulgárnosť alebo osobné urážky' => [
            '/\b(?:kokot|pic[auieoy]|pice|kurv|jeb|debil|idiot|imbecil|kreten|zmrd|dumbfuck|motherfuck|fuck|shit)[a-z]*\b/i',
            '/\b(?:blbec|blbci|blbce|tupec|tupci|hovno|hovna|srat|vysral|chrapoun|vychcanek)\b/i',
            '/\bk[\W_]*o[\W_]*k[\W_]*o[\W_]*t\b/i',
        ],
        'vyhrážky alebo podnecovanie k násiliu' => [
            '/\b(?:zabijem|zabiju|zastrelim|zastrelime|znicim)\s+(?:ta|te|vas|vam|tvoju|tvoji)\b/i',
            '/\b(?:zabite|zabijte|postrielajte|postrilejte|vyvrazdit|vyvrazdime|zlikvidujte)\b/i',
            '/\b(?:kill|shoot|murder)\s+(?:you|them|all)\b/i',
            '/\b(?:mal|mala)\s+by si\s+(?:skapat|zdochnut)\b/i',
        ],
    ],
];
