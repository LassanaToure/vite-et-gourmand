<?php
declare(strict_types=1);

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;

final class MongoStore
{
    public const FACTS = 'commande_faits';

    private static ?Manager $manager = null;

    public static function upsert(array $document): void
    {
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $document['_id']], ['$set' => $document], ['upsert' => true]);
        self::write($bulk);
    }

    public static function replaceAll(array $documents): int
    {
        $bulk = new BulkWrite();
        $ids = [];
        foreach ($documents as $document) {
            $ids[] = $document['_id'];
            $bulk->update(['_id' => $document['_id']], ['$set' => $document], ['upsert' => true]);
        }
        $bulk->delete(['_id' => ['$nin' => $ids]]);
        self::write($bulk);

        return count($documents);
    }

    public static function aggregate(array $pipeline): array
    {
        $command = new Command([
            'aggregate' => self::FACTS,
            'pipeline' => array_values($pipeline),
            'cursor' => new stdClass(),
        ]);
        $cursor = self::manager()->executeCommand(self::database(), $command);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return $cursor->toArray();
    }

    private static function write(BulkWrite $bulk): void
    {
        self::manager()->executeBulkWrite(self::database() . '.' . self::FACTS, $bulk);
    }

    private static function database(): string
    {
        return getenv('MONGO_DB') ?: 'vite_gourmand_stats';
    }

    private static function manager(): Manager
    {
        if (self::$manager !== null) {
            return self::$manager;
        }
        if (!extension_loaded('mongodb')) {
            throw new RuntimeException('Extension MongoDB absente.');
        }
        $uri = getenv('MONGODB_URI') ?: getenv('MONGO_URI');
        if ($uri === false || $uri === '') {
            throw new RuntimeException('Variable d\'environnement manquante : MONGODB_URI ou MONGO_URI');
        }

        return self::$manager = new Manager($uri, [
            'serverSelectionTimeoutMS' => 2500,
            'connectTimeoutMS' => 2500,
            'socketTimeoutMS' => 8000,
        ]);
    }
}
