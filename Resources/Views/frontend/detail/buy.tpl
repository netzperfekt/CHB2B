{extends file="parent:frontend/detail/buy.tpl"}
{namespace name='netzperfekt/carlhenkelb2b'}

{block name="frontend_detail_buy_button"}
    {if $cartId}
        <div class="buybox--button block btn is--disabled is--large">
            {s name='adding_not_possible'}Hinzufügen nicht möglich{/s}
        </div>
        <small>
            <b style="color: red">{s name='alert_cart_request'}Warenkorb-Anforderung / Freigabe-Prüfung{/s}</b>
        </small>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}