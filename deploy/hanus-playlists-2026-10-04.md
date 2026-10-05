# YouTube playlisty Hanusových dní — 4. 10. 2026

Zdroje boli overené cez YouTube Data API (`playlists.list`) na oficiálnom kanáli Hanusove Dni: https://www.youtube.com/channel/UC2hBgEp6D1fno_PwcQyJlBA/playlists.

- 2023: https://www.youtube.com/playlist?list=PLCu4owYx2YSTV5CBymgvp9Vg5vPRHrAFj
- 2024: https://www.youtube.com/playlist?list=PLCu4owYx2YSQ0lfMz9boCaOO7CkYQKhxZ
- 2025: https://www.youtube.com/playlist?list=PLCu4owYx2YSSBN0W-Dg2cCbLy6F6ugPGg
- 2019 opravený z BHD Life na hlavný ročník: https://www.youtube.com/playlist?list=PLCu4owYx2YSSZBqLbKWuvqbzpRAOk-xAt

Roky 2016–2018 a 2020–2022 mali správne playlisty. Všetky boli znovu načítané, pričom predchádzajúce väzby ostali zachované. Rok 2026 nemá na oficiálnom kanáli verejný playlist celých prednášok: dostupné sú len promo a Shorts. Pole preto ostalo prázdne.

Lokálny import pridal 134 nových videí a 139 väzieb na semináre. Roky 2023/2024/2025 majú po importe 23/26/29 videí. Blokované a zmazané záznamy sa neobnovovali. Nové videá sa uložili do existujúceho kanála Bratislavské Hanusove Dni, vlastníctvo seminárov sa nemenilo.

## Produkcia

Pripravená dátová migrácia podľa názvov ročníkov, bez lokálnych ID; neprepisuje odlišné ručne nastavené zdroje:

```sh
php artisan migrate --force --path=database/migrations/2026_10_04_180000_set_hanus_seminar_playlists.php
```

Migrácia iba dopĺňa playlisty a nevolá sieť. Na produkcii potom spustiť načítanie playlistov v správe seminárov. Produkčné nasadenie ani import sa v tejto úlohe nevykonávali.

Test: `php artisan test --compact --filter=HanusSeminarPlaylistsTest` — prešiel, 6 kontrol.
