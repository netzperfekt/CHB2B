{namespace name='netzperfekt/carlhenkelb2b'}
{assign var="cartIsValid" value=$sBasket.content && (!($sDispatchNoOrder && !$sDispatches) && !$sInvalidCartItems)}

{if $cartIsValid}
	{if $cartId}
		<div class="alert is--info" style="padding: .25rem">
			{s name='alert_cart_request'}Warenkorb-Anforderung / Freigabe-Prüfung{/s}
		</div>
	{/if}

	{if {hasPermission permission="request_budget"} && $budgetExceeded == 1 && !$cartId}
		<span class="budget-warning">
			{s name='cart_budget_exceeded'}Bestellgrenze überschritten{/s}:
			<span class="is--label" style="font-weight: bold; color: red">
				max. {$userBudget|currency}
			</span>
		</span>
	{/if}

	{if {hasRole role="viewer"}}
		<span class="budget-warning">
			{s name='cart_view_only'}Sie können keine Bestellungen tätigen.{/s}
		</span>
	{/if}

	{if $isAllowedToOrderCart}
		{$parent}
	{elseif $isAllowedToRequestCart}
		{include file="frontend/checkout/_requestcart_button.tpl"}
		{include file="frontend/carl_henkel_b2_b/_scripts.tpl"}
	{/if}
{/if}
