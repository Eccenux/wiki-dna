<?php
/**
 * Array data selection class.
 *
 * Assoc. array is assumed.
 */
class cArraySelector
{
	private $strSeparator;	//!< The separator string to be used
	private $strSelColumn;	//!< The name of the column to be selected
	
	public function __construct()
	{
	}
	
	/**
	 * Function for the array_reduce.
	 * 
	 * @see pf_selectData()
	 *
	 * @param string $strPartialResult
	 * @param array $arrCurValue
	 * @return string
	 */
	private function pf_reduceData($strPartialResult, $arrCurValue)
	{
		if (isset($arrCurValue[$this->strSelColumn])) {
			$v = $arrCurValue[$this->strSelColumn];
			// only add if unique
			if (!preg_match("#{$this->strSeparator}{$v}({$this->strSeparator}|$)#", $strPartialResult))
			{
				$strPartialResult .= $this->strSeparator.$v;
			}
		}
		return $strPartialResult;
	}
	
	/**
	 * Selects values from one column from an associative array.
	 *
	 * Call this to select values of a \a $strColumn from \a $arrData
	 * which will be concatenated to a string separated by \a $strSeparator.
	 *
	 * Records that don't have the column will be skipped.
	 * Only unique values will be returned.
	 *
	 * @param array $arrData
	 * @param string $strColumn
	 * @param string $strSeparator
	 * @return string
	 */
	public function pf_selectData(&$arrData, $strColumn, $strSeparator=",")
	{
		$this->strSeparator = $strSeparator;
		$this->strSelColumn = $strColumn;
		$strRet = array_reduce($arrData, array($this, "pf_reduceData"));
		
		$strRet = ltrim ($strRet, $strSeparator);
		
		return $strRet;
	}
}

/**
$arrPages = array (
  0 =>
  array (
    'user_id' => '0',
    'page_id' => '4540768',
    'start_len' => '2567',
  ),
  1 =>
  array (
    'user_id' => '0',
    'page_id' => '4541001',
    'start_len' => '20',
  ),
  286 =>
  array (
    'user_id' => '966876',
    'page_id' => '4540908',
    'start_len' => '2696',
  ),
  287 =>
  array (
    'user_id' => '966895',
    'actor_id' => '123',
    'page_id' => '4541074',
    'start_len' => '3065',
  ),
);

$oArraySelector = new cArraySelector();
$vUsers = $oArraySelector->pf_selectData($arrPages, 'user_id');
echo "\n";
var_export($vUsers);

$vActors = $oArraySelector->pf_selectData($arrPages, 'actor_id');
echo "\n";
var_export($vActors);
/**/