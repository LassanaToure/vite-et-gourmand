<?php
declare(strict_types=1);

return [
    'forbidden' => [
        'status' => 403,
        'heading' => 'Accès refusé',
        'message' => 'Vous n\'avez pas les droits nécessaires pour consulter cette page.',
    ],
    'csrf' => [
        'status' => 403,
        'heading' => 'Session expirée',
        'message' => 'Votre session a expiré ou le formulaire n\'est plus valide. Revenez à la page précédente, rechargez-la puis réessayez.',
    ],
    'server' => [
        'status' => 500,
        'heading' => 'Service temporairement indisponible',
        'message' => 'Une erreur est survenue. Merci de réessayer dans quelques instants.',
    ],
];
