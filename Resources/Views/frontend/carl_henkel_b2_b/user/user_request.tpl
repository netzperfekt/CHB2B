<form id="request-user--form"
      method="post"
      action="{url controller='CarlHenkelB2B' action='userNew'}">

    <div id="modalUserRequest_{$user.id}" class="modal" hidden>

        <span class="modal-label">E-Mail:</span>
        <input type="email" required
               maxlength="100" style="width: 200px"
               name="user_email">
        <br>

        <span class="modal-label">Vorname:</span>
        <input type="text" required
               maxlength="100" style="width: 200px"
               name="user_firstname">
        <br>

        <span class="modal-label">Nachname:</span>
        <input type="text" required
               maxlength="100" style="width: 200px"
               name="user_lastname">

        <br><br>

        {getRoles out="allRoles"}
        <span class="modal-label">Rolle(n):</span>
        <select id="user_roles" name="user_roles[]" multiple style="width: 200px; height: 120px" data-no-fancy-select="true">
            {foreach $allRoles as $role}
                <option value="{$role.title}">{$role.title}</option>
            {/foreach}
        </select>
        <br>
        <small>(mehrere Rollen mit STRG + Mausklick auswählen)</small>
        <br><br>

        <span class="modal-label">Budget:</span>
        <input type="number" min="0" maxlength="10" style="width: 200px"
               name="user_budget" value="{$user.b2b_budget}"> €

        <br><br>
        <input type="submit" value="Neuen Nutzer beantragen" class="btn is--primary">

        <button type="button" class="btn"
                onclick="closeModal('modalUserRequest_{$user.id}')" class="right">
            Abbrechen
        </button>
    </div>

    <button type="submit"
            class="btn is--large"
            onclick="return confirmAction(this, 'modalUserRequest_{$user.id}', null, function() { document.getElementById('newusermail').focus() })">
        {s name="user_request"}Neuen Nutzer anfordern{/s}
    </button>
</form>
