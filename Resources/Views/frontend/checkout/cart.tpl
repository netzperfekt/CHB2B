{extends file="parent:frontend/checkout/cart.tpl"}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name="frontend_checkout_actions_confirm"}
	{capture parentBlock assign=parentBlock}
		{$smarty.block.parent}
	{/capture}

	{include file="frontend/checkout/_requestcart.tpl" parent=$parentBlock}
{/block}

{block name="frontend_checkout_actions_confirm_bottom_checkout"}
	{capture parentBlock assign=parent}
		{$smarty.block.parent}
	{/capture}

	{include file="frontend/checkout/_requestcart.tpl" parent=$parentBlock}
{/block}
