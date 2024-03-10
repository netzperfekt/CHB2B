{extends file="parent:frontend/account/sidebar.tpl"}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name="frontend_account_menu_link_orders"}
    {$smarty.block.parent}

	{if {hasRole role="requester"} || {hasRole role="approver"} || {hasRole role="viewer"} ||
		{hasRole role="two_person"} || {hasRole role="main"} }
		<li class="navigation--entry">
			<a href="{url controller='CarlHenkelB2B' action='carts'}"
			   title="{s name="sidebar_cart_requests"}Anforderungen{/s}"
			   class="navigation--link{if {controllerName|lower} == 'carlhenkelb2b' && $sAction == 'carts'} is--active{/if}">

				{s name='sidebar_cart_requests'}Anforderungen{/s}

				{if ({hasRole role="approver"} || {hasRole role="two_person"}) && $cartCountApprovals > 0}
					<div class="badge is--primary">{$cartCountApprovals}</div>
				{elseif {hasRole role="requester"} && $cartCountRequests > 0}
					<div class="badge is--primary">{$cartCountRequests}</div>
				{/if}

			</a>
		</li>
	{/if}

    {if {hasPermission permission="show_user"}}
		<li class="navigation--entry">
			<a href="{url controller='CarlHenkelB2B' action='user'}"
			   title="{s name="sidebar_cart_user"}Nutzerübersicht{/s}"
			   class="navigation--link{if {controllerName|lower} == 'carlhenkelb2b' && $sAction == 'user'} is--active{/if}">

				{s name='sidebar_cart_user'}Nutzer-Übersicht{/s}

			</a>
		</li>
	{/if}
{/block}
