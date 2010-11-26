<?php
// include an array selector class
require_once './lib/arrayselect.class.php';

/*!
	@brief Data manipulation class.
*/
class cMainData
{
	private $db;	//! PDO object
	private $dbSt;	//! Last PDO Statement

	/*!
		@brief Construct
		
		@param [in] $strHost Database host
		@param [in] $strDbName Database name
		@param [in] $strUser Database user
		@param [in] $strPass Database user password
	*/
	public function __construct($strHost, $strDbName, $strUser, $strPass)
	{
		$this->db = new PDO(
			"mysql:host={$strHost};dbname={$strDbName}",
			$strUser, $strPass,
			array()
			// TS usues latin1_bin...
			//array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		); 
	}

	/*!
		@brief Generate date-time stamps SQL
		
		Generate date-time stamps SQL with column name as "{column}".
		
		@param [in] $strDay The day for the select
		@param [in] $numDateTZ A project timezone at that date
		
		@return a string of wiki-style date stamps SQL separated with OR
	*/
	private function pf_genDTStampsSQL($strDay, $numDateTZ)
	{
		$dtStart = strtotime("$strDay -$numDateTZ hours");
		$vStamps = array();
		for ($i=0; $i<24; $i++)
		{
			$wikistampStart = date('YmdH', $dtStart + $i * 60 * 60);
			$vStamps[] = "{column} LIKE '$wikistampStart%'";
		}
		$vStamps = implode(' OR ', $vStamps);
		
		return $vStamps;
	}
	
	/*!
		@brief Get basic page info for DNA
		
		Gets basic pages info for the given \a strDay that where created on that day.
		
		@param [in] $strDay The day to check
		@param [in] $numDateTZ A project timezone at that date
		
		@if TODOP2_DONE
			@todo when date in recentchanges -> get (rc_user AS) user_id, (rc_cur_id AS) page_id, (rc_new_len AS) start_len FROM recentchanges
			@todo cache parts by date or rc_id/rev_id
		@endif
		
		@return $arrPages an array of pages containing: user_id, page_id, start_len
	*/
	public function pf_getPagesBasics($strDay, $numDateTZ)//, $numMinStartSize)
	{
		// gen dt stamps SQL 
		$vStamps = $this->pf_genDTStampsSQL($strDay, $numDateTZ);
		
		// get ids of pages edited on that date
		$strSQL = "SELECT rev_page FROM revision
			WHERE
			".str_replace('{column}', 'rev_timestamp', $vStamps)."
			GROUP BY rev_page
		";
		$vPages = $this->pf_fetchAllSQL($strSQL, PDO::FETCH_COLUMN);
		$vPages = implode(",", $vPages);
		if (empty($vPages))
		{
			return array();
		}
		
		// get ids of first rev of those pages
		$strSQL = "SELECT MIN(rev_id) as first_rev_id
			FROM revision
			WHERE rev_page IN ($vPages)
			GROUP BY rev_page
		";
		$vRevs = $this->pf_fetchAllSQL($strSQL, PDO::FETCH_COLUMN);
		$vRevs = implode(",", $vRevs);
		if (empty($vRevs))
		{
			return array();
		}
		
		// finally get data of pages created on that date
		$strSQL = "SELECT rev_user AS user_id, rev_page AS page_id, rev_len AS start_len
			FROM revision
			WHERE rev_id IN ($vRevs)
				AND (".str_replace('{column}', 'rev_timestamp', $vStamps).")
			ORDER BY user_id
		";
		$vPages = $this->pf_fetchAllSQL($strSQL);
		
		return $vPages;
	}

	/*!
		@brief Get length of last revision for DNA
		
		Gets length of pages at the end of the day.
		
		@param [in] $arrPages The array of pages containing at least page_id
		@param [in] $strDay The day to check
		@param [in] $numDateTZ A project timezone at that date
		
		@return an array with key=>val set to: page_id=>end_len
	*/
	public function pf_getPageLastLens(&$arrPages, $strDay, $numDateTZ)//, $numMinStartSize)
	{
		if (empty($arrPages))
		{
			return array();
		}

		// gen dt stamps SQL 
		$vStamps = $this->pf_genDTStampsSQL($strDay, $numDateTZ);
		
		// ids of pages
		$oArraySelector = new cArraySelector();
		$vPages = $oArraySelector->pf_selectData($arrPages, 'page_id');

		// get ids of the last rev of those pages on the day
		$strSQL = "SELECT MAX(rev_id) as last_rev_id
			FROM revision
			WHERE
				rev_page IN ($vPages)
				AND (".str_replace('{column}', 'rev_timestamp', $vStamps).")
			GROUP BY rev_page
		";
		$vRevs = $this->pf_fetchAllSQL($strSQL, PDO::FETCH_COLUMN);
		$vRevs = implode(",", $vRevs);
		if (empty($vRevs))
		{
			return array();
		}
		
		// get len of last revs
		$strSQL = "SELECT rev_page AS page_id, rev_len AS end_len
			FROM revision
			WHERE rev_id IN ($vRevs)
		";
		$vPages = $this->pf_fetchAllSQL($strSQL);
		$arrRet = array();
		if (!empty($vPages))
		{
			foreach ($vPages as $arr)
			{
				$arrRet[$arr['page_id']] = $arr['end_len'];
			}
		}
		return $arrRet;
	}

	/*!
		@brief Get user info for DNA
		
		Currently only gets user name for the pages given in the pages array.
		
		@param [in] $arrPages The array of pages containing at least user_id
		
		@return an array with key=>val set to: user_id=>user_name
	*/
	public function pf_getUserInfo(&$arrPages)
	{
		if (empty($arrPages))
		{
			return array();
		}

		$oArraySelector = new cArraySelector();
		$vUsers = $oArraySelector->pf_selectData($arrPages, 'user_id');

		$strSQL = "SELECT user_id, user_name FROM user
			WHERE
				user_id IN ($vUsers)
		";
		$vUsers = $this->pf_fetchAllSQL($strSQL);
		$arrRet = array();
		if (!empty($vUsers))
		{
			foreach ($vUsers as $arr)
			{
				$arrRet[$arr['user_id']] = $arr['user_name'];
			}
		}
		return $arrRet;
	}

	/*!
		@brief Get page title for DNA
		
		Gets page titles and namespace for the pages given in the pages array.
		Does not append this data beacuse it might be easier to refresh redirects this way.
		
		@param [in] $arrPages The array of pages containing at least page_id
		
		@return an array with key=>val set to: page_id=>array('page_title'=>..., 'page_namespace'=>...)
	*/
	public function pf_getPageTitles(&$arrPages)
	{
		if (empty($arrPages))
		{
			return array();
		}

		$oArraySelector = new cArraySelector();
		$vPages = $oArraySelector->pf_selectData($arrPages, 'page_id');

		$strSQL = "SELECT page_id, page_title, page_namespace FROM page
			WHERE
				page_id IN ($vPages)
		";
		$vPages = $this->pf_fetchAllSQL($strSQL);
		$arrRet = array();
		if (!empty($vPages))
		{
			foreach ($vPages as $arr)
			{
				$arrRet[$arr['page_id']] = array('page_title'=>$arr['page_title'], 'page_namespace'=>$arr['page_namespace']);
			}
		}
		return $arrRet;
	}
	
	/*!
		@brief Fetch all rows to an assoc array 
		
		@param [in] $strSQL The query
		@param [in] $pdoFetchStyle PDO fetch style (defaults to FETCH_ASSOC)
		
		@return rows returned by the server
		
		@see PDO::fetchAll();
	*/
	private function pf_fetchAllSQL($strSQL, $pdoFetchStyle=PDO::FETCH_ASSOC)
	{
		$this->dbSt = $this->db->prepare($strSQL);
		if (!$this->dbSt->execute())
		{
			$this->pf_throwSQLError();
			return false;
		}
		return $this->dbSt->fetchAll($pdoFetchStyle);
	}

	/*!
		@brief Throw SQL Error
		
		Throw SQL error (call after unsuccessful query execution)
	*/
	private function pf_throwSQLError()
	{
		$strSQL = $this->dbSt->queryString;
		$arrErr = $this->dbSt->errorInfo();
		trigger_error("\nSQL error: {$arrErr[2]}\nSQL:{$strSQL}\n", E_USER_ERROR);
	}

	/* --=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=-- *\
		END-OF-CLASS
	\* --=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=--=-- */
}

?>