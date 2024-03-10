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
