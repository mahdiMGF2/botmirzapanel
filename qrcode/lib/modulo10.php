<?php 
	/* 
	 * Use like this
	 *    include("../api/modulo10.php");
	 *    $modulo10 = new Modulo10();
     *    echo $modulo10->create("01234987");
     * */

class Modulo10
{
    /**
     * Returns the luhn check digit
     *
     * @param string $s numbers as string
     * @return int checksum digit
     */
    public function create($s)
    {
		  $decimals = array(0,9,4,6,8,2,7,1,3,5);
		  $next = 0;

		  for ($i = 0; $i < strlen($s); $i++)
			{
			$next = $decimals[($next + substr($s, $i, 1)) % 10];
			}

		  return (10 - $next) % 10;
    }
    
    /**
     * Check luhn number
     *
     * @param string $number
     * @return bool
     */
    public function validate($number)
    {
		$content = substr($number, 0, -1);
		$check = substr($number, -1);
		if ($check == create($content))
		{
			$succcess = true;
		}
		else
		{
			$success = false;
		}
        return $success;
    }
}
