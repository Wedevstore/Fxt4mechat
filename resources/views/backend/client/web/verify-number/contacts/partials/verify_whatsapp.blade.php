<div class="">
   <span class="d-block">
      {{ $q->verify_whatsapp == 1 ? 'Verified' : ($q->verify_whatsapp == 2 ? 'Unverified' : 'Not Checked') }}
   </span>
</div>
