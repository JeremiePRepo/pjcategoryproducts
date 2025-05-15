<?php

namespace JeremieP\PJCategoryProducts;

use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\ProductGridDefinitionFactory;
use Symfony\Component\Translation\TranslatorInterface;

class CategoryProductsGridDefinitionFactory extends ProductGridDefinitionFactory
{
    protected $translator;

    public function __construct(
        $hookDispatcher,
        $configuration,
        $multistoreFeature,
        $shopContext,
        $formFactory,
        $singleShopChecker,
        $multipleShopsChecker,
        TranslatorInterface $translator
    ) {
        parent::__construct(
            $hookDispatcher,
            $configuration,
            $multistoreFeature,
            $shopContext,
            $formFactory,
            $singleShopChecker,
            $multipleShopsChecker
        );

        $this->translator = $translator;
    }

    protected function getName(): string
    {
        return $this->translator->trans('Category Products', [], 'Modules.CategoryProducts.Admin');
    }
}