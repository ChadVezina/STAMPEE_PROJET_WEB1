<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;

final class CountryService
{
    public function listAll(): array
    {
        $stmt = DB::pdo()->query("SELECT iso2, name_fr, name_en FROM `Country` ORDER BY name_fr ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
