<?php
// include an array selector class
require_once './lib/arrayselect.class.php';

/**
	Data manipulation class.
*/
class cMainData
{
	private $db;	//!< PDO object
	private $dbSt;	//!< Last PDO Statement

	public $allowSlow=false;	//!< Allow slow queries

	/**
		Construct.
		
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

	/**
		Generate date-time stamps SQL.
		
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

	/**
		Gets max/min dates in Recent Changes.
		
		@note The times are GMT.
		
		@return array(
			'rc_min' => wikiTimeStamp,
			'rc_max' => wikiTimeStamp,
		)
	*/
	private function pf_getMaxMinRC()
	{
		// get min, max time
		$strSQL = "SELECT min(rc_timestamp) as rc_min, max(rc_timestamp) as rc_max
			FROM `recentchanges`
		";
		$vTimes = $this->pf_fetchAllSQL($strSQL);
		$vTimes['rc_min'] = $vTimes[0]['rc_min'];
		$vTimes['rc_max'] = $vTimes[0]['rc_max'];
		unset($vTimes[0]);
		
		return $vTimes;
	}

	/**
		Gets local max/min dates in Recent Changes.
		
		@param [in] $numDateTZ A project's current timezone
		
		@return array(
			'min' => almost ISO date (no 'T'),
			'max' => almost ISO date (no 'T'),
		)
	*/
	public function pf_getLocalMaxMinRC($numDateTZ)
	{
		$vTimes = $this->pf_getMaxMinRC();
		
		return array(
			'min' => date('Y-m-d H:i:s', strtotime($vTimes['rc_min']." +$numDateTZ hours")),
			'max' => date('Y-m-d H:i:s', strtotime($vTimes['rc_max']." +$numDateTZ hours")),
		);
	}
	
	/**
		Check if given date is in Recent Changes.
		
		@param [in] $strDay The day for the select
		@param [in] $numDateTZ A project timezone at that date
		
		@return
			- 1 if start time is within RC table
			- 2 if end time is within RC table
			- 0 otherwise
	*/
	public function pf_isDateInRC($strDay, $numDateTZ)
	{
		$dtStart = strtotime("$strDay -$numDateTZ hours");
		$dtEnd = $dtStart + 24 * 60 * 60;
		
		// get min, max time
		$vTimes = $this->pf_getMaxMinRC();
		
		// checks
		$numRet = 0;
		$dtStart = date('YmdHis', $dtStart);
		$dtEnd = date('YmdHis', $dtEnd);
		if ($dtStart > $vTimes['rc_min'] && $dtStart < $vTimes['rc_max'])
		{
			$numRet++;
			if ($dtEnd > $vTimes['rc_min'] && $dtEnd < $vTimes['rc_max'])
			{
				$numRet++;
			}
		}
		
		/*
		echo "\$dtStart:$dtStart, \$dtEnd:$dtEnd";
		echo "\$vTimes['rc_min']:{$vTimes['rc_min']}, \$vTimes['rc_max']:{$vTimes['rc_max']}";
		*/
		
		return $numRet;
	}
	
	/**
		Get basic page info for DNA.
		
		Gets basic pages info for the given \a strDay that where created on that day.
		
		@param [in] $strDay The day to check
		@param [in] $numDateTZ A project timezone at that date
		@param [in] $isAccuracyNeeded true if accuracy is needed (and RC must not be used)
		
		@return $arrPages an array of pages containing: actor_id, page_id, start_len
	*/
	public function pf_getPagesBasics($strDay, $numDateTZ, $isAccuracyNeeded=false)//, $numMinStartSize)
	{
		// check if in RC
		if ($isAccuracyNeeded)
		{
			$isInRC = 0;	//	data taken from RC are less accurate
		}
		else
		{
			$isInRC = $this->pf_isDateInRC($strDay, $numDateTZ);
		}
		
		// gen dt stamps SQL 
		$vStamps = $this->pf_genDTStampsSQL($strDay, $numDateTZ);
		
		// get ids of pages edited on that date
		if (!$isInRC)
		{
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
		}
		
		// get ids of first rev of those pages
		if (!$isInRC)
		{
			// this query is slow when replag is high - we allow this sometimes
			$strSQL = $this->allowSlow ? "SELECT /* SLOW_OK */ " : "SELECT ";
			$strSQL .= "MIN(rev_id) as first_rev_id
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
		}
		
		// finally get data of pages created on that date
		if (!$isInRC)
		{
			$strSQL = "SELECT rev_actor AS actor_id, rev_page AS page_id, rev_len AS start_len
				FROM revision
				WHERE
					rev_id IN ($vRevs)
					AND (".str_replace('{column}', 'rev_timestamp', $vStamps).")
				ORDER BY actor_id
			";
		}
		else
		{
			$strSQL = "SELECT rc_actor AS actor_id, rc_cur_id AS page_id, rc_new_len AS start_len
				FROM recentchanges
				WHERE rc_source = 'mw.new'
					AND (".str_replace('{column}', 'rc_timestamp', $vStamps).")
				ORDER BY actor_id
			";
		}
		$vPages = $this->pf_fetchAllSQL($strSQL);
		/*
			echo "\n"; var_export($strSQL);
			echo "\n"; var_export($vPages);
		*/
		
		//header('Content-Type: text/plain; charset=UTF-8');
		//exit;
		
		return $vPages;
	}

	/**
		Get length of last revision for DNA.
		
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

	/**
		Get user/actor info for DNA.
		
		Currently only gets user name for the pages given in the pages array.
		
		@param [in] $arrPages The array of pages containing at least `user_id` or `actor_id`.
		@param [in] $useActors If true then assume `actor_id` is used.
		
		@return an array with key=>val set to: user_id=>user_name
	*/
	public function pf_getUserInfo(&$arrPages, $useActors = false)
	{
		if (empty($arrPages))
		{
			return array();
		}

		$oArraySelector = new cArraySelector();
		$idList = $oArraySelector->pf_selectData($arrPages, $useActors ? 'actor_id' : 'user_id');

		if ($useActors) {
			// actor_user is null for anonymous actors (IP)
			$strSQL = "SELECT actor_id as id, actor_name FROM actor
				WHERE
					actor_id IN ($idList)
					AND actor_user IS NOT NULL
			";
		} else {
			$strSQL = "SELECT actor_user as id, actor_name FROM actor
				WHERE
					actor_user IN ($idList)
			";
		}
		$vUsers = $this->pf_fetchAllSQL($strSQL);
		$arrRet = array();
		if (!empty($vUsers))
		{
			foreach ($vUsers as $arr)
			{
				$arrRet[$arr['id']] = $arr['actor_name'];
			}
		}
		return $arrRet;
	}

	/**
		Get page title for DNA.
		
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
	
	/**
		Fetch all rows to an assoc array.
		
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

	/**
		Throw SQL Error.
		
		Call after unsuccessful query execution.
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