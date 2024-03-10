{namespace name='netzperfekt/carlhenkelb2b'}
<div class="panel--tr" style="{if $cartHistoryEntry.message}border-bottom: none{/if}">
	<div class="panel--td" style="width: 7%">
		<div class="column--value">
			#{$cartHistoryEntry.id}
		</div>
	</div>

	<div class="panel--td" style="width: 5%">
		<div class="column--value col-right">
			{$cartHistoryEntry.positions}
		</div>
	</div>

	<div class="panel--td column--total" style="width: 10%">
		<div class="column--value col-right">
			{$cartHistoryEntry.total|currency}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value">
			{$cartHistoryEntry.requested|date:datetime_short}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value">
			{$cartHistoryEntry.ordered|date:datetime_short}
		</div>
	</div>

	<div class="panel--td" style="width: 10%">
		<div class="column--value">
			{$cartHistoryEntry.swOrderNumber}
		</div>
	</div>

	<div class="panel--td" style="width: 10%">
		<div class="column--value">
			{if $cartHistoryEntry.status == 0}
                <i class="icon--pencil" style="color: blue" 
                	 title="{s name='cart_status_open'}offen{/s}"></i>
			{elseif $cartHistoryEntry.status == 1}
				 <i class="icon--check" style="color: green"
				 	 title="{s name='cart_status_ordered'}bestellt{/s}"></i>
			{elseif $cartHistoryEntry.status == 9}
				 <i class="icon--cross" style="color: red"
				 	 title="{s name='cart_status_rejected'}zurückgewiesen{/s}"></i>
			{else}
				 <i class="icon--info" style="color: grey"
				 	 title="Status: {$cartHistoryEntry.status}"></i>
         {/if}
		</div>
	</div>

	<div class="panel--td" style="width: 20%">
		<div class="column--value">
			{if $cartHistoryEntry.approver != 0}
				{$cartHistoryEntry.approverLastName},
				{$cartHistoryEntry.approverFirstName} 
			{else}
				---
			{/if}
		</div>
	</div>
</div>

{if $cartHistoryEntry.message}
<div class="panel--tr">
	<div class="panel--td" style="padding-top: 0; overflow: hidden; width: 100%">
		<div class="column--value col-message" style="overflow-wrap: break-word">
			{$cartHistoryEntry.message}
		</div>
	</div>
</div>
{/if}
