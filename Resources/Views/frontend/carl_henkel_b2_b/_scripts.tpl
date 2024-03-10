<script>
    // needs popper.min.js + tippy.min.js

    function confirmAction(el, hiddenId = '', confirmationMessage = '', hiddenCallback = null)
    {
       if(el.getAttribute('data-confirm') == 1) {
          return true;
       }

       if(confirmationMessage != null)
       {
           el.style.backgroundColor = 'red';
           el.style.color = 'white';
           el.setAttribute('data-confirm', 1);
       }

       if(hiddenId != '')
       {
           let elHidden = document.getElementById(hiddenId);
           elHidden.removeAttribute('hidden');
           elHidden.focus();
           if(hiddenCallback)
           {
               hiddenCallback(elHidden);
           }
       }

       if(confirmationMessage != null)
       {
           tippy(el, {
               content: confirmationMessage != '' ? confirmationMessage : 'Noch einmal betätigen, um die Aktion auszuführen!',
               showOnCreate: true,
               allowHTML: true
           });
       }

       return false;
    }

    function closeModal(id)
    {
        let el = document.getElementById(id);
        el.hidden = true;
    }
</script>
