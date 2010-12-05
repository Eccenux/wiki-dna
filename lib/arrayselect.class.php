<?php
/*!
	@brief Array data selection class. Assoc array is assumed.
*/
class cArraySelector
{
	private $strSeparator;	//!< The separator string to be used
	private $strSelColumn;	//!< The name of the column to be selected
	
	public function __construct()
	{
	}
	
	//! Function for the array_reduce
	private function pf_reduceData($strPartialResult, $arrCurValue)
	{
		$v = $arrCurValue[$this->strSelColumn];
		if (!preg_match("#{$this->strSeparator}{$v}({$this->strSeparator}|$)#", $strPartialResult))
		{
			$strPartialResult .= $this->strSeparator.$v;
		}
		return $strPartialResult;
	}
	
	/*!
		@brief Selects values from one column from an associative array
	
		Call this to select values of a \a $strColumn from \a $arrData
		which will be concatenated to a string separated by \a $strSeparator
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

?>