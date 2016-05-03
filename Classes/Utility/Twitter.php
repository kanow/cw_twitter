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

use CmsWorks\CwTwitter\Exception\ConfigurationException;
use CmsWorks\CwTwitter\Exception\RequestException;
use OAuth\Consumer;
use OAuth\Request;
use OAuth\SignatureMethod\HmacSha1;
use OAuth\Token;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 *
 * @package cw_twitter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Twitter {

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
	 */
	protected $cache;

	/**
	 * @var Consumer
	 */
	protected $consumer;

	/**
	 * @var Token
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
	 * @return Twitter
	 * @throws ConfigurationException
	 */
	public static function getTwitterFromSettings($settings) {
		if(!$settings['oauth']['consumer']['key'] || !$settings['oauth']['consumer']['secret'] || !$settings['oauth']['token']['key'] || !$settings['oauth']['token']['secret']) {
			throw new ConfigurationException("Missing OAuth keys and/or secrets.", 1362059167);

		}

		$twitter = new Twitter();
		$twitter->setConsumer($settings['oauth']['consumer']['key'], $settings['oauth']['consumer']['secret']);
		$twitter->setToken($settings['oauth']['token']['key'], $settings['oauth']['token']['secret']);

		return $twitter;
	}

	/**
	 * @param array $settings
	 * @return array
	 * @throws ConfigurationException
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
				throw new ConfigurationException("Invalid mode specified.", 1362059199);
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
	 * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
	 */
	public function __construct() {
		$this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('cwtwitter_queries');
	}

	/**
	 * Sets consumer based on key and secret
	 *
	 * @param string $key
	 * @param string $secret
	 * @return void
	 */
	public function setConsumer($key, $secret) {
		$this->consumer = new Consumer($key, $secret);
	}

	/**
	 * Sets token based on key and secret
	 *
	 * @param string $key
	 * @param string $secret
	 * @return void
	 */
	public function setToken($key, $secret) {
		$this->token = new Token($key, $secret);
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

		/** @noinspection PhpUndefinedFieldInspection */
		return $this->getData('search/tweets', $params)->statuses;
	}

	/**
	 * Returns the user object for specified user
	 *
	 * @param string $user
	 * @return \stdClass
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
	 * @throws ConfigurationException
	 * @throws RequestException
	 * @throws \OAuth\Exception
	 */
	protected function getData($path, $params, $method = 'GET') {
		if(!function_exists('curl_init')) {
			throw new ConfigurationException("PHP Curl functions not available on this server", 1362059213);
		}

		if($method === 'GET') {
			if($this->cache->has($this->calculateCacheKey($path, $params))) {
				return $this->cache->get($this->calculateCacheKey($path, $params));
			}
		}

		$request = Request::fromConsumerAndToken($this->consumer, $this->token, $method, $this->api_url.$path.'.json', $params);
		$request->signRequest(new HmacSha1(), $this->consumer, $this->token);

		$hCurl = curl_init($request->toUrl());
		curl_setopt_array($hCurl, array(
			CURLOPT_HTTPHEADER => array($request->toHeader()),
			CURLOPT_RETURNTRANSFER => True,
			CURLOPT_TIMEOUT => 5000,
		));

		$response = curl_exec($hCurl);

		if($response === False) {
			throw new RequestException(sprintf("Error in request: '%s'", curl_error($hCurl)), 1362059229);
		}

		$response = json_decode($response);
		if(isset($response->errors)) {
			$msg = "Error(s) in Request:";
			foreach($response->errors as $error) {
				$msg .= sprintf("\n%d: %s", $error->code, $error->message);
			}
			throw new RequestException($msg, 1362059237);
		}

		if($method == 'GET') {
			$this->cache->set($this->calculateCacheKey($path, $params), $response, array());
		}

		return $response;
	}

	/**
	 * Calculates the cache key
	 *
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	protected function calculateCacheKey($path, $params) {
		return md5(sprintf('%s|%s', $path, implode(',', $params)));
	}
}
