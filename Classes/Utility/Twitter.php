<?php
namespace CmsWorks\CwTwitter\Utility;
/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Arjan de Pooter <arjan@cmsworks.nl>, CMS Works BV
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

//require_once(t3lib_extMgm::extPath('cw_twitter').'Classes/Contrib/OAuth.php');

/**
 *
 *
 * @package cw_twitter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Twitter {

//	/**
//	 * @var t3lib_cache_frontend_AbstractFrontend
//	 */
//	protected $cache;

	/**
	 * @var OAuthConsumer
	 */
	protected $consumer;

	/**
	 * @var OAuthToken
	 */
	protected $token;

	/**
	 * The base api url
	 *
	 * @var string
	 */
	protected $api_url = 'https://api.twitter.com/1.1/';

	/**
	 * Construct Twitter-object from settings
	 *
	 * @param array $settings
	 * @return \CmsWorks\CwTwitter\Utility\Twitter
	 */
	public static function getTwitterFromSettings($settings) {
		if(!$settings['oauth']['consumer']['key'] || !$settings['oauth']['consumer']['secret'] || !$settings['oauth']['token']['key'] || !$settings['oauth']['token']['secret']) {
			throw new \CmsWorks\CwTwitter\Exception\ConfigurationException("Missing OAuth keys and/or secrets.", 1362059167);

		}

		$twitter = new \CmsWorks\CwTwitter\Utility\Twitter();
		$twitter->setConsumer($settings['oauth']['consumer']['key'], $settings['oauth']['consumer']['secret']);
		$twitter->setToken($settings['oauth']['token']['key'], $settings['oauth']['token']['secret']);

		return $twitter;
	}

	/**
	 * @param array $settings
	 * @return array
	 */
	public static function getTweetsFromSettings($settings) {
		$twitter = self::getTwitterFromSettings($settings);

		$limit = intval($settings['limit']);
		switch ($settings['mode']) {
			case 'timeline':
				return $twitter->getTweetsFromTimeline($settings['username'], $limit, $settings['exclude_replies']);
				break;
			case 'search':
				return $twitter->getTweetsFromSearch($settings['query'], $limit);
				break;
			default:
				throw new \CmsWorks\CwTwitter\Exception\ConfigurationException("Invalid mode specified.", 1362059199);
				break;
		}
	}

	public static function getUserFromSettings($settings) {
		$twitter = self::getTwitterFromSettings($settings);

		return $twitter->getUser($settings['username']);
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
//		t3lib_cache::initializeCachingFramework();
//        try {
//            $this->cache = $GLOBALS['typo3CacheManager']->getCache('cwtwitter_queries');
//        }
        try {
            $name = 'TYPO3\\CMS\\Core\\Cache\\CacheManager';
            $manager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( $name );
            $this->cache = $manager->getCache( 'cwtwitter_queries' );
        }
//        catch (t3lib_cache_exception_NoSuchCache $e) {
//            $this->cache = $GLOBALS['typo3CacheFactory']->create(
//                'cwtwitter_queries',
//                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['frontend'],
//                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['backend'],
//                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']
//            );
//        }
        catch (\TYPO3\CMS\Core\Cache\Exception\CacheManagerNoSuchCacheException $e) {
            $this->cache = $manager->createCache('cwtwitter_queries');
        }
	}

	/**
	 * Sets consumer based on key and secret
	 *
	 * @param string $key
	 * @param string $secret
	 * @return void
	 */
	public function setConsumer($key, $secret) {
		$this->consumer = new OAuthConsumer($key, $secret);
	}

	/**
	 * Sets token based on key and secret
	 *
	 * @param string $key
	 * @param string $secret
	 * @return void
	 */
	public function setToken($key, $secret) {
		$this->token = new OAuthToken($key, $secret);
	}

	/**
	 * Get tweets from timeline from a specific user
	 *
	 * @param string $user
	 * @param int $limit
	 * @param boolean $exclude_replies
	 * @return array
	 */
	public function getTweetsFromTimeline($user = Null, $limit = Null, $exclude_replies = False) {
		$params = array(
			'exclude_replies' => $exclude_replies ? 'true':'false',
		);

		if($user) {
			$params['screen_name'] = $user;
		}
		if($limit) {
			$params['count'] = $limit;
		}

		return $this->getData('statuses/user_timeline', $params);
	}

	/**
	 * Search for tweets with specific query
	 *
	 * @param string $query
	 * @param int $limit
	 * @return array
	 */
	public function getTweetsFromSearch($query, $limit = Null) {
		$params = array(
			'q' => $query,
		);

		if($limit) {
			$params['count'] = $limit;
		}

		return $this->getData('search/tweets', $params)->statuses;
	}

	/**
	 * Returns the user object for specified user
	 *
	 * @param string $user
	 * @return stdClass
	 */
	public function getUser($user) {
		return $this->getData('users/show', array(
			'screen_name' => $user,
		));
	}

	/**
	 *
	 * @param string $path
	 * @param array $params
	 * @param string $method
	 * @return array
	 */
	protected function getData($path, $params, $method = 'GET') {
		if(!function_exists('curl_init')) {
			throw new \CmsWorks\CwTwitter\Exception\ConfigurationException("PHP Curl functions not available on this server", 1362059213);
		}

		if($method === 'GET') {
			if($this->cache->has($this->calculateCacheKey($path, $params))) {
				return $this->cache->get($this->calculateCacheKey($path, $params));
			}
		}

		$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $this->api_url.$path.'.json', $params);
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, $this->token);

		$hCurl = curl_init($request->to_url());
		curl_setopt_array($hCurl, array(
			CURLOPT_HTTPHEADER => array($request->to_header()),
			CURLOPT_RETURNTRANSFER => True,
			CURLOPT_TIMEOUT => 5000,
		));

		$response = curl_exec($hCurl);

		if($response === False) {
			throw new \CmsWorks\CwTwitter\Exception\RequestException(sprintf("Error in request: '%s'", curl_error($hCurl)), 1362059229);
		}

		$response = json_decode($response);
		if(isset($response->errors)) {
			$msg = "Error(s) in Request:";
			foreach($response->errors as $error) {
				$msg .= sprintf("\n%d: %s", $error->code, $error->message);
			}
			throw new \CmsWorks\CwTwitter\Exception\RequestException($msg, 1362059237);
		}

		if($method == 'GET') {
			$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cw_twitter']);
			$this->cache->set($this->calculateCacheKey($path, $params), $response, array(), $conf['lifetime']);
		}

		return $response;
	}

	/**
	 * Calculates the cache key
	 *
	 * @param string $path
	 * @param array $params
	 * @return void
	 */
	protected function calculateCacheKey($path, $params) {
		return md5(sprintf('%s|%s', $path, implode(',', $params)));
	}
}
