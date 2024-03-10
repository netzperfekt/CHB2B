{extends file='parent:frontend/account/index.tpl'}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name='frontend_index_start'}
    {$smarty.block.parent}
    {$sBreadcrumb.0.link = {url module=frontend controller=account action=index}}
    {$sBreadcrumb[] = ['name' => "B2B - Nutzerübersicht", 'link'=>{url}]}
{/block}

{block name="frontend_index_content"}
	<div class="netzp--carlhenkelmain content account--content">
        {include file="frontend/carl_henkel_b2_b/_message.tpl"}

        {if {hasPermission permission="show_user"}}
            {include file="frontend/carl_henkel_b2_b/user/user.tpl"}
        {/if}
    </div>
{/block}
