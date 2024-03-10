{namespace name='netzperfekt/carlhenkelb2b'}
<div class="account--welcome panel">
    <h1 class="panel--title">{s name='b2b_user'}B2B - Nutzerübersicht{/s}</h1>
</div>

<div class="account--orders-overview panel is--rounded">
    {if ! {hasPermission permission="show_user"}}
        <div class="account--no-orders-info">
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name='no_access'}<b>Kein Zugriff</b>{/s}"}
</div>
    {elseif ! $b2bUser}
        <div class="account--no-orders-info">
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='no_user'}Es gibt noch keine B2B-Nutzer.{/s}"}
        </div>
    {else}
        <div class="panel--table">
            <div class="panel--tr">
                <div class="panel--th" style="width: 12%">
                    {s name='user_id'}Kundennr.{/s}
                </div>
                <div class="panel--th" style="width: 20%">
                    {s name='user_name'}Name{/s}
                </div>
                <div class="panel--th" style="width: 15%">
                    {s name='user_budget'}Bestellgrenze{/s}
                </div>
                <div class="panel--th" style="width: 30%">
                    {s name='user_roles'}Rolle(n){/s}
                </div>
                <div class="panel--th">
                </div>
            </div>

            {foreach $b2bUser as $user name=b2bUser}
                {include file="frontend/carl_henkel_b2_b/_user_entry.tpl"
                         number=$smarty.foreach.b2bUser.iteration}
            {/foreach}
        </div>
    {/if}
</div>

{if {hasPermission permission="request_changes"}}
    <br>
        <form id="request-user--form" 
                method="post" 
                action="{url controller='CarlHenkelB2B' action='userNew'}">

                <input type="email" required
                         maxlength="100" style="width: 200px"
                         id="newusermail" name="newusermail"
                         placeholder="{s name='user_email'}E-Mail des neuen Nutzers{/s}"
                         hidden>

             <button type="submit" 
                      class="btn is--secondary is--large"
                      onclick="return confirmAction(this, 'newusermail')">
                {s name="user_request"}Neuen Nutzer anfordern{/s}
             </button>
          </form>
{/if}

{include file="frontend/carl_henkel_b2_b/_scripts.tpl"}
