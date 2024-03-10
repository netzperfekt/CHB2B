<form id="change-user--form"
      method="post"
      action="{url controller='CarlHenkelB2B' action='userChange'}">

    <input type="hidden"
           name="id"
           value="{$user.id}">

    <div id="modalUserChange_{$user.id}" class="modal" hidden>
        <span class="modal-label">Rolle(n):</span>

        {getRoles out="allRoles"}
        <select id="user_roles" name="user_roles[]" multiple style="width: 200px; height: 120px" data-no-fancy-select="true">
            <option value="">- keine -</option>
            {foreach $allRoles as $role}
                <option value="{$role.short}" {if {hasRole role=$role.short userId=$user.id}}selected{/if}>{$user.id} - {$role.title}</option>
            {/foreach}
        </select>

        <br>

        <span class="modal-label">Budget:</span>

        <input type="number" min="0" maxlength="10" style="width: 200px"
               name="user_budget" value="{$user.b2b_budget}"> €

        <br><br>
        <input type="submit" value="Änderung beantragen" class="btn is--primary">
    </div>

    <button type="submit"
            class="btn is--large"
            onclick="return confirmAction(this, 'modalUserChange_{$user.id}')">
        <i class="icon--pencil"></i>
    </button>
</form>
