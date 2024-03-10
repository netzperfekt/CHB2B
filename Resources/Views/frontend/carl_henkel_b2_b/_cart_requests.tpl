{namespace name='netzperfekt/carlhenkelb2b'}
<div class="account--welcome panel">
    <h1 class="panel--title">{s name='cart_requests'}Meine Bestell-Anforderungen{/s}</h1>
</div>

<div class="account--orders-overview panel is--rounded">
    {if ! $cartRequests}
        <div class="account--no-orders-info">
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='no_requests'}Sie haben noch keine Bestellungen angefordert.{/s}"}
        </div>
    {else}

    	<div class="panel--table">
    		<div class="panel--tr">
    			<div class="panel--th" style="width: 7%">
                    {s name='cart_id'}ID{/s}
                </div>
                <div class="panel--th" style="width: 5%">
                    {s name='cart_positions'}Anz.{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_total'}Summe{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_requested'}Angefordert{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_ordered'}Bestellt{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_ordernumber'}Bestellnr.{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                    {s name='cart_status'}Status{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_approver'}Bearbeiter{/s}
                </div>
                <div class="panel--th" style="width: 10%">
                </div>
    		</div>

    		{foreach $cartRequests as $cartRequest name=cartRequests}
                {include file="frontend/carl_henkel_b2_b/_cart_request_entry.tpl" 
                         number=$smarty.foreach.cartRequests.iteration}
    		{/foreach}
    	</div>
    {/if}
</div>

{include file="frontend/carl_henkel_b2_b/_scripts.tpl"}
