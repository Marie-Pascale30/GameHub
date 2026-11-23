# GameHub v2

Catalogue communautaire de jeux vidéo : on explore une trentaine de classiques, on garde ses **coups de cœur**, on **note** et on **donne son avis**.
Application PHP 8.3 écrite sans framework lourd : un petit MVC fait maison avec un front controller, un routeur, des contrôleurs, des modèles PDO et des templates PHP.

## Fonctionnalités

- **Catalogue** de 34 jeux réels (titre, plateformes, genres, année, studio, synopsis en français rédigé pour le projet).
- **Recherche instantanée**, **filtres** (genre, plateforme, décennie) et **tri** (popularité, note, date, titre). Tout passe par `fetch` sur une API JSON, et l'URL reste partageable.
- **Défilement infini**, avec une pagination classique si JavaScript est désactivé.
- **Fiche jeu** : moyenne et répartition des notes, avis des joueurs et jeux similaires.
- **Coups de cœur** ajoutés ou retirés sans rechargement, plus une page « Mes coups de cœur ».
- **Notes de 1 à 5 et avis courts** : une seule note par joueur et par jeu, modifiable ou supprimable.
- **Profil** : avatar à initiales, statistiques, genres préférés, activité récente, changement de pseudo et de mot de passe.
- **Administration** (rôle `admin`) : ajouter, modifier et supprimer des jeux, avec validation complète.
- **Jaquettes génératives** : un dégradé et un motif SVG sont dérivés du titre, avec la typographie du titre et un badge de plateforme. Aucune image protégée n'est téléchargée ni liée.
- **Thème sombre** par défaut et thème clair, mémorisés dans le navigateur. Mise en page responsive, du mobile au grand écran.

## Comptes de démonstration

| Rôle   | E-mail              | Mot de passe |
|--------|---------------------|--------------|
| Joueur | `joueur@gamehub.fr` | `password`   |
| Admin  | `admin@gamehub.fr`  | `password`   |

Sept joueurs fictifs alimentent les notes et les avis. Leurs comptes ne permettent pas de se connecter (mot de passe aléatoire).

## Lancer le projet

### Avec Docker (recommandé)

```bash
docker build -t gamehub .
docker run -p 8080:8080 gamehub
# → http://localhost:8080
```

Au premier démarrage, l'entrypoint crée la base SQLite, applique les migrations et charge les données de démo.
Le port d'écoute suit la variable `PORT`, ce qui le rend compatible avec l'offre gratuite de Render.

### Sans Docker

```bash
cp .env.example .env
php bin/migrate.php
php bin/seed.php
php -S localhost:8080 -t public
```

Extensions requises : `pdo_sqlite` (ou `pdo_mysql`).

### MySQL / MariaDB

```bash
DB_DRIVER=mysql DB_HOST=… DB_NAME=gamehub DB_USER=… DB_PASS=…
```

Les migrations du dossier `database/migrations/mysql/` sont alors utilisées. Le seed ne s'exécute que si la base est vide (`--if-empty`).

## Architecture

```
public/            seul dossier exposé : index.php (front controller), assets CSS/JS
src/Core/          App, Router, Request, Response, View, Session, Csrf, Auth, RateLimiter, Database, Config
src/Controllers/   Catalog, Game, Auth, Password, Favorite, Review, Profile, Admin
src/Models/        User, Game, Favorite, Review, PasswordReset (PDO, requêtes préparées)
src/Support/       Cover (jaquettes SVG), Validator, helpers (échappement, icônes, dates)
templates/         layout, pages et partiels
database/          migrations SQLite + MySQL, données du catalogue
bin/               migrate.php, seed.php
docker/            configuration Apache/PHP, entrypoint
```

## Sécurité

- **Mots de passe** : `password_hash` / `password_verify`, avec ré-hachage automatique si l'algorithme évolue. Les règles de robustesse (10 caractères, majuscule, minuscule, chiffre, caractère spécial) sont vérifiées côté serveur et affichées en direct.
- **Base de données** : PDO avec des requêtes préparées natives (pas d'émulation). Le tri passe par une liste blanche et les filtres sont validés contre les valeurs présentes en base. Les jokers `%` et `_` sont échappés dans les recherches `LIKE`.
- **CSRF** : un jeton de session est vérifié sur **toute** requête POST, via un champ caché dans les formulaires ou l'en-tête `X-CSRF-Token` pour les appels `fetch`. Il est comparé avec `hash_equals`.
- **Session** : cookie `HttpOnly`, `SameSite=Lax` (et `Secure` en HTTPS), mode strict. L'identifiant de session et le jeton CSRF sont régénérés à la connexion et au changement de mot de passe.
- **Échappement** systématique des sorties (`e()`) et **Content-Security-Policy** stricte (`script-src 'self'`, `style-src 'self'`, sans style inline). La page envoie aussi `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy` et `Permissions-Policy`.
- **Limitation des tentatives de connexion** : 5 échecs par e-mail et 20 par IP sur 15 minutes (réponse 429 avec `Retry-After`). Les demandes de réinitialisation sont limitées par IP. Les clés sont stockées hachées.
- **Anti-énumération** : un message de connexion générique et un temps de réponse constant (hash factice). Le « mot de passe oublié » répond toujours la même chose.
- **Réinitialisation du mot de passe** : un jeton aléatoire de 256 bits, dont **seul le SHA-256 est stocké**. Il expire au bout de 30 minutes, ne sert qu'une fois, et les anciens jetons sont invalidés.
  - **Mode démo** (`APP_DEMO=1`) : comme aucun serveur SMTP n'est configuré, le lien est affiché à l'écran dans un encadré « Mode démo » et écrit dans les journaux du conteneur.
  - En production, il suffit de passer `APP_DEMO=0` : le lien n'apparaît plus que dans les journaux, en attendant un vrai envoi d'e-mail.
- **Autorisations** : les routes d'administration sont réservées au rôle `admin` (403 sinon). Les redirections `next` n'acceptent que des chemins internes (anti open-redirect).
- **Exposition** : seul `public/` est servi. Le code, les templates et la base SQLite (`var/`) sont hors de la racine web. Les erreurs sont journalisées, jamais affichées en production.

## Historique

- **v1 — projet scolaire de BTS SIO (octobre à décembre 2025).** Première application PHP/MySQL réalisée en 2e année : inscription, connexion, catalogue en cartes Bootstrap, bouton « coup de cœur », page favoris et thème clair/sombre.
- **v2 — réécriture personnelle (novembre 2026).** Même concept, reconstruit de zéro pour le portfolio, avec une architecture propre, une sécurité sérieuse et de vraies fonctionnalités communautaires.

### Ce qui a changé, et pourquoi

| Problème de la v1 | Correction en v2 |
|---|---|
| Les `cover_url` pointaient vers des pages de recherche Bing, pas vers des images (et vers du contenu protégé). | Les jaquettes sont générées en SVG à partir du titre : aucune dépendance externe, aucun problème de droits. |
| La requête du catalogue ne sélectionnait pas `id`, donc les boutons favoris n'avaient pas d'identifiant. | Les modèles PDO renvoient des entités complètes. Les favoris passent par une API JSON `POST /api/jeux/{id}/favori`. |
| `favorites.php` lisait des colonnes `genre` et `release_year` inexistantes. | Le schéma est normalisé (tables `genres`, `platforms` et tables de liaison) et versionné par des migrations. |
| Le profil était une carte statique « John Doe ». | Le profil est réel : statistiques, genres préférés, activité, modification du pseudo et du mot de passe. |
| Le mot de passe oublié modifiait une colonne `password` inexistante, sans aucun jeton. | Le flux de réinitialisation utilise un jeton haché, à usage unique et qui expire. |
| `favoritesBack` utilisait `$_SESSION` sans `session_start()`. | Une session centralisée et durcie est démarrée par le front controller. |
| Pas de protection CSRF, sorties partiellement échappées, `session_start()` appelé deux fois, mots de passe en dur dans la configuration. | Protection CSRF globale, échappement systématique, CSP, et configuration par variables d'environnement. |
| Les règles de mot de passe étaient combinées avec un ET logique erroné (`!A && !B && !C`), donc un mot de passe sans majuscule passait. | Chaque règle est vérifiée séparément et le message liste ce qui manque. |
| Scripts PHP appelés directement (`controllers/*.php`) et dossier `models/` accessible depuis le web. | Un front controller unique et un routeur. Seul `public/` est exposé. |
| Mise en forme Bootstrap par défaut. | Une feuille de style maison au rendu « catalogue gaming » : cartes riches, effets de survol, deux thèmes. |

## Tests

Le parcours complet a été vérifié de bout en bout avec Playwright :

- inscription refusée avec un mot de passe faible ;
- connexion ;
- recherche et filtres ;
- défilement infini ;
- coups de cœur ;
- notes et avis ;
- modification du profil ;
- réinitialisation du mot de passe ;
- administration ;
- rejets CSRF ;
- tentatives d'injection SQL ;
- limitation des connexions ;
- en-têtes de sécurité.
