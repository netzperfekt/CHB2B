{extends file='parent:frontend/account/index.tpl'}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name='frontend_index_start'}
    {$smarty.block.parent}
    {$sBreadcrumb.0.link = {url module=frontend controller=account action=index}}
    {$sBreadcrumb[] = ['name' => "B2B - Bestell-Anforderungen", 'link'=>{url}]}
{/block}

{block name="frontend_index_content"}
	<div class="netzp--carlhenkelmain content account--content">
        {include file="frontend/carl_henkel_b2_b/_message.tpl"}

        {if $canShowApprovals}
            {include file="frontend/carl_henkel_b2_b/cart/cart_approvals.tpl"}
            <br>
        {/if}

        {if $canShowRequests}
            {include file="frontend/carl_henkel_b2_b/cart/cart_requests.tpl"}
            <br>
        {/if}

        {if $canShowHistory}
            {include file="frontend/carl_henkel_b2_b/cart/cart_history.tpl"}
        {/if}
    </div>
{/block}
