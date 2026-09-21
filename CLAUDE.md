# Pokyny pre Claude

- Vždy odpovedaj používateľovi po slovensky.
- Po každej zmene, ktorá sa týka frontendu (`resources/js/**`, `resources/css/**`, Vue komponenty, Blade šablóny s Tailwind triedami, `tailwind.config.js`, `vite.config.js`, `package.json`) alebo pri zmene API endpointu (`routes/**`, controllery, zmena URL, parametrov alebo tvaru odpovede, ktorú frontend volá), automaticky spusti `npm run build` bez pýtania. Build výstup v `public/build` je verzovaný v gite, takže ho commitni spolu so zmenou.
