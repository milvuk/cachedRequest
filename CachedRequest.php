<?php

/**
 * https://github.com/milvuk/cachedRequest
 * 
 * Class CachedRequest
 * 
 * Database-cache driven version of php's file_get_contents().
 * We will fetch data from live URL, or if we have fresh copy of it in cache, use that copy instead.
 * Useful for example, when working with API's 
 * to lower resources usage on service side and improve speed on client side.
 */
class CachedRequest {

	protected $pdo;
	
	// Name of database table to be used for storing cache entries
	protected $cache_dbtable = 'cached_requests';

	// Time in seconds for duration of cached entries
	protected $cache_time = 3600;

	/**
	 * @var bool Check validity of response before storing it in cache
	 */
	public $checkValidity = false;

	/**
	 * @var string If we are checking for validity of response, look for this string in response to confirm it is
	 * a valid response.
	 * E.g. A json array will contain '[' string.
	 */
	public $validityString = '[';

	/**
	 * CachedRequest constructor.
	 * @param PDO $pdo instance of a pdo object, to be used for reading/writing to/from db cache
	 */
	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	/**
	 * Fetch contents of an URL, whether from cache or live site.
	 * @param $url URL location from witch we are fetching contents
	 * @return string wanted contents
	 */
	public function getData($url) {
		$data = $this->getCache($url);
		if ($data === false) {
			$data = $this->doGetContents($url);
			$this->setCache($url, $data);
		}
		if (rand(1,100) <= 5) {
			$this->clearCache();
		}
		return $data;
	}

	protected function setCache($url, $data) {
		// check if valid response
		if ($this->checkValidity) {
			if (strpos($data, $this->validityString) === false) {
				return;
			}
		}
		// store to DB
		$timeCached = date('Y-m-d H:i:s');
		$sql = 'INSERT INTO `' . $this->cache_dbtable . '` (url, response, time_cached) VALUES (:url, :data, "' . $timeCached . '")';
		$stmt = $this->pdo->prepare($sql);
		$stmt->bindValue(':url', $url, PDO::PARAM_STR);
		$stmt->bindValue(':data', $data, PDO::PARAM_STR);
		$stmt->execute();
	}

	protected function getCache($url) {
		// fetch from DB
		$timeValid = new DateTime('-' . $this->cache_time . ' secs');
		$sql = 'SELECT * FROM `' . $this->cache_dbtable . '`' .
			   ' WHERE time_cached >= "' . $timeValid->format('Y-m-d H:i:s') . '"' .
			   ' AND url = :url' .
			   ' ORDER BY time_cached DESC';
	    $stmt = $this->pdo->prepare($sql);
	    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
	    $stmt->execute();
	   	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	   	//var_dump($sql);exit;
	   	foreach ($rows as $row) {
	   		return $row['response'];
	   	}
	   	return false;
	}

	protected function clearCache() {
		// delete old entries from DB
		$timeValid = new DateTime('-' . $this->cache_time . ' secs');
		$sql = 'DELETE FROM `' . $this->cache_dbtable . '`' .
			   ' WHERE time_cached < "' . $timeValid->format('Y-m-d H:i:s') . '"';
	    $this->pdo->query($sql);
	}

	protected function doGetContents($url) {
		return file_get_contents($url);
	}
}