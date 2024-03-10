{namespace name='netzperfekt/carlhenkelb2b'}
<div class="account--welcome panel">
    <h1 class="panel--title">{s name='cart_history'}Anforderungs-Historie{/s}</h1>
</div>

<div class="account--orders-overview panel is--rounded">
    {if ! $cartHistory}
        <div class="account--no-orders-info">
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='no_history'}Es gibt noch keine Anforderungs-Historie.{/s}"}
        </div>
    {else}
        <div class="panel--table">
    		<div class="panel--tr">
    			<div class="panel--th" style="width: 7%">
                    {s name='cart_id'}ID{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_requester'}Anforderer{/s}
                </div>
                <div class="panel--th" style="width: 5%">
                    {s name='cart_positions'}Anz.{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_total'}Summe{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_requested'}Angefordert{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_ordered'}Bestellt{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_ordernumber'}Bestellnr.{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_approver'}Bearbeiter{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_status'}Status{/s}
                </div>
    		</div>

    		{foreach $cartHistory as $cartHistoryEntry name=cartHistory}
                {include file="frontend/carl_henkel_b2_b/cart/cart_history_entry.tpl"
                         number=$smarty.foreach.cartHistory.iteration}
    		{/foreach}
    	</div>
    {/if}
</div>
