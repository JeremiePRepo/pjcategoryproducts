{if $products|@count > 0}
    <h3>{$category_name|escape:'html':'UTF-8'}</h3>
    <ul>
        {foreach from=$products item=product}
            <li>
                {$product.name} - {$product.price}
            </li>
        {/foreach}
    </ul>
{else}
    <p>{l s='No products found for this category.'}</p>
{/if}