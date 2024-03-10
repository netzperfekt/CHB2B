{extends file="parent:frontend/account/index.tpl"}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name="frontend_account_index_success_messages"}
    {include file="frontend/carl_henkel_b2_b/_message.tpl"}
    {$smarty.block.parent}
{/block}

{block name="frontend_account_index_info_content"}
    {$smarty.block.parent}

	{getRoles userId=$sUserData['additional']['user']['id'] out="roles"}
	***s
    <div class="panel--body is--wide" {if count($roles) > 0}style="padding-top: 0; top: -2rem; margin-bottom: -3rem"{/if}>
		    {s name="customer_number"}Kundennummer{/s}:
		    <span class="is--label">
				{$sUserData.additional.user.text1}{if $sUserData.additional.user.text1}.{$sUserData.additional.user.text2}{/if}
			</span>

			{if count($roles) > 0}
				<br><br>

				{if {hasPermission permission="request_budget"}}
					{s name="budget"}Bestellgrenze{/s}:
					<span class="is--label">{$sUserData.additional.user.b2b_budget|currency}</span>
					<br>
				{/if}

				{if count($roles) > 0}
					{s name="roles"}Rollen{/s}:
					{foreach from=$roles item=role}
						<span class="is--label" title="{$role.description}">
							{$role.title}
						</span>
					{/foreach}
				{/if}
			{/if}
	</div>
{/block}