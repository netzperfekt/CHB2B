{namespace name='netzperfekt/carlhenkelb2b'}

<div class="panel--tr" style="{if $cartApproval.message}border-bottom: none{/if}">
	<div class="panel--td" style="width: 7%">
		<div class="column--value">
			#{$cartApproval.id}
		</div>
	</div>

	<div class="panel--td" style="width: 20%">
		<div class="column--value">
			{$cartApproval.lastName},
			{$cartApproval.firstName} 
		</div>
	</div>

	<div class="panel--td" style="width: 5%">
		<div class="column--value col-right">
			{$cartApproval.positions}
		</div>
	</div>

	<div class="panel--td column--total" style="width: 15%">
		<div class="column--value col-right">
			{$cartApproval.total|currency}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value">
			{$cartApproval.requested|date:datetime_short}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value">
			{if $cartApproval.status == 0}
                <i class="icon--pencil" style="color: blue" 
                	 title="{s name='cart_status_open'}offen{/s}"></i>
			{elseif $cartApproval.status == 1}
					 <i class="icon--check" style="color: green"
					 	 title="{s name='cart_status_ordered'}bestellt{/s}"></i>
			{elseif $cartApproval.status == 9}
					 <i class="icon--cross" style="color: red"
					 	 title="{s name='cart_status_rejected'}zurückgewiesen{/s}"></i>
			{else}
					 <i class="icon--info" style="color: grey"
					 	 title="Status: {$cartApproval.status}"></i>
         {/if}
		</div>
	</div>

	<div class="panel--td" style="float: right">
		<a href="{url controller='CarlHenkelB2B' action='cartApprove' id=$cartApproval.cartId}"
		   onclick="return confirmAction(this)"
   		   title="Bestellung prüfen"
   		   class="btn is--primary">
    		{s name="cart_approve_check"}Prüfen{/s}
		</a>
	</div>
</div>

{if $cartApproval.message}
<div class="panel--tr">
	<div class="panel--td" style="padding-top: 0">
		<div class="column--value col-message">
			{$cartApproval.message}
		</div>
	</div>
</div>
{/if}
