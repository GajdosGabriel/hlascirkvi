import axios from 'axios';

/**
 * Súbor pôvodne exponoval na window aj lodash, popper.js a jQuery — pozostatok
 * po Bootstrape z pôvodnej kostry. V celom resources/ (Vue komponenty aj Blade
 * šablóny) ich nevolalo nič; posledné jQuery volanie bolo $(el).fadeOut()
 * v comments/Comment-Item.vue a nahradil ho CSS prechod.
 *
 * Axios sa načítava kvôli tomu, že komponenty ho používajú cez window.axios
 * a hlavička X-Requested-With sa nastavuje na jednom mieste.
 */

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
