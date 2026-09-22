<?php
declare(strict_types=1);

final class OrderFlow
{
    private const NEXT = [
        'en_attente' => 'accepte',
        'accepte' => 'en_preparation',
        'en_preparation' => 'en_cours_livraison',
        'en_cours_livraison' => 'livre',
    ];

    private const ACTIONS = [
        'accepte' => 'Accepter la commande',
        'en_preparation' => 'Lancer la préparation',
        'en_cours_livraison' => 'Démarrer la livraison',
        'livre' => 'Marquer comme livrée',
        'attente_retour_materiel' => 'Matériel prêté : attendre le retour',
        'terminee' => 'Terminer la commande',
    ];

    private const CANCELLABLE = ['en_attente', 'accepte', 'en_preparation', 'en_cours_livraison'];

    public static function next(array $order): ?string
    {
        $status = $order['statut'];

        if (isset(self::NEXT[$status])) {
            return self::NEXT[$status];
        }
        if ($status === 'livre') {
            return (bool) $order['pret_materiel'] ? 'attente_retour_materiel' : 'terminee';
        }
        if ($status === 'attente_retour_materiel') {
            return 'terminee';
        }

        return null;
    }

    public static function actionLabel(string $status): string
    {
        return self::ACTIONS[$status] ?? 'Changer le statut';
    }

    public static function extra(array $order): ?string
    {
        return match ($order['statut']) {
            'en_cours_livraison' => 'lend',
            'attente_retour_materiel' => 'return',
            default => null,
        };
    }

    public static function canCancel(array $order): bool
    {
        return in_array($order['statut'], self::CANCELLABLE, true);
    }
}
