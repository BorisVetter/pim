<?php

declare(strict_types=1);

namespace Pim\Repositories;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\ORM\Entity;
use Espo\Core\Utils\Util;
use Espo\Core\Templates\Repositories\Base;
use Espo\ORM\EntityCollection;

/**
 * Class Product
 *
 * @author r.ratsun <r.ratsun@gmail.com>
 */
class Product extends Base
{
    /**
     * @return array
     */
    public function getInputLanguageList(): array
    {
        return $this->getConfig()->get('inputLanguageList', []);
    }

    /**
     * @param string $productId
     *
     * @return array
     */
    public function getChannelRelationData(string $productId): array
    {
        $data = $this
            ->getEntityManager()
            ->nativeQuery(
                "SELECT channel_id as channelId, is_active AS isActive, from_category_tree as isFromCategoryTree FROM product_channel WHERE product_id='{$productId}' AND deleted=0"
            )
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $result[$row['channelId']] = $row;
        }

        return $result;
    }

    /**
     * @param array $productsIds
     * @param array $categoriesIds
     *
     * @return array
     */
    public function getProductCategoryLinkData(array $productsIds, array $categoriesIds): array
    {
        $productsIds = implode("','", $productsIds);
        $categoriesIds = implode("','", $categoriesIds);

        return $this
            ->getEntityManager()
            ->nativeQuery(
                "SELECT * FROM product_category WHERE product_id IN ('$productsIds') AND category_id IN ('$categoriesIds') AND deleted=0"
            )
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $productId
     * @param string $categoryId
     * @param int    $sorting
     */
    public function updateProductCategorySortOrder(string $productId, string $categoryId, int $sorting): void
    {
        // get link data
        $linkData = $this->getProductCategoryLinkData([$productId], [$categoryId]);

        // get max
        $max = (int)$linkData[0]['sorting'];

        // update current
        $this
            ->getEntityManager()
            ->nativeQuery("UPDATE product_category SET sorting='$sorting' WHERE category_id='$categoryId' AND product_id='$productId' AND deleted=0");

        // get next records
        $ids = $this
            ->getEntityManager()
            ->nativeQuery("SELECT id FROM product_category WHERE sorting>='$sorting' AND category_id='$categoryId' AND deleted=0 AND product_id!='$productId' ORDER BY sorting")
            ->fetchAll(\PDO::FETCH_COLUMN);

        // update next records
        if (!empty($ids)) {
            // prepare sql
            $sql = '';
            foreach ($ids as $id) {
                // increase max
                $max = $max + 10;

                // prepare sql
                $sql .= "UPDATE product_category SET sorting='$max' WHERE id='$id';";
            }

            // execute sql
            $this->getEntityManager()->nativeQuery($sql);
        }
    }

    /**
     * @param Entity $product
     * @param Entity $category
     *
     * @return bool
     * @throws BadRequest
     */
    public function isCategoryFromCatalogTrees(Entity $product, Entity $category): bool
    {
        if (!empty($catalog = $product->get('catalog'))) {
            /** @var array $treesIds */
            $treesIds = array_column($catalog->get('categories')->toArray(), 'id');

            /** @var string $rootId */
            $rootId = $category->getRoot()->get('id');

            if (!in_array($rootId, $treesIds)) {
                throw new BadRequest($this->translate("You should use categories from those trees that linked with product catalog", 'exceptions', 'Product'));
            }
        }

        return true;
    }

    /**
     * @param Entity $product
     * @param Entity $catalog
     *
     * @return bool
     * @throws BadRequest
     */
    public function isProductCategoriesInSelectedCatalog(Entity $product, Entity $catalog): bool
    {
        /** @var array $catalogTreesIds */
        $catalogTreesIds = array_column($catalog->get('categories')->toArray(), 'id');

        /** @var EntityCollection $categories */
        $categories = $product->get('categories');
        if ($categories->count() > 0) {
            foreach ($categories as $category) {
                if (!in_array($category->getRoot()->get('id'), $catalogTreesIds)) {
                    throw new BadRequest("You should use categories from those trees that linked with product catalog");
                }
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $this->addDependency('language');
    }

    /**
     * @inheritdoc
     */
    protected function afterSave(Entity $entity, array $options = [])
    {
        // save attributes
        $this->saveAttributes($entity);

        // parent action
        parent::afterSave($entity, $options);
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     * @throws Error
     */
    protected function saveAttributes(Entity $product): bool
    {
        if (!empty($product->productAttribute)) {
            $data = $this
                ->getEntityManager()
                ->getRepository('ProductAttributeValue')
                ->where(
                    [
                        'productId'   => $product->get('id'),
                        'attributeId' => array_keys($product->productAttribute),
                        'scope'       => 'Global'
                    ]
                )
                ->find();

            // prepare exists
            $exists = [];
            if (count($data) > 0) {
                foreach ($data as $v) {
                    $exists[$v->get('attributeId')] = $v;
                }
            }

            foreach ($product->productAttribute as $attributeId => $values) {
                if (isset($exists[$attributeId])) {
                    $entity = $exists[$attributeId];
                } else {
                    $entity = $this->getEntityManager()->getEntity('ProductAttributeValue');
                    $entity->set('productId', $product->get('id'));
                    $entity->set('attributeId', $attributeId);
                    $entity->set('scope', 'Global');
                }

                foreach ($values['locales'] as $locale => $value) {
                    if ($locale == 'default') {
                        $entity->set('value', $value);
                    } else {
                        // prepare locale
                        $locale = Util::toCamelCase(strtolower($locale), '_', true);
                        $entity->set("value$locale", $value);
                    }
                }

                if (isset($values['data']) && !empty($values['data'])) {
                    foreach ($values['data'] as $field => $item) {
                        $entity->set($field, $item);
                    }
                }

                $this->getEntityManager()->saveEntity($entity);
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @param string $label
     * @param string $scope
     *
     * @return string
     */
    protected function translate(string $key, string $label, $scope = ''): string
    {
        return $this->getInjection('language')->translate($key, $label, $scope);
    }
}