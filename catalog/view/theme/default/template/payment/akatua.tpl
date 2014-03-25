<form action="<?php echo $serverurl; ?>" method="post" id="payment">
  <input type="hidden" name="test_mode" value="<?php echo $test; ?>" />
  <input type="hidden" name="application_id" value="<?php echo $application_id; ?>" />
  <input type="hidden" name="signature" value="<?php echo $signature; ?>" />
  <input type="hidden" name="timestamp" value="<?php echo $timestamp; ?>" />
  <input type="hidden" name="transaction_type" value="<?php echo $transaction_type; ?>" />
  <input type="hidden" name="description" value="<?php echo $description; ?>" />
  <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
  <input type="hidden" name="invoice" value="<?php echo $invoice; ?>" />
  <input type="hidden" name="fail_url" value="<?php echo $failurl; ?>" />
  <input type="hidden" name="success_url" value="<?php echo $successurl; ?>" />
  <input type="hidden" name="callback_url" value="<?php echo $callbackurl; ?>" />
  <input type="hidden" name="logo_url" value="<?php echo $logourl; ?>" />

  <div class="buttons">
    <div class="right"><a onclick="$('#payment').submit();" class="button"><span><?php echo $button_confirm; ?></span></a></div>
  </div>
</form>