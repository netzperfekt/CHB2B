{namespace name='netzperfekt/carlhenkelb2b'}
<div class="panel--tr" style="{if $cartRequest.message}border-bottom: none{/if}">
	<div class="panel--td" style="width: 7%">
		<div class="column--value">
			#{$cartRequest.id}
		</div>
	</div>

	<div class="panel--td" style="width: 5%">
		<div class="column--value col-right">
			{$cartRequest.positions}
		</div>
	</div>

	<div class="panel--td column--total" style="width: 10%">
		<div class="column--value col-right">
			{$cartRequest.total|currency}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value">
			{$cartRequest.requested|date:datetime_short}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value">
			{$cartRequest.ordered|date:datetime_short}
		</div>
	</div>

	<div class="panel--td" style="width: 10%">
		<div class="column--value">
			{$cartRequest.swOrderNumber}
		</div>
	</div>

	<div class="panel--td" style="width: 10%">
		<div class="column--value">
			{if $cartRequest.status == 0}
                <i class="icon--pencil" style="color: blue" 
                	 title="{s name='cart_status_open'}offen{/s}"></i>
			{elseif $cartRequest.status == 1}
					 <i class="icon--check" style="color: green"
					 	 title="{s name='cart_status_ordered'}bestellt{/s}"></i>
			{elseif $cartRequest.status == 9}
					 <i class="icon--cross" style="color: red"
					 	 title="{s name='cart_status_rejected'}zurückgewiesen{/s}"></i>
			{else}
					 <i class="icon--info" style="color: grey"
					 	 title="Status: {$cartRequest.status}"></i>
         {/if}
		</div>
	</div>

	<div class="panel--td" style="width: 15%">
		<div class="column--value">
			{if $cartRequest.approver != 0}
				{$cartRequest.approverLastName},
				{$cartRequest.approverFirstName} 
			{else}
				---
			{/if}
		</div>
	</div>

	<div class="panel--td" style="width: 10%">
		<div class="column--value">
			{if $cartRequest.status == 0 || $cartRequest.status == 9}
			<a href="{url controller='CarlHenkelB2B' action='cartEdit' id=$cartRequest.cartId}"
			   onclick="return confirmAction(this, '', '<b style=\'padding: 0 .2rem; background-color: white; color: red\'>Achtung</b> Ein eventuell bereits vorhandener <b>Warenkorb wird gelöscht</b> und es werden die Artikel dieser Bestellanforderung in den Warenkorb übernommen. Zum Ausführen bitte noch einmal den Button betätigen!')"
	   		   title="Anforderung bearbeiten"
	   		   class="btn" id="actionButton">
	    		<i class="icon--pencil"></i>
			</a>
			{/if}

			{if $cartRequest.status == 0 || $cartRequest.status == 9}
			<a href="{url controller='CarlHenkelB2B' action='cartDelete' id=$cartRequest.cartId}"
			   onclick="return confirmAction(this)"
	   		   title="Anforderung löschen"
	   		   class="btn is--secondary" id="actionButton">
	    		<i class="icon--cross"></i>
			</a>
			{/if}
		</div>
	</div>
</div>

{if $cartRequest.message}
<div class="panel--tr">
	<div class="panel--td" style="padding-top: 0; overflow: hidden; width: 100%">
		<div class="column--value col-message" style="overflow-wrap: break-word">
			{$cartRequest.message}
		</div>
	</div>
</div>
{/if}
