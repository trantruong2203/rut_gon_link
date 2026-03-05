<?php

declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\FormHelper as BaseFormHelper;

/**
 * App FormHelper - adds input() alias for CakePHP 4 compatibility.
 * CakePHP 4 uses control() instead of input(); this provides backward compatibility.
 */
class AppFormHelper extends BaseFormHelper
{
    /**
     * Alias for control() - CakePHP 3 compatibility.
     *
     * @param string $fieldName Field name
     * @param array<string, mixed> $options Options for the input
     * @return string HTML form control
     */
    public function input(string $fieldName, array $options = []): string
    {
        return $this->control($fieldName, $options);
    }

    /**
     * Alias for setTemplates() - CakePHP 3 compatibility.
     *
     * @param array<string, string> $templates Templates to add
     * @return $this
     */
    public function templates(array $templates)
    {
        return $this->setTemplates($templates);
    }
}
