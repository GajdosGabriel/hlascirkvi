/**
 * Formátovanie dátumov pre výpisy modlitieb.
 *
 * Kvôli týmto dvom filtrom sa do bundlu ťahal celý moment.js (~70 kB gzip,
 * balík je v režime údržby). Intl.DateTimeFormat aj Intl.RelativeTimeFormat
 * vedia slovensky a sú súčasťou prehliadača.
 */

const RELATIVE = new Intl.RelativeTimeFormat("sk", { numeric: "auto" });

// Od najväčšej jednotky po najmenšiu; prvá, do ktorej sa rozdiel zmestí, vyhrá.
const UNITS = [
    ["year", 365 * 24 * 3600],
    ["month", 30 * 24 * 3600],
    ["week", 7 * 24 * 3600],
    ["day", 24 * 3600],
    ["hour", 3600],
    ["minute", 60],
];

export const filterMixin = {
    filters: {
        dateTime: function (value) {
            const date = new Date(value);

            if (isNaN(date.getTime())) {
                return value;
            }

            const minutes = String(date.getMinutes()).padStart(2, "0");

            return `${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}, ${date.getHours()}:${minutes}`;
        },

        humanDateTime: function (value) {
            const date = new Date(value);

            if (isNaN(date.getTime())) {
                return value;
            }

            // Pôvodne moment(...).startOf('hour').fromNow() — zaokrúhlenie na
            // celú hodinu ostáva, aby sa výpis nezmenil.
            date.setMinutes(0, 0, 0);

            const seconds = (date.getTime() - Date.now()) / 1000;

            for (const [unit, size] of UNITS) {
                if (Math.abs(seconds) >= size) {
                    return RELATIVE.format(Math.round(seconds / size), unit);
                }
            }

            return RELATIVE.format(0, "hour");
        },
    },
};
