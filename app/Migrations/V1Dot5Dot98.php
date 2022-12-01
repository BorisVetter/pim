<?php
/*
 * This file is part of AtroPIM.
 *
 * AtroPIM - Open Source PIM application.
 * Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 * Website: https://atropim.com
 *
 * AtroPIM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AtroPIM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AtroPIM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "AtroPIM" word.
 *
 * This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Pim\Migrations;

use Treo\Core\Migration\Base;

class V1Dot5Dot98 extends Base
{
    public function up(): void
    {
        $languages = [];
        if ($this->getConfig()->get('isMultilangActive', false)) {
            foreach ($this->getConfig()->get('inputLanguageList', []) as $language) {
                $languages[$language] = strtolower($language);
            }
        }

        $this->exec("ALTER TABLE attribute ADD tooltip VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`");

        foreach ($languages as $language) {
            $this->exec("ALTER TABLE attribute ADD tooltip_{$language} VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`");
        }
    }

    public function down(): void
    {
        $languages = [];
        if ($this->getConfig()->get('isMultilangActive', false)) {
            foreach ($this->getConfig()->get('inputLanguageList', []) as $language) {
                $languages[$language] = strtolower($language);
            }
        }

        $this->exec("ALTER TABLE attribute DROP COLUMN tooltip");

        foreach ($languages as $language) {
            $this->exec("ALTER TABLE attribute DROP COLUMN tooltip_{$language}");
        }
    }

    protected function exec(string $query): void
    {
        try {
            $this->getPDO()->exec($query);
        } catch (\Throwable $e) {
        }
    }

}
