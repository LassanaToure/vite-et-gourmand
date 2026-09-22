<?php
declare(strict_types=1);

final class HomeController
{
    public static function index(array $params, array $query): array
    {
        $reviews = array_map(static function (array $review): array {
            $initial = mb_strtoupper(mb_substr((string) $review['nom'], 0, 1));

            return [
                'note' => (int) $review['note'],
                'texte' => $review['description'],
                'auteur' => trim($review['prenom'] . ($initial === '' ? '' : ' ' . $initial . '.')),
                'depuis' => (int) $review['depuis'],
            ];
        }, (new ReviewModel())->published(3));

        return ['vars' => ['reviews' => $reviews]];
    }
}
