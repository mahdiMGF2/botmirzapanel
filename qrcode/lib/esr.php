<?php
  /* ESR code generation */
  /* 01(10 digits amount)(check amount)>(26 digits reference)(check reference)+ (2 digits first part account)(6 digits middle part account)(check account) */
  include("modulo10.php");
  
  class ESR
  {  
	  public static function account_code($account)
	  {
		  $parts = explode("-", $account);
		  if (count($parts) > 2)
		  {
		      $code = str_pad($parts[0], 2, "0", STR_PAD_LEFT) .
		          str_pad($parts[1], 6, "0", STR_PAD_LEFT) .
		          $parts[2];
		  }
		  else
		  {
			  $code = "FAILED";
		  }
		  return $code;
	  }
	  
	  public static function reference_code($reference)
	  {
		  if (strlen($reference) > 26)
		  {
			  $reference = substr($reference, 0, 26);
		  }
		  
		  $reference = str_pad($reference, 26, "0", STR_PAD_LEFT);
		  
		  $modulo10 = new Modulo10();
		  $check = $modulo10->create($reference);
		  
		  $code = $reference . $check;
		  return $code;
	  }
	  
	  public static function amount_code($amount)
	  {
		  $amount = round($amount, 2);
		  
		  $int_amount = $amount * 100.0;
		  if ($int_amount > 9999999999)
		  {
			  $code = "TOO MUCH";
		  }
		  else
		  {
			  // include type 01
			  $content = "01" . str_pad($int_amount, 10, "0", STR_PAD_LEFT);
			  $modulo10 = new Modulo10();
		      $check = $modulo10->create($content);
		  
		      $code = $content . $code;
		  }
		  return $code;
	  }
  }
