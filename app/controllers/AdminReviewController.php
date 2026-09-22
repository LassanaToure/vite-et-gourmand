<?php
declare(strict_types=1);

final class AdminReviewController
{
    public static function index(array $params, array $query): array
    {
        $reviews = (new ReviewModel())->listAll();
        $counts = ['en_attente' => 0, 'valide' => 0, 'refuse' => 0];
        foreach ($reviews as $review) {
            $counts[$review['statut']]++;
        }

        return ['vars' => ['reviews' => $reviews, 'counts' => $counts]];
    }

    public static function validate(array $params, array $query): ?array
    {
        return self::moderate($params, 'valide', 'L\'avis est publié sur la page d\'accueil.');
    }

    public static function refuse(array $params, array $query): ?array
    {
        return self::moderate($params, 'refuse', 'L\'avis est refusé et n\'est pas publié.');
    }

    private static function moderate(array $params, string $status, string $message): ?array
    {
        if (!is_post()) {
            return ['redirect' => '/admin/avis'];
        }
        if (!(new ReviewModel())->moderate((int) $params['id'], $status)) {
            return null;
        }

        Session::flash('success', $message);

        return ['redirect' => '/admin/avis'];
    }
}
