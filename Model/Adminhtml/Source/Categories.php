<?php

namespace Openpay\Payment\Model\Adminhtml\Source;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class Categories
 */
class Categories implements \Magento\Framework\Data\OptionSourceInterface
{
     /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var CategoryManagementInterface
     */
    private $categoryManagement;
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $objectConverter;

    /**
     * Categories constructor.
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param CategoryManagementInterface $categoryManagement
     * @param ExtensibleDataObjectConverter $objectConverter
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        CategoryManagementInterface $categoryManagement,
        ExtensibleDataObjectConverter $objectConverter
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->categoryManagement = $categoryManagement;
        $this->objectConverter = $objectConverter;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $data = [];
        $rootIds = $this->getRootIds();

        foreach ($rootIds as $rootCategory) {
            /** @var CategoryTreeInterface $tree */
            $tree = $this->categoryManagement->getTree($rootCategory, 6);
            $categoryArray = $this->objectConverter->toNestedArray($tree, [], CategoryTreeInterface::class);
            if (count($categoryArray)) {
                $this->getArray($data, $categoryArray["children_data"], 1);
            }
        }
        return $data;
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        $ids = [\Magento\Catalog\Model\Category::TREE_ROOT_ID];
        foreach ($this->storeManager->getGroups() as $store) {
            $ids[] = $store->getRootCategoryId();
        }
        return $ids;
    }

    public function getArray(&$data, $array, $level = 0)
    {
        foreach ($array as $category) {
            $arrow = str_repeat("-", $level);
            $data[] = ['value' => $category["id"], 'label' => __($arrow." ".$category["name"])];
            if ($category["children_data"]) {
                $this->getArray($data, $category["children_data"], $level+1);
            }
        }
    }
}