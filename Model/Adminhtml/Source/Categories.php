<?php

namespace Openpay\Payment\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\CategoryRepository;

/**
 * Class Categories
 */
class Categories implements ArrayInterface
{
    /** @var Category */
    protected $_categoryHelper;

    /** @var CategoryRepository */
    protected $categoryRepository;

    /**
     * categories constructor
     *
     * @param Category           $catalogCategory
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        Category $catalogCategory,
        CategoryRepository $categoryRepository
    ) {
        $this->_categoryHelper = $catalogCategory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Return categories helper
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    /**
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {

        $categories = $this->getStoreCategories(true, false, true);
        $categoryList = $this->renderCategories($categories);
        return $categoryList;
    }

    /**
     * @return array
     */
    public function renderCategories($_categories)
    {
        foreach ($_categories as $category) {
            $i = 0;
            $this->categoryList[$category->getEntityId()] = __($category->getName());   // Main categories
            $list = $this->renderSubCat($category, $i);
        }

        return $this->categoryList;
    }

    /**
     * @return array
     */
    public function renderSubCat($cat, $j)
    {
        $categoryObj = $this->categoryRepository->get($cat->getId());
        $level = $categoryObj->getLevel();
        $arrow = str_repeat("---", $level-1);
        $subcategories = $categoryObj->getChildrenCategories();

        foreach ($subcategories as $subcategory) {
            $this->categoryList[$subcategory->getEntityId()] = __($arrow.$subcategory->getName());

            if ($subcategory->hasChildren()) {

                $this->renderSubCat($subcategory, $j);

            }
        }
        return $this->categoryList;
    }
}
