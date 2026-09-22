# Vite & Gourmand

Site de traiteur événementiel (ECF Développeur Web et Web Mobile).
Stack : PHP 8.3 pur + PDO, MySQL 8.4, MongoDB 7 (statistiques), HTML/CSS/JavaScript vanilla, Docker.

Avancement : front public intégré, environnement Docker et base de données prêts, pages menus (liste avec filtres dynamiques, détail) branchées sur MySQL, authentification complète (inscription, connexion, rôles, déconnexion, réinitialisation du mot de passe). Le tunnel de commande et l'espace utilisateur (commandes, modification, annulation, suivi, avis, profil) sont en place. Le back-office employé (commandes, menus, plats, horaires, modération des avis) est en place, avec le footer et l'accueil branchés sur la base. L'espace administrateur est en place : comptes employés et statistiques alimentées par MongoDB.

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (ou Docker Engine + plugin Compose v2)
- Git

## Lancer le projet en local

```bash
git clone <url-du-depot>
cd vite-gourmand

cp .env.example .env
docker compose up -d --build
```

Sous PowerShell, remplacer `cp` par `Copy-Item .env.example .env`.

Au premier démarrage, MySQL exécute automatiquement `database/schema.sql` (création des tables) puis `database/seed.sql` (données de démo). La première construction de l'image PHP est un peu longue (compilation de l'extension MongoDB).

| Service | URL / port (local uniquement) |
|---|---|
| Site | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| MySQL | `127.0.0.1:3306` |
| MongoDB | `127.0.0.1:27017` |

Tous les ports sont liés à `127.0.0.1` : rien n'est exposé sur le réseau. Les ports et identifiants se modifient dans `.env`.

Connexion à phpMyAdmin : utilisateur et mot de passe `MYSQL_USER` / `MYSQL_PASSWORD` du fichier `.env` (ou `root` / `MYSQL_ROOT_PASSWORD`).

## Base de données

Fichiers SQL livrés :

- `database/schema.sql` : supprime puis recrée toutes les tables (15 tables, clés étrangères, contraintes `CHECK`). Il reconstruit la base à zéro.
- `database/seed.sql` : vide les tables puis insère les données de démo.

### Réimporter ou repartir de zéro

Réimport sans supprimer les conteneurs (bash) :

```bash
docker compose exec -T mysql sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/schema.sql
docker compose exec -T mysql sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/seed.sql
```

PowerShell :

```powershell
Get-Content database/schema.sql -Raw | docker compose exec -T mysql sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
Get-Content database/seed.sql -Raw | docker compose exec -T mysql sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
```

Réinitialisation complète (supprime les volumes MySQL et MongoDB, puis réimporte au redémarrage) :

```bash
docker compose down -v
docker compose up -d
```

### Modèle de données

Le modèle suit le MCD de l'Annexe 1 du sujet :

- `menu` est rattaché à un `theme` et à un `regime` (clés étrangères `theme_id` et `regime_id`).
- `menu` et `plat` sont liés par `menu_plat` : un plat peut figurer dans plusieurs menus.
- `plat` et `allergene` sont liés par `plat_allergene`.
- `utilisateur` possède un `role`, passe des `commande` sur un `menu` et publie des `avis`.
- `horaire` est autonome (dimanche fermé : heures à `NULL`).

Ajouts nécessaires aux exigences du sujet : `menu_image` (galerie), `commande_suivi` (historique des statuts), `password_reset` (réinitialisation par mail), et les colonnes `utilisateur.nom`, `utilisateur.actif`, `menu.conditions`, `plat.categorie`, `commande.adresse_prestation`, `commande.ville_prestation`, `commande.motif_annulation`, `commande.mode_contact_annulation`, `commande.code_postal_prestation`, `commande.telephone_contact`, `commande.remise`, `commande.distance_km`, `commande.distance_source`, `menu.delai_minimum_jours`, `menu.actif`, `utilisateur.code_postal`, `commande_suivi.commentaire`, `commande_suivi.auteur_id`, `avis.commande_id`.

Types adaptés par rapport au MCD : `DECIMAL(10,2)` pour les montants, `TEXT` pour les textes longs, `TINYINT` (1 à 5) pour la note, `TIME` pour les heures, chemin de fichier à la place du `BLOB` pour les photos.

### Connexion PDO

`app/config/database.php` expose `db()`, qui retourne une instance PDO unique configurée en mode exception, en `utf8mb4`, avec requêtes préparées natives. Les paramètres viennent des variables d'environnement `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, fournies au conteneur PHP par `docker-compose.yml` à partir de `.env`.

```php
require __DIR__ . '/app/config/database.php';

$statement = db()->prepare('SELECT titre FROM menu WHERE prix_par_personne <= :prix');
$statement->execute(['prix' => 50]);
```

### Pages menus et API

- `/menus` : liste des menus lue en base, avec filtres (prix maximum, fourchette de prix, thème, régime, nombre de personnes). Avec JavaScript, la liste se met à jour sans rechargement ; sans JavaScript, le formulaire (méthode GET) filtre en rechargeant la page.
- `/menus/{id}` : détail complet d'un menu (galerie, plats par catégorie avec allergènes, conditions, stock). Le bouton « Commander » pointe vers `/commande?menuId={id}`.
- `GET /api/menus.php` : menus filtrés au format JSON. Paramètres optionnels : `prix_max`, `fourchette_min`, `fourchette_max`, `theme` (id), `regime` (id), `personnes` (les menus dont le minimum est inférieur ou égal à ce nombre). Les valeurs invalides sont ignorées, toutes les requêtes SQL sont préparées.

```bash
curl "http://localhost:8080/api/menus.php?theme=4&prix_max=80"
```

### Authentification

| Route | Accès | Rôle |
|---|---|---|
| `/inscription` | visiteur | crée un compte avec le rôle `utilisateur` (jamais un autre rôle) |
| `/connexion` | visiteur | ouvre la session ; paramètre `retour` limité aux chemins internes |
| `/deconnexion` | connecté | POST uniquement (bouton du menu compte) |
| `/mot-de-passe-oublie` | visiteur | envoie un lien de réinitialisation valable 1 heure |
| `/reinitialiser-mot-de-passe?token=…` | libre | choix d'un nouveau mot de passe |
| `/mon-compte` | connecté | profil en lecture seule |

La protection d'une page se déclare dans `app/config/routes.php` avec la clé `access` : `'auth'` (connecté), `'guest'` (visiteur uniquement) ou une liste de rôles, par exemple `['employe', 'administrateur']`. Un visiteur est redirigé vers `/connexion`, un rôle insuffisant reçoit une page 403.

E-mails : l'envoi est simulé pour l'instant. Chaque message (bienvenue, lien de réinitialisation) est écrit dans le journal du conteneur PHP :

```bash
docker compose logs php | grep -A4 "MAIL simulé"
```

Le lien de réinitialisation utilise `APP_URL` (défini dans `.env`).

Sécurité mise en place : mots de passe hashés (`password_hash`, bcrypt coût 12), règles de mot de passe du sujet, session `HttpOnly` + `SameSite=Lax` régénérée à la connexion, expiration après 2 h d'inactivité, jeton CSRF vérifié pour toute requête POST, message d'erreur unique à la connexion, jetons de réinitialisation stockés sous forme de hash SHA-256 et à usage unique, requêtes SQL préparées, en-têtes `X-Content-Type-Options`, `X-Frame-Options` et `Referrer-Policy`.

### Commande

| Route | Accès | Rôle |
|---|---|---|
| `/commande?menuId=<id>` | connecté | formulaire de commande (menu pré-sélectionné, sinon choix du menu) |
| `/commande/livraison` | connecté | devis de livraison en JSON (aperçu du formulaire) |
| `/commande/confirmation` | connecté | récapitulatif de la dernière commande passée |

Règles (toutes recalculées côté serveur à la validation, dans `app/services/OrderPricing.php` ; constantes dans `app/config/order.php`) :

- prix du menu = prix par personne × nombre de personnes, nombre de personnes ≥ minimum du menu ;
- réduction de 10 % si le nombre de personnes ≥ minimum + 5 ;
- livraison offerte à Bordeaux, sinon 5,00 € + 0,59 € par km (distance aller simple à vol d'oiseau depuis le centre de Bordeaux) ;
- date de prestation ≥ aujourd'hui + délai de commande du menu ;
- une commande consomme une unité du stock du menu (transaction avec verrou, pas de survente) ;
- statut initial `en_attente`, première ligne de `commande_suivi`, e-mail de confirmation simulé.

Distance de livraison : adresse géocodée par la Géoplateforme (base adresse nationale, gratuite, sans clé, URL modifiable avec `GEOCODER_URL`), distance calculée avec la formule de Haversine, puis enregistrée sur la commande avec sa source (`bordeaux`, `ban` ou `commune`). Si le géocodeur ne répond pas, une table locale de communes (`app/data/communes.php`) sert de repli ; si la commune est inconnue, la commande est refusée avec un message clair plutôt que facturée 0 €.

### Espace utilisateur

Réservé au rôle `utilisateur` (les commandes d'un autre utilisateur répondent toujours 404).

| Route | Action |
|---|---|
| `/mes-commandes` | liste en cartes, onglets Toutes / En cours / Terminées (sans rechargement) |
| `/mes-commandes/{id}` | détail : menu et allergènes, prestation, prix, avis |
| `/mes-commandes/{id}/modifier` | modification (statut `en_attente` seulement, menu verrouillé, prix recalculé) |
| `/mes-commandes/{id}/annuler` | confirmation puis annulation (statut `en_attente` seulement, stock du menu remis) |
| `/mes-commandes/{id}/suivi` | chronologie des états avec date et heure (dès que la commande est acceptée) |
| `/mes-commandes/{id}/avis` | note de 1 à 5 et commentaire (commande `terminee`, un seul avis) ; publié après validation |
| `/mon-compte` | profil (nom, prénom, GSM, adresse, code postal, ville) et changement de mot de passe |

Les statuts et leur regroupement sont décrits dans `app/config/order_status.php`. Modification et annulation sont réalisées dans une transaction qui verrouille la commande : une commande acceptée entre-temps par un employé ne peut plus être modifiée ni annulée. Une commande modifiée conserve le prix unitaire convenu à la commande.

### Back-office (employé et administrateur)

Accessible aux rôles `employe` et `administrateur` (après connexion, redirection vers `/admin/commandes`).

| Route | Action |
|---|---|
| `/admin/commandes`, `/admin/commandes/{id}` | tableau filtrable par statut et client (sans rechargement) et triable ; détail avec coordonnées du client, historique et actions |
| `/admin/commandes/{id}/annuler` | annulation par l'équipe : mode de contact (GSM ou e-mail) et motif obligatoires, stock remis |
| `/admin/menus`, `/admin/menus/nouveau`, `/admin/menus/{id}/modifier` | création et modification des menus (informations, plats, galerie d'images) |
| `/admin/plats`, `/admin/plats/nouveau`, `/admin/plats/{id}/modifier` | plats, allergènes et menus rattachés |
| `/admin/horaires` | horaires du lundi au dimanche (affichés dans le footer et la page contact) |
| `/admin/avis` | modération : onglets En attente / Validés / Refusés, valider ou refuser |

Séquence des statuts, calculée côté serveur (`app/services/OrderFlow.php`) : `en_attente → accepte → en_preparation → en_cours_livraison → livre → [attente_retour_materiel] → terminee`. L'étape « retour du matériel » n'existe que si du matériel a été prêté (case cochée au passage à `livre`). Chaque changement écrit une ligne dans `commande_suivi` avec son auteur, et deux passages envoient un e-mail simulé au client : retour du matériel (10 jours ouvrés, 600 €) et commande terminée (invitation à donner son avis).

Suppression sûre : un menu déjà commandé ne peut pas être supprimé, il s'archive (`menu.actif`) : il disparaît du site public et de la commande mais son historique reste visible. Un menu jamais commandé peut être supprimé avec ses liaisons et ses images téléversées. Un plat rattaché à un menu ne peut pas être supprimé (le message liste les menus concernés).

Images des menus : JPEG, PNG ou WebP (3 Mo maximum, 6000 px maximum), type réel vérifié côté serveur, stockées sous `public/assets/uploads/menus/` avec un nom aléatoire (dossier ignoré par git).

## Espace administrateur

Réservé au rôle `administrateur` (un employé reçoit une page 403). Il réutilise le layout du back-office ; les entrées « Employés » et « Statistiques » n'apparaissent dans le menu que pour l'administrateur.

| Route | Action |
|---|---|
| `/admin/employes` | liste des comptes employés (e-mail = identifiant, statut Actif ou Désactivé), désactivation et réactivation |
| `/admin/employes/nouveau` | création d'un compte employé (e-mail, mot de passe selon la politique du sujet) |
| `/admin/stats` | statistiques lues uniquement dans MongoDB, filtres par menu et par période |
| `/admin/stats/synchroniser` | POST : reconstruit les faits MongoDB depuis MySQL |

Comptes employés : le rôle est imposé côté serveur (`employe`), jamais lu depuis le formulaire, et toute requête qui tente de fournir un rôle est refusée : un compte administrateur ne peut pas être créé depuis l'application. Un e-mail simulé informe l'employé de la création de son compte sans contenir le mot de passe, que l'administrateur lui transmet par un autre canal. « Désactiver » passe `utilisateur.actif` à 0 : la connexion est refusée et une session déjà ouverte est fermée à la requête suivante. Seuls des comptes `employe` peuvent être désactivés ou réactivés.

### Statistiques MongoDB

MySQL reste la source de vérité. MongoDB (base `MONGO_DB`) contient la collection `commande_faits`, un document par commande (`_id` = numéro interne de la commande) : menu, montant en centimes (menu après remise + livraison), dates, statut, indicateur d'annulation. Le document est écrit (upsert idempotent) après chaque validation MySQL : création, modification et annulation par le client, changement de statut et annulation par l'équipe. Si MongoDB est injoignable, la commande n'est pas bloquée : l'erreur est journalisée et le rattrapage se fait par resynchronisation.

Resynchroniser (après un import du jeu de démo ou une réinitialisation des volumes) :

```bash
docker compose exec php php bin/sync-stats.php
```

Le bouton « Resynchroniser depuis MySQL » de `/admin/stats` fait la même chose. Les statistiques sont calculées par des pipelines d'agrégation (`$match`, `$group`, `$sort`). Règle de calcul : le chiffre d'affaires et les compteurs excluent les commandes annulées, qui sont comptées à part ; la période porte sur la date de commande. Les filtres sont typés avant d'entrer dans les pipelines (identifiant entier, dates `AAAA-MM-JJ`) : une valeur invalide est ignorée avec un message.

Connexion : en local, `MONGO_URI` pointe vers le conteneur `mongodb` (défini dans `docker-compose.yml` à partir de `.env`). Pour un hébergement (par exemple MongoDB Atlas), définir `MONGO_URI` dans l'environnement du serveur ; ne jamais écrire d'URL ou d'identifiants dans un fichier versionné.

## Comptes de démonstration

Ces comptes n'existent que dans le jeu de données de démo. Les mots de passe respectent la politique du sujet (10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial) et sont stockés hashés (bcrypt).

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@vitegourmand.fr | `Admin#Gourmand26` |
| Employé | employe@vitegourmand.fr | `Employe#Gourmand26` |
| Utilisateur | sophie.marchand@example.fr | `Sophie#Gourmand26` |
| Utilisateur | marc.delacroix@example.fr | `Marc#Gourmand2026` |

Les données de démo contiennent 5 menus, 19 plats, 14 allergènes, 8 commandes couvrant tous les statuts (dont une annulée et une en attente de retour de matériel) et 4 avis (validé, en attente, refusé).

## Déploiement Render + Aiven + Atlas (mode conteneur)

Le déploiement réutilise le `Dockerfile` du dépôt (pas de buildpack) : MySQL est fourni par une base managée Aiven (connexion SSL obligatoire), MongoDB par un cluster Atlas. Le fonctionnement local (`docker compose up`) n'est pas affecté par ce qui suit.

1. Créer une base MySQL sur [Aiven](https://aiven.io) (offre gratuite). Récupérer dans sa page « Overview » : hôte, port, utilisateur, mot de passe, nom de la base, et télécharger le certificat CA (`ca.pem`).
2. Importer le schéma et le jeu de démo (une seule fois), avec la connexion SSL :
   ```bash
   mysql --ssl-ca=ca.pem -h <host> -P <port> -u <user> -p <base> < database/schema.sql
   mysql --ssl-ca=ca.pem -h <host> -P <port> -u <user> -p <base> < database/seed.sql
   ```
3. Créer un cluster MongoDB Atlas (offre gratuite), autoriser les connexions entrantes (adresse `0.0.0.0/0`, ou les IP sortantes de Render) et créer un utilisateur dédié à l'application.
4. Créer le service sur [Render](https://render.com) : Nouveau → Web Service → connecter le dépôt GitHub. Render détecte `render.yaml` à la racine (type `docker`, `docker/php/Dockerfile`, plan gratuit). Renseigner dans l'onglet « Environment » les variables marquées `sync: false` dans `render.yaml` :
   - `APP_URL` : `https://<nom-du-service>.onrender.com`
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` : les valeurs Aiven de l'étape 1
   - `DB_SSL_CA` : le contenu complet de `ca.pem`, collé tel quel (lu par `app/config/database.php`)
   - `MONGODB_URI` : l'URI Atlas (`mongodb+srv://…`)
   - `MONGO_DB` : `vite_gourmand_stats`
5. Déployer (automatique à chaque push sur la branche connectée, ou bouton « Manual Deploy »).
6. Lancer la première synchronisation MongoDB (à refaire après tout réimport du jeu de démo), depuis l'onglet « Shell » du service Render :
   ```bash
   php bin/sync-stats.php
   ```
7. Parcours du jury : utiliser les comptes de démonstration listés ci-dessous (administrateur, employé, utilisateur).

Durcissement activé par `APP_ENV=production` (voir `app/helpers.php`, `app/bootstrap.php`, `app/services/Session.php`) : affichage des erreurs PHP désactivé (page générique 500, erreur toujours journalisée côté serveur), en-tête `Content-Security-Policy`, cookie de session `Secure` en plus de `HttpOnly` et `SameSite=Lax`. Aucune clé applicative supplémentaire n'est nécessaire : le site utilise les sessions PHP natives (pas de JWT, pas de cookie chiffré).

**Limites connues sur Render (plan gratuit) :**
- Le système de fichiers est éphémère : les images de menus ajoutées depuis le back-office (`/admin/menus`) ne survivent pas à un redéploiement. Seules les images de démo déjà commitées dans `public/assets/img/` persistent.
- Le service se met en veille après une période d'inactivité : la première requête qui le réveille peut prendre plusieurs dizaines de secondes.
- Les sessions PHP natives sont stockées sur le disque local de l'instance : au-delà d'une seule instance, ou après son redémarrage, les sessions ouvertes sont perdues (sans impact pour une démonstration).

## Livrables ECF

Le dossier `docs/` contient les documents demandés par le sujet, en complément du code, des fichiers SQL et de ce README :

| Fichier | Contenu |
|---|---|
| `manuel-utilisation.pdf` | Présentation de l'application et parcours par rôle, avec les comptes de démonstration |
| `charte-graphique.pdf` | Palette de couleurs, typographies, 3 maquettes bureau et 3 maquettes mobile |
| `documentation-technique.pdf` | MCD, diagramme d'utilisation, diagramme de séquence, résumé du déploiement |
| `documentation-gestion-de-projet.pdf` | Méthodologie, outil de suivi, récapitulatif des étapes |

La copie à rendre (`ECF_TPDeveloppeurWebEtWebMobile_copiearendre_TOURE_Lassana.docx`) est déposée séparément, comme demandé par le sujet : elle n'est pas versionnée dans ce dépôt (voir `.gitignore`).

Suivi de projet (Notion) : https://app.notion.com/p/8fa7c87c8de341fb96676bb516139c9b

## Structure du dépôt

```
.
├── app/
│   ├── config/         routes.php, database.php
│   ├── controllers/    MenuController, AuthController, PasswordController, AccountController, OrderController, MyOrdersController, HomeController, AdminOrderController, AdminMenuController, AdminPlatController, AdminHoursController, AdminReviewController, AdminEmployeeController, AdminStatsController
│   ├── data/           site.php, hours.php (horaires de repli si la base est indisponible), communes.php
│   ├── models/         Model (base), MenuModel, PlatModel, MenuImageModel, ReferenceModel, UserModel, PasswordResetModel, OrderModel, ReviewModel, OrderAdminModel, MenuAdminModel, PlatAdminModel, HoraireModel, EmployeeModel
│   ├── services/       Session, Auth, Guard, Csrf, PasswordPolicy, RegistrationValidator, Mailer, MenuFilters, MenuPresenter, Geocoder, Distance, DeliveryQuote, OrderPricing, OrderValidator, OrderTimeline, ReviewValidator, OrderFlow, ImageUploader, MenuFormValidator, PlatFormValidator, HoursValidator, EmployeeValidator, MongoStore, OrderStatsSync, StatsQuery
│   ├── views/          layouts, partials, pages
│   ├── bootstrap.php   chargement commun (site et API)
│   └── helpers.php
├── bin/                sync-stats.php (resynchronisation MySQL vers MongoDB)
├── database/           schema.sql, seed.sql
├── docker/php/         Dockerfile, vhost Apache, entrypoint.sh (port $PORT dynamique)
├── docs/               livrables ECF (manuel, charte graphique, documentations technique et projet)
├── public/             racine web (index.php, api/menus.php, assets)
├── docker-compose.yml
├── render.yaml          build du conteneur pour le déploiement Render
└── .env.example        modèle des variables d'environnement
```

## Crédits

Les photos des menus et du bandeau d'accueil viennent de Pexels (licence libre). Les auteurs sont listés dans `public/assets/img/CREDITS.md`.

## Sécurité

- `.env` n'est jamais versionné (`.gitignore`) : seul `.env.example` l'est. Changer tous les mots de passe avant tout déploiement.
- Le serveur Apache ne publie que le dossier `public/`.
- Mots de passe hashés avec `password_hash`, requêtes SQL préparées, ports Docker liés à `127.0.0.1`.
