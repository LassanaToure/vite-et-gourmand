<?php
declare(strict_types=1);

return [
    'en_attente' => ['label' => 'En attente', 'event' => 'Commande reçue, en attente de validation', 'group' => 'en_cours', 'tone' => 'pending'],
    'accepte' => ['label' => 'Acceptée', 'event' => 'Commande acceptée par notre équipe', 'group' => 'en_cours', 'tone' => 'progress'],
    'en_preparation' => ['label' => 'En préparation', 'event' => 'Votre commande est en préparation', 'group' => 'en_cours', 'tone' => 'progress'],
    'en_cours_livraison' => ['label' => 'En livraison', 'event' => 'Votre commande est en cours de livraison', 'group' => 'en_cours', 'tone' => 'progress'],
    'livre' => ['label' => 'Livrée', 'event' => 'Commande livrée', 'group' => 'en_cours', 'tone' => 'progress'],
    'attente_retour_materiel' => ['label' => 'Retour du matériel', 'event' => 'En attente du retour du matériel', 'group' => 'en_cours', 'tone' => 'alert'],
    'terminee' => ['label' => 'Terminée', 'event' => 'Commande terminée', 'group' => 'terminee', 'tone' => 'done'],
    'annulee' => ['label' => 'Annulée', 'event' => 'Commande annulée', 'group' => 'terminee', 'tone' => 'cancelled'],
];
