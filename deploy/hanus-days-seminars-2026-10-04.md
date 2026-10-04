# Bratislavské Hanusove dni 2023–2026

Migrácia: `2026_10_04_120000_create_hanus_days_seminars_2023_to_2026.php`.

Na produkcii po nasadení spustiť bežné `php artisan migrate --force`.
Samostatne iba túto migráciu možno spustiť:

```sh
php artisan migrate --force --path=database/migrations/2026_10_04_120000_create_hanus_days_seminars_2023_to_2026.php
```

Vlastníka nových seminárov preberá zo seminára „Bratislavské Hanusove dni 2022“ podľa názvu, bez závislosti od ID lokálnej databázy. Ak vzorový seminár chýba, nič nemení.

Vytvorí a zverejní semináre 2023, 2024 a 2025. Rok 2026 vytvorí iba pri nájdení aspoň jedného videa. Existujúce aktívne semináre rovnakého názvu a vlastníka použije bez zmeny ich nastavení. YouTube playlisty nevymýšľa a nič nesťahuje.

Priradí existujúce nezmazané videá zo všetkých kanálov podľa explicitného označenia podujatia a ročníka v názve: BHD23, BHD 2023, Bratislavské Hanusove dni 2023 alebo Bratislava Hanus Days 2023 (analogicky 2024–2026). Videá s viacerými rôznymi ročníkmi v názve vynechá. Samotný rok, dátum publikácie a zmienka v popise nestačia. Videá bez takého označenia treba priradiť ručne.

Zmení sekciu priradených videí na `seminar`, aby sa zobrazovali medzi prednáškami. Zachová pôvodný kanál videa, zverejnenie a všetky existujúce väzby. Nevytvára kópie videí ani duplicitné väzby. Úpravy dát vykoná v jednej transakcii.

Migrácia je dátová a jej `down()` zaradenie nevracia späť, aby neodstránila neskoršie ručné úpravy. Na presné obnovenie pôvodného zaradenia treba použiť databázovú zálohu.
