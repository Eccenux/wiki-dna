<?php
/*!
	@brief Data caching class
*/
class cCache
{
	private $strCachePath;	//! Realtive or absolute path to a folder that will contain cache
							//! Always contains '/' at the end.
	private $strCacheSalt;	//! More or less random string to be appended to a file name

	/*!
		@brief Constructor
		
		Use \a $strCachePath to change the default folder.
		
		@param [in] $strCacheSalt Add salt for paranoid security.
		@warning Hold your salt in a secrete jar.
		@param [in] $strCachePath A path to the cache folder.
	*/
	public function __construct($strCacheSalt='', $strCachePath='./cache/')
	{
		$this->strCachePath = rtrim ($strCachePath, '/').'/';
		$this->strCacheSalt = $strCacheSalt;
	}

	/*!
		@brief Write data to cache
		
		@param [in] $strCacheName An id of data to be cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		@param [in] $vCacheMe "any" data which in pratice means strings and ints simple arrays of both
		
		@warning DO NOT cache boolean values! Map it to int if you need such.
		
		@return 0 upon failure, 1 otherwise
	*/
	public function pf_writeToCache($strCacheName, $strCacheKey, $vCacheMe)
	{
		$strFile = $this->pf_getCacheFileName($strCacheName, $strCacheKey);
		if (!file_put_contents($strFile ,"<?php\n\$vCachedValued = \n".var_export ($vCacheMe, true).";\n?>"))
		{
			trigger_error("writeToCache failed for $strCacheName[$strCacheKey]");
			return 0;
		}
		return 1;
	}

	/*!
		@brief Read data from cache
		
		@param [in] $strCacheName An id of data to be cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		
		@return $vCachedValued previously written with writeToCache
			or boolean false upon error (probably value was not saved)

		@warning Always check returned value agains boolean false with `===`.
	*/
	public function pf_readFromCache($strCacheName, $strCacheKey)
	{
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

	/*!
		@brief Get a name of a file
		
		@param [in] $strCacheName An id of data to be cached
		@param [in] $strCacheKey Cache key (e.g. might be page_id for caching page data)
		
		@return The name of the file with it's path
		
		@see cCache::pf_writeToCache()
	*/
	private function pf_getCacheFileName($strCacheName, $strCacheKey)
	{
		$reEvilChars = '#[/\\:*?"<>|\'\.=]#';	// = because it is use in name-key concat
		
		$strFileName = preg_replace($reEvilChars, '_', $this->strCacheSalt);
		$strFileName .= preg_replace($reEvilChars, '_', $strCacheName);
		$strFileName .= '_==_'.preg_replace($reEvilChars, '_', $strCacheKey);

		$strFileName = $this->strCachePath . $strFileName . '.php';
		return $strFileName;
	}
}

?>