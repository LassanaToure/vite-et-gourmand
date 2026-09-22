SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE password_reset;
TRUNCATE TABLE avis;
TRUNCATE TABLE commande_suivi;
TRUNCATE TABLE commande;
TRUNCATE TABLE plat_allergene;
TRUNCATE TABLE menu_plat;
TRUNCATE TABLE menu_image;
TRUNCATE TABLE plat;
TRUNCATE TABLE menu;
TRUNCATE TABLE utilisateur;
TRUNCATE TABLE horaire;
TRUNCATE TABLE allergene;
TRUNCATE TABLE theme;
TRUNCATE TABLE regime;
TRUNCATE TABLE role;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO role (role_id, libelle) VALUES
    (1, 'utilisateur'),
    (2, 'employe'),
    (3, 'administrateur');

INSERT INTO theme (theme_id, libelle) VALUES
    (1, 'Noël'),
    (2, 'Pâques'),
    (3, 'Classique'),
    (4, 'Événement');

INSERT INTO regime (regime_id, libelle) VALUES
    (1, 'Classique'),
    (2, 'Végétarien'),
    (3, 'Vegan'),
    (4, 'Sans gluten');

INSERT INTO allergene (allergene_id, libelle) VALUES
    (1, 'Gluten'),
    (2, 'Crustacés'),
    (3, 'Œufs'),
    (4, 'Poisson'),
    (5, 'Arachides'),
    (6, 'Soja'),
    (7, 'Lait'),
    (8, 'Fruits à coque'),
    (9, 'Céleri'),
    (10, 'Moutarde'),
    (11, 'Sésame'),
    (12, 'Sulfites'),
    (13, 'Lupin'),
    (14, 'Mollusques');

INSERT INTO horaire (horaire_id, jour, heure_ouverture, heure_fermeture) VALUES
    (1, 'Lundi', '08:00:00', '18:00:00'),
    (2, 'Mardi', '08:00:00', '18:00:00'),
    (3, 'Mercredi', '08:00:00', '18:00:00'),
    (4, 'Jeudi', '08:00:00', '18:00:00'),
    (5, 'Vendredi', '08:00:00', '18:00:00'),
    (6, 'Samedi', '09:00:00', '17:00:00'),
    (7, 'Dimanche', NULL, NULL);

INSERT INTO utilisateur (utilisateur_id, email, password, nom, prenom, telephone, ville, pays, adresse_postale, code_postal, actif, date_creation, role_id) VALUES
    (1, 'admin@vitegourmand.fr', '$2y$12$0gBlpEWuoATeBg4bmeSnDunHsLFdg2VV3MCpWiU70T9NBhnOb1lLK', 'Da Silva', 'José', '05 56 12 34 56', 'Bordeaux', 'France', '14 rue du Palais Gallien', '33000', TRUE, '2026-01-05 09:00:00', 3),
    (2, 'employe@vitegourmand.fr', '$2y$12$9JePYGAN9xLQY715KUAHDOnCAg/ggQbWHP8a22ft8UOMK/8Bh285O', 'Durand', 'Camille', '06 44 55 66 77', 'Bordeaux', 'France', '22 rue Sainte-Catherine', '33000', TRUE, '2026-01-06 09:30:00', 2),
    (3, 'sophie.marchand@example.fr', '$2y$12$z5KhCq6PcR704dfV3qEqXOLvONReuRJnhD5A9w.YWvRiUExUJeJlq', 'Marchand', 'Sophie', '06 12 34 56 78', 'Bordeaux', 'France', '12 cours de l''Intendance', '33000', TRUE, '2026-02-20 18:15:00', 1),
    (4, 'marc.delacroix@example.fr', '$2y$12$8VTSS8NmwIxkaWhhZS92Vu6pFocRXVbS42KeQBwofWzVGStnBUCxi', 'Delacroix', 'Marc', '06 98 76 54 32', 'Mérignac', 'France', '8 avenue de la Libération', '33700', TRUE, '2025-11-28 20:05:00', 1);

INSERT INTO menu (menu_id, titre, description, conditions, nombre_personne_minimum, prix_par_personne, quantite_restante, delai_minimum_jours, regime_id, theme_id) VALUES
    (1, 'Menu Mariage Prestige', 'Un menu pensé pour le plus beau jour : cocktail apéritif, entrée fine, plat signature, fromages de nos producteurs et pièce montée. Service à l''assiette ou buffet, selon votre réception.', 'Commande à passer au minimum 14 jours avant l''événement. Conserver au frais entre 0 et 4 °C et consommer le jour même.', 30, 78.00, 6, 14, 1, 4),
    (2, 'Menu Noël Tradition', 'Les classiques de la table de fête revisités : saumon fumé maison, chapon rôti aux marrons et bûche artisanale. Livré prêt à réchauffer avec les instructions de service.', 'Commande à passer au minimum 10 jours avant le 24 ou le 25 décembre. Réchauffage conseillé à 160 °C.', 6, 54.00, 12, 10, 1, 1),
    (3, 'Menu Pâques Printanier', 'Gigot d''agneau confit sept heures, légumes primeurs et tarte au citron meringuée : la fraîcheur du printemps dans l''assiette.', 'Commande à passer au minimum 7 jours avant l''événement.', 4, 42.00, 15, 7, 1, 2),
    (4, 'Menu Végétarien de Saison', 'Des produits de saison sélectionnés au marché des Capucins : tarte fine, risotto crémeux et dessert aux fruits rouges.', 'Commande à passer au minimum 5 jours avant l''événement.', 8, 36.00, 20, 5, 2, 3),
    (5, 'Menu Vegan Fraîcheur', 'Houmous maison, curry de légumes au lait de coco et mousse au chocolat noir à l''aquafaba, pour un événement responsable.', 'Commande à passer au minimum 5 jours avant l''événement.', 8, 34.00, 18, 5, 3, 4);

INSERT INTO menu_image (menu_id, chemin, texte_alternatif, ordre) VALUES
    (1, 'img/menus/mariage-prestige-1.jpg', 'Salle de réception de mariage avec buffet chaud, drapés dorés et compositions de fleurs orangées', 1),
    (1, 'img/menus/mariage-prestige-2.jpg', 'Filet de bœuf nappé de réduction de vin rouge sur un risotto crémeux, brin de romarin', 2),
    (1, 'img/menus/mariage-prestige-3.jpg', 'Verrines apéritives et bouchées croustillantes présentées sur un plateau ajouré', 3),
    (2, 'img/menus/noel-tradition-1.jpg', 'Volaille rôtie aux citrons, carottes et grenade sur une table de Noël décorée de sapin et de pommes de pin', 1),
    (2, 'img/menus/noel-tradition-2.jpg', 'Planche de saumon fumé sur une table de fête éclairée aux bougies', 2),
    (2, 'img/menus/noel-tradition-3.jpg', 'Bûche de Noël au chocolat sur une planche en bois, guirlande lumineuse en arrière-plan', 3),
    (3, 'img/menus/paques-printanier-1.jpg', 'Côtelettes d''agneau en croûte d''herbes sur un plateau de bois rustique', 1),
    (3, 'img/menus/paques-printanier-2.jpg', 'Asperges vertes rôties avec tomates cerises et citron sur papier de cuisson', 2),
    (3, 'img/menus/paques-printanier-3.jpg', 'Tartelettes au citron meringuées aux pointes dorées', 3),
    (4, 'img/menus/vegetarien-saison-1.jpg', 'Tarte aux tomates anciennes et herbes fraîches', 1),
    (4, 'img/menus/vegetarien-saison-2.jpg', 'Risotto aux champignons et basilic sur une assiette noire', 2),
    (4, 'img/menus/vegetarien-saison-3.jpg', 'Panna cotta nappée de coulis, framboises, myrtilles et mangue', 3),
    (5, 'img/menus/vegan-fraicheur-1.jpg', 'Curry rouge de légumes et tofu au basilic dans un bol noir', 1),
    (5, 'img/menus/vegan-fraicheur-2.jpg', 'Crudités de saison et trois dips accompagnés de pain croustillant', 2),
    (5, 'img/menus/vegan-fraicheur-3.jpg', 'Verrines de mousse au chocolat garnies de grenade', 3);

INSERT INTO plat (plat_id, titre_plat, categorie) VALUES
    (1, 'Foie gras mi-cuit, chutney de figues', 'entree'),
    (2, 'Saint-Jacques snackées, crème de topinambour', 'entree'),
    (3, 'Saumon fumé maison, blinis et crème citronnée', 'entree'),
    (4, 'Velouté de petits pois et menthe', 'entree'),
    (5, 'Tarte fine tomates anciennes et chèvre', 'entree'),
    (6, 'Houmous et crudités croquantes', 'entree'),
    (7, 'Filet de bœuf, jus au vin de Bordeaux', 'plat'),
    (8, 'Chapon rôti, farce aux marrons', 'plat'),
    (9, 'Gigot d''agneau confit, jus au thym', 'plat'),
    (10, 'Risotto aux champignons et parmesan', 'plat'),
    (11, 'Curry de légumes au lait de coco', 'plat'),
    (12, 'Gratin dauphinois', 'plat'),
    (13, 'Légumes primeurs rôtis', 'plat'),
    (14, 'Purée de céleri', 'plat'),
    (15, 'Pièce montée aux choux craquants', 'dessert'),
    (16, 'Bûche artisanale chocolat et noisette', 'dessert'),
    (17, 'Tarte au citron meringuée', 'dessert'),
    (18, 'Panna cotta aux fruits rouges', 'dessert'),
    (19, 'Mousse au chocolat noir à l''aquafaba', 'dessert');

INSERT INTO menu_plat (menu_id, plat_id) VALUES
    (1, 1), (1, 2), (1, 7), (1, 12), (1, 15),
    (2, 3), (2, 8), (2, 12), (2, 14), (2, 16),
    (3, 4), (3, 9), (3, 13), (3, 17),
    (4, 4), (4, 5), (4, 10), (4, 13), (4, 18),
    (5, 6), (5, 11), (5, 13), (5, 19);

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
    (1, 1),
    (2, 7), (2, 14),
    (3, 1), (3, 4), (3, 7),
    (4, 7),
    (5, 1), (5, 7),
    (6, 11),
    (7, 12),
    (8, 8),
    (10, 7),
    (12, 7),
    (14, 7), (14, 9),
    (15, 1), (15, 3), (15, 7),
    (16, 1), (16, 3), (16, 7), (16, 8),
    (17, 1), (17, 3), (17, 7),
    (18, 7);

INSERT INTO commande (commande_id, numero_commande, date_commande, date_prestation, heure_livraison, adresse_prestation, code_postal_prestation, ville_prestation, telephone_contact, prix_menu, remise, nombre_personne, distance_km, distance_source, prix_livraison, statut, pret_materiel, restitution_materiel, motif_annulation, mode_contact_annulation, utilisateur_id, menu_id) VALUES
    (1, 'VG-2025-0001', '2025-12-05', '2025-12-24', '19:30:00', '8 avenue de la Libération', '33700', 'Mérignac', '0698765432', 432.00, 0.00, 8, 6.0, 'ban', 8.54, 'terminee', FALSE, FALSE, NULL, NULL, 4, 2),
    (2, 'VG-2026-0001', '2026-03-02', '2026-06-13', '12:00:00', '3 rue Vital-Carles', '33000', 'Bordeaux', '0612345678', 2808.00, 312.00, 40, 0.0, 'bordeaux', 0.00, 'terminee', TRUE, TRUE, NULL, NULL, 3, 1),
    (3, 'VG-2026-0002', '2026-03-20', '2026-04-05', '12:30:00', '12 cours de l''Intendance', '33000', 'Bordeaux', '0612345678', 378.00, 42.00, 10, 0.0, 'bordeaux', 0.00, 'terminee', FALSE, FALSE, NULL, NULL, 3, 3),
    (4, 'VG-2026-0003', '2026-04-28', '2026-05-16', '12:00:00', '8 avenue de la Libération', '33700', 'Mérignac', '0698765432', 272.00, 0.00, 8, 6.0, 'ban', 8.54, 'terminee', FALSE, FALSE, NULL, NULL, 4, 5),
    (5, 'VG-2026-0004', '2026-08-25', '2026-09-12', '12:00:00', '12 cours de l''Intendance', '33000', 'Bordeaux', '0612345678', 648.00, 72.00, 20, 0.0, 'bordeaux', 0.00, 'attente_retour_materiel', TRUE, FALSE, NULL, NULL, 3, 4),
    (6, 'VG-2026-0005', '2026-09-15', '2026-09-26', '12:00:00', '8 avenue de la Libération', '33700', 'Mérignac', '0698765432', 288.00, 0.00, 8, 6.0, 'ban', 8.54, 'en_preparation', FALSE, FALSE, NULL, NULL, 4, 4),
    (7, 'VG-2026-0006', '2026-09-19', '2026-11-07', '12:00:00', '12 cours de l''Intendance', '33000', 'Bordeaux', '0612345678', 408.00, 0.00, 12, 0.0, 'bordeaux', 0.00, 'en_attente', FALSE, FALSE, NULL, NULL, 3, 5),
    (8, 'VG-2026-0007', '2026-09-10', '2026-12-24', '19:00:00', '8 avenue de la Libération', '33700', 'Mérignac', '0698765432', 583.20, 64.80, 12, 6.0, 'ban', 8.54, 'annulee', FALSE, FALSE, 'Date indisponible pour cause de fermeture exceptionnelle. Client contacté par téléphone, report proposé.', 'gsm', 4, 2),
    (9, 'VG-2026-0008', '2026-06-20', '2026-07-11', '12:00:00', '12 cours de l''Intendance', '33000', 'Bordeaux', '0612345678', 168.00, 0.00, 4, 0.0, 'bordeaux', 0.00, 'terminee', FALSE, FALSE, NULL, NULL, 3, 3);

INSERT INTO commande_suivi (commande_id, statut, date_modification) VALUES
    (1, 'en_attente', '2025-12-05 14:20:00'),
    (1, 'accepte', '2025-12-06 09:10:00'),
    (1, 'en_preparation', '2025-12-23 08:00:00'),
    (1, 'en_cours_livraison', '2025-12-24 16:30:00'),
    (1, 'livre', '2025-12-24 17:15:00'),
    (1, 'terminee', '2025-12-24 17:20:00'),
    (2, 'en_attente', '2026-03-02 10:12:00'),
    (2, 'accepte', '2026-03-03 09:05:00'),
    (2, 'en_preparation', '2026-06-11 08:00:00'),
    (2, 'en_cours_livraison', '2026-06-13 09:30:00'),
    (2, 'livre', '2026-06-13 11:45:00'),
    (2, 'attente_retour_materiel', '2026-06-13 11:46:00'),
    (2, 'terminee', '2026-06-19 16:20:00'),
    (3, 'en_attente', '2026-03-20 18:05:00'),
    (3, 'accepte', '2026-03-21 10:00:00'),
    (3, 'en_preparation', '2026-04-04 08:30:00'),
    (3, 'en_cours_livraison', '2026-04-05 10:45:00'),
    (3, 'livre', '2026-04-05 11:30:00'),
    (3, 'terminee', '2026-04-05 11:35:00'),
    (4, 'en_attente', '2026-04-28 19:40:00'),
    (4, 'accepte', '2026-04-29 09:15:00'),
    (4, 'en_preparation', '2026-05-15 08:00:00'),
    (4, 'en_cours_livraison', '2026-05-16 10:30:00'),
    (4, 'livre', '2026-05-16 11:20:00'),
    (4, 'terminee', '2026-05-16 11:25:00'),
    (5, 'en_attente', '2026-08-25 17:30:00'),
    (5, 'accepte', '2026-08-26 09:00:00'),
    (5, 'en_preparation', '2026-09-11 08:00:00'),
    (5, 'en_cours_livraison', '2026-09-12 10:30:00'),
    (5, 'livre', '2026-09-12 11:40:00'),
    (5, 'attente_retour_materiel', '2026-09-12 11:41:00'),
    (6, 'en_attente', '2026-09-15 11:02:00'),
    (6, 'accepte', '2026-09-16 09:30:00'),
    (6, 'en_preparation', '2026-09-21 07:30:00'),
    (7, 'en_attente', '2026-09-19 21:14:00'),
    (8, 'en_attente', '2026-09-10 16:40:00'),
    (8, 'annulee', '2026-09-12 10:15:00'),
    (9, 'en_attente', '2026-06-20 11:10:00'),
    (9, 'accepte', '2026-06-21 09:00:00'),
    (9, 'en_preparation', '2026-07-10 08:00:00'),
    (9, 'en_cours_livraison', '2026-07-11 10:45:00'),
    (9, 'livre', '2026-07-11 11:30:00'),
    (9, 'terminee', '2026-07-11 11:35:00');

INSERT INTO avis (avis_id, note, description, statut, date_creation, utilisateur_id, commande_id) VALUES
    (1, 5, 'Le repas de Noël en famille a été sublimé par leur menu. La bûche artisanale était à pleurer de bonheur.', 'valide', '2025-12-27 10:12:00', 4, 1),
    (2, 5, 'Un mariage inoubliable grâce à Julie et José. Chaque plat était une œuvre d''art, nos invités en parlent encore.', 'valide', '2026-06-25 18:40:00', 3, 2),
    (3, 4, 'Très bon repas de Pâques, l''agneau était fondant et la livraison ponctuelle. Un peu juste sur les desserts.', 'en_attente', '2026-04-08 09:15:00', 3, 3),
    (4, 2, 'Le site m''a paru difficile à utiliser et j''ai eu du mal à passer commande.', 'refuse', '2026-05-20 20:05:00', 4, 4);
