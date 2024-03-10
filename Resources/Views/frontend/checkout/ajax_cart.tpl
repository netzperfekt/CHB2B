{extends file="parent:frontend/checkout/ajax_cart.tpl"}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name='frontend_checkout_ajax_cart_open_checkout_inner'}
	{capture parentBlock assign=parentBlock}
		{$smarty.block.parent}
	{/capture}

	{include file="frontend/checkout/_requestcart.tpl" parent=$parentBlock ajax=1}
{/block}
