<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Repositories own all SQL for one table. Controllers never write queries.
 */
abstract class Repository
{
    protected Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Escape LIKE wildcards so user search terms are matched literally. */
    protected static function like(string $term): string
    {
        return '%' . addcslashes($term, '%_\\') . '%';
    }
}
