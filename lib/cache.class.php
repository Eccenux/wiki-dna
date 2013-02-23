<?php
/**
	Data caching class
*/
class cCache
{
	private $strCachePath;	//!< @brief Realtive or absolute path to a folder that will contain cache.
							//!< @note Always contains '/' at the end.
	private $strCacheSalt;	//!< More or less random string to be appended to a file name/path
	public $isDisabled;		//!< if true then writting to cache and reading from cache is disable
	private $isCacheKeyToBecomePath;		//!< if true then \a $strCacheKey will be changed to path
	private $strCacheKeyToPathSeparator;	//!< if \a $isCacheKeyToBecomePath is true then this is used to convert \a $strCacheKey to path

	/**
		Constructor
		
		Use \a $strCachePath to change the default folder.
		
		@param [in] $strCacheSalt Add salt for paranoid security.
			@warning Hold your salt in a secrete jar ;-).
		@param [in] $strCachePath A path to the cache folder.
		@param [in] $isCacheKeyToBecomePath Set to true to enable CacheKey to Path conversion
		@param [in] $strCacheKeyToPathSeparator Separator used to convert CacheKey to subdirs (only used if \a $isCacheKeyToBecomePath is true)
	*/
	public function __construct($strCacheSalt='', $strCachePath='./cache/', $isCacheKeyToBecomePath=false, $strCacheKeyToPathSeparator='-')
	{
		$this->strCachePath = rtrim ($strCachePath, '/').'/';
		$this->strCacheSalt = $strCacheSalt;
		$this->isDisabled = false;
		// cachekey setup
		$this->isCacheKeyToBecomePath = $isCacheKeyToBecomePath;
		$this->strCacheKeyToPathSeparator = $strCacheKeyToPathSeparator;
	}

	/**
		Write data to cache
		
		@param [in] $strCacheName An id of data to be cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		@param [in] $vCacheMe "any" data which in pratice means strings and ints simple arrays of both
		
		@warning DO NOT cache boolean values! Map it to int if you need such.
		
		@return 0 upon failure, 1 otherwise
	*/
	public function pf_writeToCache($strCacheName, $strCacheKey, $vCacheMe)
	{
		if ($this->isDisabled)
		{
			return 1;
		}
		
		$strFile = $this->pf_getCacheFileName($strCacheName, $strCacheKey);
		$strDir = dirname($strFile);
		if (!is_dir($strDir) && !mkdir($strDir, 0777, true))
		{
			trigger_error("writeToCache failed upon creating directory for $strCacheName[$strCacheKey]");
			return 0;
		}
		if (!file_put_contents($strFile ,"<?php\n\$vCachedValued = \n".var_export ($vCacheMe, true).";\n?>"))
		{
			trigger_error("writeToCache failed for $strCacheName[$strCacheKey]");
			return 0;
		}
		return 1;
	}

	/**
		Read data from cache
		
		@param [in] $strCacheName An id of data that is cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		
		@return $vCachedValued previously written with writeToCache
			or boolean false upon error (probably value was not saved)

		@warning Always check returned value agains boolean false with `===`.
	*/
	public function pf_readFromCache($strCacheName, $strCacheKey)
	{
		if ($this->isDisabled)
		{
			return false;
		}

		$strFile = $this->pf_getCacheFileName($strCacheName, $strCacheKey);
		if (file_exists($strFile))
		{
			@include ($strFile);
		}
		
		if (!isset($vCachedValued))
		{
			return false;
		}
		return $vCachedValued;
	}

	/**
		Check if data is cached
		
		@param [in] $strCacheName An id of data that is cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		
		@return boolean false if value is not available, true otherwise
	*/
	public function pf_isInCache($strCacheName, $strCacheKey)
	{
		if ($this->isDisabled)
		{
			return false;
		}

		$strFile = $this->pf_getCacheFileName($strCacheName, $strCacheKey);
		if (file_exists($strFile))
		{
			return true;
		}
		return false;
	}

	/**
		Returns storage time of cache entry
		
		@param [in] $strCacheName An id of data that is cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		
		@return storage time of the entry
	*/
	public function pf_getCacheTime($strCacheName, $strCacheKey)
	{
		if ($this->isDisabled)
		{
			return false;
		}

		$strFile = $this->pf_getCacheFileName($strCacheName, $strCacheKey);
		if (file_exists($strFile))
		{
			return filemtime($strFile);
		}
		return false;
	}

	/**
		Remove data from cache
		
		@param [in] $strCacheName An id of data that is cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		
		@return boolean false if operation was not successful, true otherwise
	*/
	public function pf_delFromCache($strCacheName, $strCacheKey)
	{
		$strFile = $this->pf_getCacheFileName($strCacheName, $strCacheKey);
		if (file_exists($strFile))
		{
			return @unlink($strFile);
		}
		return true;
	}

	/**
		Get a name of a file
		
		@param [in] $strCacheName An id of data to be cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		
		@return The name of the file with it's path
		
		@see cCache::pf_writeToCache()
	*/
	private function pf_getCacheFileName($strCacheName, $strCacheKey)
	{
		$reEvilChars = '#[/\\:*?"<>|\'\.=]#';	// '=' too because it is used in the name-key concat below
		
		if (!$this->isCacheKeyToBecomePath)
		{
			$strFileName = preg_replace($reEvilChars, '_', $this->strCacheSalt);
			$strFileName .= preg_replace($reEvilChars, '_', $strCacheName);
			$strFileName .= '_==_'.preg_replace($reEvilChars, '_', $strCacheKey);
		}
		else
		{
			$strFileName = strtr($strCacheKey, array($this->strCacheKeyToPathSeparator => '/'));
			$strFileName .= '/'.preg_replace($reEvilChars, '_', $this->strCacheSalt);
			$strFileName .= preg_replace($reEvilChars, '_', $strCacheName);
			$strFileName .= '_==_'.preg_replace($reEvilChars, '_', $strCacheKey);
		}

		$strFileName = $this->strCachePath . $strFileName . '.php';
		return $strFileName;
	}
}

?>