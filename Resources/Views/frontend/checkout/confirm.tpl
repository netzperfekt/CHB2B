{extends file="parent:frontend/checkout/confirm.tpl"}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name='frontend_checkout_confirm_submit'}
   	{$smarty.block.parent}

	{if {hasPermission permission="approve"} && $cartId}
		<form id="reject--form"
			  method="post"
			  action="{url controller='CarlHenkelB2B' action='cartReject'}">

			<input type="hidden"
				   name="cartid"
				   value="{$cartId}">

			<input type="text" maxlength="255" style="width: 700px" hidden
				   id="rejectmessage" name="rejectmessage"
				   placeholder="{s name='cart_reject_message'}Mitteilung zur Zurückweisung (optional){/s}">

			<button type="submit"
					class="btn is--secondary is--large"
					onclick="return confirmAction(this, 'rejectmessage')">
				{s name="cart_reject"}Zurückweisen{/s}
			</button>
		</form>

		{include file="frontend/carl_henkel_b2_b/_scripts.tpl"}
	{/if}
{/block}

{block name="frontend_checkout_confirm_additional_features_add_product"}
	{if ! $cartId}
		{$smarty.block.parent}
	{/if}
{/block}
