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

namespace Pim\Repositories;

use Espo\ORM\Entity;

class ProductAsset extends \Espo\Core\Templates\Repositories\Relationship
{
    protected function afterSave(Entity $entity, array $options = [])
    {
        parent::afterSave($entity, $options);

        if ($entity->isAttributeChanged('isMainImage') && !empty($entity->get('isMainImage'))) {
            foreach ($this->where(['isMainImage' => true, 'productId' => $entity->get('productId'), 'id!=' => $entity->get('id')])->find() as $productAsset) {
                $productAsset->set('isMainImage', false);
                $this->getEntityManager()->saveEntity($productAsset);
            }
        }
    }

    public function updateSortOrder(array $ids): void
    {
        foreach ($ids as $k => $id) {
            $id = $this->getPDO()->quote((string)$id);
            $sortOrder = (int)$k * 10;
            $this->getPDO()->exec("UPDATE `product_asset` SET sorting=$sortOrder WHERE id=$id");
        }
    }
}
