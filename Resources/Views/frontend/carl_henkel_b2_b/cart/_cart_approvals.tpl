{namespace name='netzperfekt/carlhenkelb2b'}
<div class="account--welcome panel">
    <h1 class="panel--title">{s name='cart_approvals'}Aktuell freizugebende Bestellungen{/s}</h1>
</div>

<div class="account--orders-overview panel is--rounded">
    {if ! $cartApprovals}
        <div class="account--no-orders-info">
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='no_approvals'}Momentan liegen keine freizugebenden Bestellungen für Sie vor.{/s}"}
        </div>
    {else}
    	<div class="panel--table">
    		<div class="panel--tr">
    			<div class="panel--th" style="width: 7%">
                    {s name='cart_id'}ID{/s}
                </div>
                <div class="panel--th" style="width: 20%">
                    {s name='cart_requester'}Nutzer{/s}
                </div>
                <div class="panel--th" style="width: 5%">
                    {s name='cart_positions'}Anz.{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_total'}Summe{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_requested'}Angefordert{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='cart_status'}Status{/s}
                </div>
                <div class="panel--th"></div>
    		</div>

    		{foreach $cartApprovals as $cartApproval name=cartApprovals}
                {include file="frontend/carl_henkel_b2_b/cart/_cart_approval_entry.tpl"
                         number=$smarty.foreach.cartApprovals.iteration}
    		{/foreach}
    	</div>
    {/if}
</div>

{include file="frontend/carl_henkel_b2_b/_scripts.tpl"}
