{namespace name='netzperfekt/carlhenkelb2b'}

<form id="request--form"
      method="post"
      action="{url controller='CarlHenkelB2B' action='cartRequest'}">

    <input type="hidden"
           name="cartid"
           value="{$cartId}">

    <button type="submit"
            class="btn is--primary button--checkout is--icon-right right"
            style="margin-left: .5rem"
            title="Bestellung anfordern"
            onclick="return confirmAction(this, 'approver', '<b style=\'padding: 0 .2rem; background-color: white; color: red\'>Bearbeiter auswählen</b> und dann noch einmal bestätigen!', function(el) { if('{$ajax}' == '1') el.style.display = 'inline-block'; })">
        {s name="cart_request"}Bestellung anfordern{/s}
        <i class="icon--arrow-right"></i>
    </button>

    <span hidden id="approver" class="select-approver{if $ajax}-ajax{/if}">
        <select id="approverid"
                name="approverid"
                class="right">
            <option value="0">{s name='cart_approver'}Bearbeiter{/s}: {s name='cart_approver_all'}- egal -{/s}</option>
            {foreach from=$approver key=value item=name}
                <option value="{$value}">{$name}</option>
            {/foreach}
        </select>
    </span>
</form>
