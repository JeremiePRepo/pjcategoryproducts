<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PJCategoryProducts extends Module
{
    protected $config_form = false;
    private static array $localCache = [];

    public function __construct()
    {
        $this->name = 'pjcategoryproducts';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Jérémie Pasquis';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PJ Category Products');
        $this->description = $this->l('Displays a list of products associated with each category directly in the category administration pages.');

        $this->ps_versions_compliancy = array('min' => '8.1', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install(): bool
    {
        return parent::install()
            && $this->registerHook([
                'displayAdminEndContent',
            ]);
    }

    public function uninstall(): bool
    {
        return parent::uninstall();
    }

    /**
     * Affiche la liste des produits associés à la catégorie
     */
    public function hookDisplayAdminEndContent($params): string
    {
        try {
            return $this->displayAdminEndContent($params);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Displays the list of products associated with the category with PrestaShop services
     * @throws PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException
     * @throws Exception
     */
    private function displayAdminEndContent($params)
    {
        if ($this->context->controller->controller_name !== 'AdminCategories') {
            return '';
        }

        $categoryId = (int)$this->get('request_stack')->getCurrentRequest()->get('categoryId');
        $localCacheName = __FUNCTION__.'_' . $this->context->controller->controller_name . '_' . $categoryId;

        if (isset(self::$localCache[$localCacheName])) {
            return self::$localCache[$localCacheName];
        }

        $categoryFilterForm = $this->get('form.factory')->create(
            PrestaShopBundle\Form\Admin\Sell\Product\Category\CategoryFilterType::class,
            $categoryId,
            ['action' => $this->get('router')->generate('admin_products_grid_category_filter'),]
        )->createView();

        $productFilters = $this->get('request_stack')->getCurrentRequest()->request->get('product', []);

        $filtersKeysType = [
            'id_product' => 'int.range',
            'name' => 'string',
            'reference' => 'string',
            'category' => 'string',
            'final_price_tax_excluded' => 'float.range',
            'quantity' => 'int.range',
            'active' => 'bool',
        ];

        $filters = [];
        if ($categoryId) {
            $filters['id_category'] = $categoryId;
        }

        foreach ($filtersKeysType as $key => $filterType) {
            switch ($filterType) {
                case 'int.range':
                    if (!empty($productFilters[$key]['min_field']) || !empty($productFilters[$key]['max_field'])) {
                        $filters[$key] = [
                            'min_field' => !empty($productFilters[$key]['min_field']) ? (int)$productFilters[$key]['min_field'] : null,
                            'max_field' => !empty($productFilters[$key]['max_field']) ? (int)$productFilters[$key]['max_field'] : null,
                        ];
                    }
                    break;
                case 'string':
                    if (!empty($productFilters[$key])) {
                        $filters[$key] = (string)$productFilters[$key];
                    }
                    break;
                case 'bool':
                    if (isset($productFilters[$key]) && ($productFilters[$key] === '1' || $productFilters[$key] === '0')) {
                        $filters[$key] = (int)$productFilters[$key];
                    }
                    break;
                case 'float.range':
                    if (!empty($productFilters[$key]['min_field']) || !empty($productFilters[$key]['max_field'])) {
                        $filters[$key] = [
                            'min_field' => !empty($productFilters[$key]['min_field']) ? (float)$productFilters[$key]['min_field'] : null,
                            'max_field' => !empty($productFilters[$key]['max_field']) ? (float)$productFilters[$key]['max_field'] : null,
                        ];
                    }
                    break;
            }
        }

        $grid = $this->get('prestashop.core.grid.presenter.grid_presenter')->present(
            $this->get('prestashop.core.grid.factory.product')->getGrid(
                new PrestaShop\PrestaShop\Core\Search\Filters\ProductFilters(
                    PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint::shop(
                        (int)Context::getContext()->shop->id
                    ),
                    [
                        "limit" => 20,
                        "offset" => 0,
                        "orderBy" => "id_product",
                        "sortOrder" => "desc",
                        "filters" => $filters,
                    ],
                    'product'
                )
            )
        );

        $html = $this->get('twig')->render('@Modules/pjcategoryproduct/views/templates/admin/grid.html.twig', [
            'categoryFilterForm' => $categoryFilterForm,
            'grid' => $grid,
        ]);

        self::$localCache[$localCacheName] = $html;
        return $html;
    }
}
