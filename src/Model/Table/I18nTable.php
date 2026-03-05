<?php

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * I18n table used by Translate behavior (EavStrategy).
 * Prevents Auto-Table fallback for I18n and translation aliases.
 */
class I18nTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('i18n');
    }
}
