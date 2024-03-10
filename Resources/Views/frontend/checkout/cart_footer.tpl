{extends file="parent:frontend/checkout/cart_footer.tpl"}

{block name='frontend_checkout_cart_footer_add_product'}
    {if ! $cartId}
        {$smarty.block.parent}
    {/if}
{/block}
