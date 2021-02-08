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

namespace Pim\Controllers;

use Espo\Core\Exceptions;
use Espo\Core\Exceptions\NotFound;
use Slim\Http\Request;

/**
 * Attribute controller
 */
class Attribute extends AbstractController
{

    /**
     * @ApiDescription(description="Get filters data for product entity")
     * @ApiMethod(type="GET")
     * @ApiRoute(name="/Markets/Attribute/filtersData")
     * @ApiReturn(sample="'json'")
     *
     * @param array   $params
     * @param array   $data
     * @param Request $request
     *
     * @return bool
     * @throws Exceptions\BadRequest
     * @throws Exceptions\Error
     * @throws Exceptions\Forbidden
     */
    public function actionGetFiltersData($params, $data, Request $request): array
    {
        if ($this->isReadAction($request, $params)) {
            return $this->getRecordService()->getFiltersData();
        }

        throw new Exceptions\Error();
    }

    /**
     * @inheritDoc
     */
    public function actionRead($params, $data, $request)
    {
        $result = parent::actionRead($params, $data, $request);

        return $this->getRecordService()->updateAttributeReadData($result);
    }
}
