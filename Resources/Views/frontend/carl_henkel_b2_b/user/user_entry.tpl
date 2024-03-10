{namespace name='netzperfekt/carlhenkelb2b'}
<div class="panel--tr">
	<div class="panel--td" style="width: 12%">
		<div class="column--value">
			{if $user.id == $loggedInUserId}<b>{/if}
			{$user.customerNumber}{if $user.subNumber}.{$user.subNumber}{/if}
			{if $user.id == $loggedInUserId}</b>{/if}
		</div>
	</div>

	<div class="panel--td" style="width: 20%">
		<div class="column--value">
			{if $user.id == $loggedInUserId}<b>{/if}
			{$user.lastname}, {$user.firstname}
			{if $user.id == $loggedInUserId}</b>{/if}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value col-right">
			{if $user.b2b_budget}
				{$user.b2b_budget|currency}
			{/if}
		</div>
	</div>

	<div class="panel--td" style="width: 30%">
		<div class="column--value">
	    	{getRoles userId=$user.id out="roles"}
	    	{foreach from=$roles item=role}
			    <span class="is--label" title="{$role.description}">
			    	{$role.title}
		    	</span>
			{/foreach}
		</div>
	</div>

	<div class="panel--td" style="width: 10%">
		<div class="column--value" style="display:flex">
	        {if {hasPermission permission="request_changes"}}
				{include file="frontend/carl_henkel_b2_b/user/user_change.tpl"}
				&nbsp;&nbsp;
				{include file="frontend/carl_henkel_b2_b/user/user_delete.tpl"}
			{/if}
		</div>
	</div>
</div>
