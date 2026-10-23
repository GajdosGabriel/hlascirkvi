# Pokyny pre Claude

- Po zmene v `resources/**` (JS, CSS, Vue, Blade — Tailwind skenuje aj Blade), `tailwind.config.js`, `vite.config.js` alebo `package.json` spusti bez pýtania `npm run build`.
- `public/build` je verzovaný v gite a patrí do toho istého commitu ako zmena, ktorá ho vyvolala. Produkcia sa nasadzuje ručne cez `deploy/deploy.sh` a na serveri sa nebuilduje.
