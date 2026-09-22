<?php
declare(strict_types=1);

return [
    'name' => 'Vite & Gourmand',
    'tagline' => "L'art de recevoir depuis 25 ans.",
    'baseline' => 'Traiteur événementiel à Bordeaux.',
    'address' => ['14 rue du Palais Gallien', '33000 Bordeaux'],
    'phone' => '+33 5 56 12 34 56',
    'phone_href' => '+33556123456',
    'email' => 'contact@vitegourmand.fr',
    'nav' => [
        ['label' => 'Accueil', 'path' => '/'],
        ['label' => 'Nos menus', 'path' => '/menus'],
        ['label' => 'Contact', 'path' => '/contact'],
    ],
    'account_links' => [
        ['label' => 'Back-office', 'path' => '/admin/commandes', 'roles' => ['employe', 'administrateur']],
        ['label' => 'Mes commandes', 'path' => '/mes-commandes', 'roles' => ['utilisateur']],
        ['label' => 'Mon compte', 'path' => '/mon-compte'],
    ],
];
