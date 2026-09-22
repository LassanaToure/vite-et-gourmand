SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS
    password_reset,
    avis,
    commande_suivi,
    commande,
    plat_allergene,
    menu_plat,
    menu_image,
    plat,
    menu,
    utilisateur,
    horaire,
    allergene,
    theme,
    regime,
    role;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE role (
    role_id INT NOT NULL AUTO_INCREMENT,
    libelle VARCHAR(50) NOT NULL,
    PRIMARY KEY (role_id),
    UNIQUE KEY uq_role_libelle (libelle)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE regime (
    regime_id INT NOT NULL AUTO_INCREMENT,
    libelle VARCHAR(50) NOT NULL,
    PRIMARY KEY (regime_id),
    UNIQUE KEY uq_regime_libelle (libelle)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE theme (
    theme_id INT NOT NULL AUTO_INCREMENT,
    libelle VARCHAR(50) NOT NULL,
    PRIMARY KEY (theme_id),
    UNIQUE KEY uq_theme_libelle (libelle)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE allergene (
    allergene_id INT NOT NULL AUTO_INCREMENT,
    libelle VARCHAR(50) NOT NULL,
    PRIMARY KEY (allergene_id),
    UNIQUE KEY uq_allergene_libelle (libelle)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE horaire (
    horaire_id INT NOT NULL AUTO_INCREMENT,
    jour VARCHAR(50) NOT NULL,
    heure_ouverture TIME NULL,
    heure_fermeture TIME NULL,
    PRIMARY KEY (horaire_id),
    UNIQUE KEY uq_horaire_jour (jour),
    CONSTRAINT ck_horaire_plage CHECK (
        (heure_ouverture IS NULL AND heure_fermeture IS NULL)
        OR (heure_ouverture IS NOT NULL AND heure_fermeture IS NOT NULL AND heure_fermeture > heure_ouverture)
    )
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE utilisateur (
    utilisateur_id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NULL,
    prenom VARCHAR(50) NULL,
    telephone VARCHAR(20) NULL,
    ville VARCHAR(100) NULL,
    pays VARCHAR(50) NOT NULL DEFAULT 'France',
    adresse_postale VARCHAR(255) NULL,
    code_postal VARCHAR(10) NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    role_id INT NOT NULL,
    PRIMARY KEY (utilisateur_id),
    UNIQUE KEY uq_utilisateur_email (email),
    KEY idx_utilisateur_role (role_id),
    CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id)
        REFERENCES role (role_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE menu (
    menu_id INT NOT NULL AUTO_INCREMENT,
    titre VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    conditions TEXT NOT NULL,
    nombre_personne_minimum INT NOT NULL,
    prix_par_personne DECIMAL(10, 2) NOT NULL,
    quantite_restante INT NOT NULL DEFAULT 0,
    delai_minimum_jours INT NOT NULL DEFAULT 3,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    regime_id INT NOT NULL,
    theme_id INT NOT NULL,
    PRIMARY KEY (menu_id),
    KEY idx_menu_regime (regime_id),
    KEY idx_menu_theme (theme_id),
    KEY idx_menu_prix (prix_par_personne),
    CONSTRAINT ck_menu_minimum CHECK (nombre_personne_minimum > 0),
    CONSTRAINT ck_menu_prix CHECK (prix_par_personne >= 0),
    CONSTRAINT ck_menu_stock CHECK (quantite_restante >= 0),
    CONSTRAINT ck_menu_delai CHECK (delai_minimum_jours >= 0),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id)
        REFERENCES regime (regime_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_menu_theme FOREIGN KEY (theme_id)
        REFERENCES theme (theme_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE menu_image (
    image_id INT NOT NULL AUTO_INCREMENT,
    menu_id INT NOT NULL,
    chemin VARCHAR(255) NOT NULL,
    texte_alternatif VARCHAR(255) NOT NULL,
    ordre INT NOT NULL DEFAULT 0,
    PRIMARY KEY (image_id),
    KEY idx_menu_image_menu (menu_id, ordre),
    CONSTRAINT fk_menu_image_menu FOREIGN KEY (menu_id)
        REFERENCES menu (menu_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE plat (
    plat_id INT NOT NULL AUTO_INCREMENT,
    titre_plat VARCHAR(100) NOT NULL,
    categorie ENUM('entree', 'plat', 'dessert') NOT NULL,
    photo VARCHAR(255) NULL,
    PRIMARY KEY (plat_id),
    KEY idx_plat_categorie (categorie)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE menu_plat (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    KEY idx_menu_plat_plat (plat_id),
    CONSTRAINT fk_menu_plat_menu FOREIGN KEY (menu_id)
        REFERENCES menu (menu_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_menu_plat_plat FOREIGN KEY (plat_id)
        REFERENCES plat (plat_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE plat_allergene (
    plat_id INT NOT NULL,
    allergene_id INT NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    KEY idx_plat_allergene_allergene (allergene_id),
    CONSTRAINT fk_plat_allergene_plat FOREIGN KEY (plat_id)
        REFERENCES plat (plat_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_plat_allergene_allergene FOREIGN KEY (allergene_id)
        REFERENCES allergene (allergene_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE commande (
    commande_id INT NOT NULL AUTO_INCREMENT,
    numero_commande VARCHAR(50) NOT NULL,
    date_commande DATE NOT NULL,
    date_prestation DATE NOT NULL,
    heure_livraison TIME NOT NULL,
    adresse_prestation VARCHAR(255) NOT NULL,
    code_postal_prestation VARCHAR(10) NOT NULL,
    ville_prestation VARCHAR(100) NOT NULL,
    telephone_contact VARCHAR(20) NOT NULL,
    prix_menu DECIMAL(10, 2) NOT NULL,
    remise DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    nombre_personne INT NOT NULL,
    distance_km DECIMAL(6, 1) NOT NULL DEFAULT 0.0,
    distance_source VARCHAR(20) NOT NULL DEFAULT 'bordeaux',
    prix_livraison DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
    pret_materiel BOOLEAN NOT NULL DEFAULT FALSE,
    restitution_materiel BOOLEAN NOT NULL DEFAULT FALSE,
    motif_annulation TEXT NULL,
    mode_contact_annulation ENUM('gsm', 'mail') NULL,
    utilisateur_id INT NOT NULL,
    menu_id INT NOT NULL,
    PRIMARY KEY (commande_id),
    UNIQUE KEY uq_commande_numero (numero_commande),
    KEY idx_commande_utilisateur (utilisateur_id),
    KEY idx_commande_menu (menu_id),
    KEY idx_commande_statut (statut),
    KEY idx_commande_prestation (date_prestation),
    CONSTRAINT ck_commande_personnes CHECK (nombre_personne > 0),
    CONSTRAINT ck_commande_prix_menu CHECK (prix_menu >= 0),
    CONSTRAINT ck_commande_prix_livraison CHECK (prix_livraison >= 0),
    CONSTRAINT ck_commande_remise CHECK (remise >= 0),
    CONSTRAINT ck_commande_distance CHECK (distance_km >= 0),
    CONSTRAINT ck_commande_distance_source CHECK (distance_source IN ('bordeaux', 'ban', 'commune')),
    CONSTRAINT ck_commande_statut CHECK (statut IN (
        'en_attente', 'accepte', 'en_preparation', 'en_cours_livraison',
        'livre', 'attente_retour_materiel', 'terminee', 'annulee'
    )),
    CONSTRAINT ck_commande_materiel CHECK (restitution_materiel = FALSE OR pret_materiel = TRUE),
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_commande_menu FOREIGN KEY (menu_id)
        REFERENCES menu (menu_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE commande_suivi (
    suivi_id INT NOT NULL AUTO_INCREMENT,
    commande_id INT NOT NULL,
    statut VARCHAR(50) NOT NULL,
    commentaire VARCHAR(255) NULL,
    auteur_id INT NULL,
    date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (suivi_id),
    KEY idx_commande_suivi_commande (commande_id, date_modification),
    CONSTRAINT ck_commande_suivi_statut CHECK (statut IN (
        'en_attente', 'accepte', 'en_preparation', 'en_cours_livraison',
        'livre', 'attente_retour_materiel', 'terminee', 'annulee'
    )),
    CONSTRAINT fk_commande_suivi_commande FOREIGN KEY (commande_id)
        REFERENCES commande (commande_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_commande_suivi_auteur FOREIGN KEY (auteur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE avis (
    avis_id INT NOT NULL AUTO_INCREMENT,
    note TINYINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id INT NOT NULL,
    commande_id INT NOT NULL,
    PRIMARY KEY (avis_id),
    UNIQUE KEY uq_avis_commande (commande_id),
    KEY idx_avis_utilisateur (utilisateur_id),
    KEY idx_avis_statut (statut),
    CONSTRAINT ck_avis_note CHECK (note BETWEEN 1 AND 5),
    CONSTRAINT ck_avis_statut CHECK (statut IN ('en_attente', 'valide', 'refuse')),
    CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_avis_commande FOREIGN KEY (commande_id)
        REFERENCES commande (commande_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE password_reset (
    reset_id INT NOT NULL AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expire_le DATETIME NOT NULL,
    utilise BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (reset_id),
    UNIQUE KEY uq_password_reset_token (token_hash),
    KEY idx_password_reset_utilisateur (utilisateur_id),
    CONSTRAINT fk_password_reset_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
