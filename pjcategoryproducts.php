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
    protected static $hookCount = 0;

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
                'displayBackOfficeHeader'
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
        } catch (\PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Displays the list of products associated with the category with PrestaShop services
     * @throws PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException
     */
    private function displayAdminEndContent($params)
    {
        self::$hookCount++;
        if (Tools::getValue('controller') !== 'AdminCategories' || self::$hookCount > 1) {
            return '';
        }

        global $kernel;
        $container = $kernel->getContainer();
        $requestStack = $container->get('request_stack');
        $request = $requestStack->getCurrentRequest();
        $categoryId = (int)$request->get('categoryId');

        if (!$categoryId) {
            return '';
        }

        $productGridFactory = $container->get('prestashop.core.grid.factory.product_light');

        $filters = new PrestaShop\PrestaShop\Core\Search\Filters\ProductFilters(
            PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint::shop((int)Context::getContext()->shop->id),
            [
                "limit" => 20,
                "offset" => 0,
                "orderBy" => "id_product",
                "sortOrder" => "desc",
                "filters" => ['id_category' => $categoryId],
            ],
            'product'
        );

        $productGrid = $productGridFactory->getGrid($filters);
        $filteredCategoryId = null;

        if (isset($filters->getFilters()['id_category'])) {
            $filteredCategoryId = (int)$filters->getFilters()['id_category'];
        }

        // Correction de l'URL du filtre
        $categoriesForm = $container->get('form.factory')->create(
            PrestaShopBundle\Form\Admin\Sell\Product\Category\CategoryFilterType::class,
            $filteredCategoryId,
            [
                'action' => $container->get('router')->generate('admin_products_grid_category_filter'),
            ]
        );

        return $container->get('twig')->render('@PrestaShop/Admin/Sell/Catalog/Product/Grid/grid_panel.html.twig', [
            'categoryFilterForm' => $categoriesForm->createView(),
            'grid' => $container->get('prestashop.core.grid.presenter.grid_presenter')->present($productGrid),
            'enableSidebar' => false,
        ]);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('controller') === 'AdminCategories') {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        }
    }
}
