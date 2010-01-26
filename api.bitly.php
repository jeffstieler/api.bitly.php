<?php

/**
* A simple wrapper class for Bit.ly's API meant for use
* in WordPress plugins
*
* Instantiate a new Bitly object:
* $bitly = new Bitly(loginname, apikey, [version]);
*
* Returns the shorten Bit.ly URL:
* $bitly->shorten("http://longurl.com");
*
* Returns the long URL:
* $bitly->expand("http://bit.ly/ABCDE");
* $bitly->expand("FGHiJ");
*
* Returns the JSON response:
* $bitly->info("http://bit.ly/ABCDE");
* $bitly->info("FGHiJ");
* $bitly->info("FGJiC", "htmlTitle,thumbnail");
* $bitly->stats("http://bit.ly/ABCDE");
* $bitly->stats("DEFjJ"); 
*
* Complete API documentation can be found at:
* http://code.google.com/p/bitly-api/wiki/ApiDocumentation
*
* @author Andy Goh
* @author Jeff Stieler
*/
class Bitly
{
	/**
	* Username for Bit.ly
	*
	* @var string
	**/
	private $login = '';

	/**
	* API key for Bit.ly
	* Get it from http://bit.ly/app/developers
	*
	* @var string
	**/
	private $apikey = '';
	
	/**
	* Bit.ly API version
	*
	* @var string
	**/
	private $version = '2.0.1';
	
	function __construct($login, $apikey, $version='2.0.1')
	{
		$this->login = $login;
		$this->apikey = $apikey;
		$this->version = $version;
	}
	
	/**
	* Shortens the specified long URL
	*
	* @return short url
	**/
	function shorten($longurl)
	{
		$request = 'http://api.bit.ly/shorten?version='.$this->version.'&longUrl='.$longurl;
		
		$response = $this->process($this->request($request));
		
		if ($response['errorCode'] == 0)
		{
			return $response['results'][$longurl]['shortUrl'];
		}
		return $response;
	}
	
	/**
	* Expands the specified URL or hash
	*
	* @return long url
	**/
	function expand($in)
	{
		$request = 'http://api.bit.ly/expand?version='.$this->version;
		
		// short url
		if (substr($in, 0, 14) == "http://bit.ly/")
		{
			$request = $request.'&shortUrl='.$in;
			$in = substr($in, 14);
		}
		// hash
		else
		{
			$request = $request.'&hash='.$in;
		}
		
		$response = $this->process($this->request($request));
		
		if ($response['errorCode'] == 0)
		{
			return $response['results'][$in]['longUrl'];
		}
		return $response;
	}
	
	/**
	* Gets information about the given short URL or hash
	*
	* Array elements:
	* - Hash of Bitly URL
	*	- calais					(array)
	*	- contentLength
	*	- contentType
	*	- exif						(array)
	*	- globalHash
	*	- hash
	*	- htmlMetaDescription
	*	- htmlMetaKeywords			(array)
	*	- htmlTitle
	*	- id3						(array)
	*	- keywords					(array)
	*	- longUrl
	*	- metacarta					(array)
	*	- mirrorUrl
	*	- surbl
	*	- thumbnail					(array)
	*	- users						(array)
	*	- version
	*
	* @return info about the short url/hash
	**/
	function info($in)
	{
		$request = 'http://api.bit.ly/info?version='.$this->version;
		
		// short url
		if (substr($in, 0, 14) == "http://bit.ly/")
		{
			$request = $request.'&shortUrl='.$in;
			$in = substr($in, 14);
		}
		// hash
		else
		{
			$request = $request.'&hash='.$in;
		}
		
		$response = $this->process($this->request($request));
		
		if ($response['errorCode'] == 0)
		{
			return $response['results'];
		}
		return $response;
	}

	/**
	* Returns traffic and referer data
	* for the given short URL or hash
	*
	* Array elements:
	* - clicks							// clicks to source from bit.ly overall
	* - hash							// global bit.ly hash for source
	* - referrers				(array) // traffic to source from bit.ly overall
	* - userClicks						// clicks to source from your bit.ly url
	* - userHash						// user bit.ly hash for source
	* - userReferrers			(array) // traffic to source from your bit.ly url
	*
	* @return traffic and referrer data
	**/
	function stats($in)
	{
		$request = 'http://api.bit.ly/stats?version='.$this->version;
		
		// short url
		if (substr($in, 0, 14) == "http://bit.ly/")
		{
			$request = $request.'&shortUrl='.$in;
		}
		// hash
		else
		{
			$request = $request.'&hash='.$in;
		}
		
		$response = $this->process($this->request($request));
		
		if ($response['errorCode'] == 0)
		{
			return $response['results'];
		}
		return $response;
	}
	
	/**
	 * Retrieve the error codes and messages associated with Bitly
	 *
	 * @return error codes and messages
	 **/
	function errors()
	{
		$request = 'http://api.bit.ly/errors?version='.$this->version;
		
		$response = $this->process($this->request($request));
		
		if ($response['errorCode'] == 0)
		{
			return $response['results'];
		}
		return $response;
	}
	
	/**
	* Single function to deal with sending wp_remote_get requests
	* to the URL specified
	*
	* @param string $url the url to send the curl requests
	* @return void
	**/
	private function request($url)
	{
		$url = $url . "&login=" . $this->login . "&apiKey=" . $this->apikey;

		$response = wp_remote_get($url);

		if ($response instanceof WP_Error)
		{
			return false;
		}
		else
		{
			return $response['body'];
		}
	}
	
	/**
	 * Prepare the JSON data as an array
	 *
	 * @param string $json the json response
	 * @return object variable
	 **/
	private function process($data)
	{
		return json_decode($data, true);
	}
}
?>
