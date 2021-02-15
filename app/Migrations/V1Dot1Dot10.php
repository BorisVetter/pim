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
 */

declare(strict_types=1);

namespace Pim\Migrations;

use Espo\Core\Utils\Json;
use Treo\Core\Migration\Base;

/**
 * Class V1Dot1Dot10
 */
class V1Dot1Dot10 extends Base
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $sql = "
            SELECT note.id, note.data
            FROM note
            INNER JOIN product_attribute_value pav
                ON note.attribute_id = pav.id AND pav.deleted = 0
            INNER JOIN attribute a
                ON pav.attribute_id = a.id AND a.deleted = 0
            WHERE a.type IN ('enum', 'multiEnum') AND note.deleted = 0
        ";

        $notes = $this
            ->getPDO()
            ->query($sql)
            ->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($notes)) {
            $sql = "";

            foreach ($notes as $note) {
                $data = Json::decode($note['data'], true);

                if (isset($data['locale']) && !empty($data['locale'])) {
                    $sql .= "DELETE FROM note WHERE id = '{$note['id']}';";
                }
            }

            if (!empty($sql)) {
                $this->execute($sql);
            }
        }
    }

    /**
     * @param string $sql
     */
    protected function execute(string $sql)
    {
        try {
            $this->getPDO()->exec($sql);
        } catch (\Throwable $e) {
            // ignore all
        }
    }
}
