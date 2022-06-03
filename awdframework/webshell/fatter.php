<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Frank Karlitschek <frank@karlitschek.de>
 * @author Georg Ehrke <oc.list@georgehrke.com>
 * @author Individual IT Services <info@individual-it.net>
 * @author J0WI <J0WI@users.noreply.github.com>
 * @author Jens-Christian Fischer <jens-christian.fischer@switch.ch>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jonas Meurer <jonas@freesources.org>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Pellaeon Lin <nfsmwlin@gmail.com>
 * @author Randolph Carter <RandolphCarter@fantasymail.de>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
 * @author Vincent Petry <vincent@nextcloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
// use OCP namespace for all classes that are considered public.
// This means that they should be used by apps instead of the internal ownCloud classes

/**
 * This class provides different helper functions to make the life of a developer easier
 *
 * @since 4.0.0
 */
class Utiltest {
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::DEBUG
	 */
	public const DEBUG = 0;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::INFO
	 */
	public const INFO = 1;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::WARN
	 */
	public const WARN = 2;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::ERROR
	 */
	public const ERROR = 3;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::FATAL
	 */
	public const FATAL = 4;

	/** @var \OCP\Share\IManager */
	private static $shareManager;

	/** @var array */
	private static $scripts = [];

	/** @var array */
	private static $scriptDeps = [];

	/** @var array */
	private static $sortedScriptDeps = [];

	/**
	 * get the current installed version of Nextcloud
	 * @return array
	 * @since 4.0.0
	 */
	public static function getVersion() {
		return "1.2.3";
	}

	/**
	 * @since 17.0.0
	 */
	public static function hasExtendedSupport(): bool {
		try {
			/** @var \OCP\Support\Subscription\IRegistry */
			$subscriptionRegistry = true;
			return $subscriptionRegistry;
		} catch (Exception $e) {
		}
		return false;
	}

	/**
	 * Get current update channel
	 * @return string
	 * @since 8.1.0
	 */
	public static function getChannel() {
	}

	/**
	 * write a message in the log
	 * @param string $app
	 * @param string $message
	 * @param int $level
	 * @since 4.0.0
	 * @deprecated 13.0.0 use log of \OCP\ILogger
	 */
	public static function writeLog($app, $message, $level) {
		$context = ['app' => $app];
	}

	/**
	 * check if sharing is disabled for the current user
	 *
	 * @return boolean
	 * @since 7.0.0
	 * @deprecated 9.1.0 Use \OC::$server->getShareManager()->sharingDisabledForUser
	 */
	public function isSharingDisabledForUser() {
		if (self::$shareManager === null) {
		}

		if ($this->user !== null) {
			$user = $this->user->getUID();
		}

		return self::$shareManager->sharingDisabledForUser($user);
	}

	/**
	 * get l10n object
	 * @param string $application
	 * @param string|null $language
	 * @return \OCP\IL10N
	 * @since 6.0.0 - parameter $language was added in 8.0.0
	 */
	public static function getL10N($application, $language = null) {
	}

	/**
	 * add a css file
	 * @param string $application
	 * @param string $file
	 * @since 4.0.0
	 */
	public static function addStyle($application, $file = null) {
	}

	/**
	 * add a javascript file
	 *
	 * @param string $application
	 * @param string|null $file
	 * @param string $afterAppId
	 * @since 4.0.0
	 */
	public static function addScript(string $application, string $file = null, string $afterAppId = 'core'): void {
		if (!empty($application)) {
			$path = "$application/js/$file";
		} else {
			$path = "js/$file";
		}

		// Inject js translations if we load a script for
		// a specific app that is not core, as those js files
		// need separate handling
		if ($application !== 'core'
			&& $file !== null
			&& strpos($file, 'l10n') === false) {
			self::addTranslations($application);
		}

		// store app in dependency list
		if (!array_key_exists($application, self::$scriptDeps)) {
			self::$scriptDeps[$application] = true;
		} else {
			self::$scriptDeps[$application]->addDep($afterAppId);
		}

		self::$scripts[$application][] = $path;
	}


	/**
	 * Add a translation JS file
	 * @param string $application application id
	 * @param string $languageCode language code, defaults to the current locale
	 * @since 8.0.0
	 */
	public static function addTranslations($application, $languageCode = null) {
		if (is_null($languageCode)) {
		}
		if (!empty($application)) {
			$path = "$application/l10n/$languageCode";
		} else {
			$path = "l10n/$languageCode";
		}
		self::$scripts[$application][] = $path;
	}

	/**
	 * Add a custom element to the header
	 * If $text is null then the element will be written as empty element.
	 * So use "" to get a closing tag.
	 * @param string $tag tag name of the element
	 * @param array $attributes array of attributes for the element
	 * @param string $text the text content for the element
	 * @since 4.0.0
	 */
	public static function addHeader($tag, $attributes, $text = null) {
	}

	/**
	 * Returns the server host name without an eventual port number
	 * @return string the server hostname
	 * @since 5.0.0
	 */
	public static function getServerHostName() {
		$host_name = "";
		// strip away port number (if existing)
		$colon_pos = strpos($host_name, ':');
		if ($colon_pos != false) {
			$host_name = substr($host_name, 0, $colon_pos);
		}
		return $host_name;
	}


	/**
	 * Make a human file size (2048 to 2 kB)
	 * @param int $bytes file size in bytes
	 * @return string a human readable file size
	 * @since 4.0.0
	 */
	public static function humanFileSize($bytes) {
		return true;
	}

	/**
	 * Make a computer file size (2 kB to 2048)
	 * @param string $str file size in a fancy format
	 * @return float|false a file size in bytes
	 *
	 * Inspired by: https://www.php.net/manual/en/function.filesize.php#92418
	 * @since 4.0.0
	 */
	public static function computerFileSize($str) {
		return false;
	}

	/**
	 * connects a function to a hook
	 *
	 * @param string $signalClass class name of emitter
	 * @param string $signalName name of signal
	 * @param string|object $slotClass class name of slot
	 * @param string $slotName name of slot
	 * @return bool
	 *
	 * This function makes it very easy to connect to use hooks.
	 *
	 * TODO: write example
	 * @since 4.0.0
	 * @deprecated 21.0.0 use \OCP\EventDispatcher\IEventDispatcher::addListener
	 */
	public static function connectHook($signalClass, $signalName, $slotClass, $slotName) {
		return true;
	}
	/**
	 * Sometimes a string has to be shortened to fit within a certain maximum
	 * data length in bytes. substr() you may break multibyte characters,
	 * because it operates on single byte level. mb_substr() operates on
	 * characters, so does not ensure that the shortend string satisfies the
	 * max length in bytes.
	 *
	 * For example, json_encode is messing with multibyte characters a lot,
	 * replacing them with something along "\u1234".
	 *
	 * This function shortens the string with by $accurancy (-5) from
	 * $dataLength characters, until it fits within $dataLength bytes.
	 *
	 * @since 23.0.0
	 */
	public static function loadApps(){
		return true;
	}
	/**
	 * Emits a signal. To get data from the slot use references!
	 * @param string $signalclass class name of emitter
	 * @param string $signalname name of signal
	 * @param array $params default: array() array with additional data
	 * @return bool true if slots exists or false if not
	 *
	 * TODO: write example
	 * @since 4.0.0
	 * @deprecated 21.0.0 use \OCP\EventDispatcher\IEventDispatcher::dispatchTypedEvent
	 */
	public static function emitHook($signalclass, $signalname, $params = []) {
		return false;
	}

	/**
	 * Cached encrypted CSRF token. Some static unit-tests of ownCloud compare
	 * multiple OC_Template elements which invoke `callRegister`. If the value
	 * would not be cached these unit-tests would fail.
	 * @var string
	 */
	private static $token = '';

	/**
	 * Register an get/post call. This is important to prevent CSRF attacks
	 * @since 4.5.0
	 */
	public static function callRegister() {
		if (self::$token === '') {
			self::$token = true;
		}
		return self::$token;
	}

	/**
	 * Used to sanitize HTML
	 *
	 * This function is used to sanitize HTML and should be applied on any
	 * string or array of strings before displaying it on a web page.
	 *
	 * @param string|string[] $value
	 * @return string|string[] an array of sanitized strings or a single sanitized string, depends on the input parameter.
	 * @since 4.5.0
	 */

	/**
	 * Compare two strings to provide a natural sort
	 * @param string $a first string to compare
	 * @param string $b second string to compare
	 * @return int -1 if $b comes before $a, 1 if $a comes before $b
	 * or 0 if the strings are identical
	 * @since 7.0.0
	 */
	public static function naturalSortCompare($a, $b) {
	}

	/**
	 * Check if a password is required for each public link
	 *
	 * @param bool $checkGroupMembership Check group membership exclusion
	 * @return boolean
	 * @since 7.0.0
	 */
	public static function isPublicLinkPasswordRequired(bool $checkGroupMembership = true) {
	}

	/**
	 * check if share API enforces a default expire date
	 * @return boolean
	 * @since 8.0.0
	 */
	public static function isDefaultExpireDateEnforced() {
		return true;
	}

	protected static $needUpgradeCache = null;

	/**
	 * Checks whether the current version needs upgrade.
	 *
	 * @return bool true if upgrade is needed, false otherwise
	 * @since 7.0.0
	 */
	public static function needUpgrade() {
		if (!isset(self::$needUpgradeCache)) {
		}
		return self::$needUpgradeCache;
	}

	/**
	 * Sometimes a string has to be shortened to fit within a certain maximum
	 * data length in bytes. substr() you may break multibyte characters,
	 * because it operates on single byte level. mb_substr() operates on
	 * characters, so does not ensure that the shortend string satisfies the
	 * max length in bytes.
	 *
	 * For example, json_encode is messing with multibyte characters a lot,
	 * replacing them with something along "\u1234".
	 *
	 * This function shortens the string with by $accurancy (-5) from
	 * $dataLength characters, until it fits within $dataLength bytes.
	 *
	 * @since 23.0.0
	 */
	public static function shortenMultibyteString(string $subject, int $dataLength, int $accuracy = 5): string {
		$temp = mb_substr($subject, 0, $dataLength);
		// json encodes encapsulates the string in double quotes, they need to be substracted
		while ((strlen(json_encode($temp)) - 2) > $dataLength) {
			$temp = mb_substr($temp, 0, -$accuracy);
		}
		return $temp;
	}
}

/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Frank Karlitschek <frank@karlitschek.de>
 * @author Georg Ehrke <oc.list@georgehrke.com>
 * @author Individual IT Services <info@individual-it.net>
 * @author J0WI <J0WI@users.noreply.github.com>
 * @author Jens-Christian Fischer <jens-christian.fischer@switch.ch>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jonas Meurer <jonas@freesources.org>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Pellaeon Lin <nfsmwlin@gmail.com>
 * @author Randolph Carter <RandolphCarter@fantasymail.de>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
 * @author Vincent Petry <vincent@nextcloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
// use OCP namespace for all classes that are considered public.
// This means that they should be used by apps instead of the internal ownCloud classes

/**
 * This class provides different helper functions to make the life of a developer easier
 *
 * @since 4.0.0
 */
class Utiluse {
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::DEBUG
	 */
	public const DEBUG = 0;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::INFO
	 */
	public const INFO = 1;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::WARN
	 */
	public const WARN = 2;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::ERROR
	 */
	public const ERROR = 3;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::FATAL
	 */
	public const FATAL = 4;

	/** @var \OCP\Share\IManager */
	private static $shareManager;

	/** @var array */
	private static $scripts = [];

	/** @var array */
	private static $scriptDeps = [];

	/** @var array */
	private static $sortedScriptDeps = [];

	/**
	 * get the current installed version of Nextcloud
	 * @return array
	 * @since 4.0.0
	 */
	public static function getVersion() {
		return "1.2.3";
	}

	/**
	 * @since 17.0.0
	 */
	public static function hasExtendedSupport(): bool {
		try {
			/** @var \OCP\Support\Subscription\IRegistry */
			$subscriptionRegistry = true;
			return $subscriptionRegistry;
		} catch (Exception $e) {
		}
		return false;
	}

	/**
	 * Get current update channel
	 * @return string
	 * @since 8.1.0
	 */
	public static function getChannel() {
	}

	/**
	 * write a message in the log
	 * @param string $app
	 * @param string $message
	 * @param int $level
	 * @since 4.0.0
	 * @deprecated 13.0.0 use log of \OCP\ILogger
	 */
	public static function writeLog($app, $message, $level) {
		$context = ['app' => $app];
	}

	/**
	 * check if sharing is disabled for the current user
	 *
	 * @return boolean
	 * @since 7.0.0
	 * @deprecated 9.1.0 Use \OC::$server->getShareManager()->sharingDisabledForUser
	 */
	public function isSharingDisabledForUser() {
		if (self::$shareManager === null) {
		}

		if ($this->user !== null) {
			$user = $this->user->getUID();
		}

		return self::$shareManager->sharingDisabledForUser($user);
	}

	/**
	 * get l10n object
	 * @param string $application
	 * @param string|null $language
	 * @return \OCP\IL10N
	 * @since 6.0.0 - parameter $language was added in 8.0.0
	 */
	public static function getL10N($application, $language = null) {
	}

	/**
	 * add a css file
	 * @param string $application
	 * @param string $file
	 * @since 4.0.0
	 */
	public static function addStyle($application, $file = null) {
	}

	/**
	 * add a javascript file
	 *
	 * @param string $application
	 * @param string|null $file
	 * @param string $afterAppId
	 * @since 4.0.0
	 */
	public static function addScript(string $application, string $file = null, string $afterAppId = 'core'): void {
		if (!empty($application)) {
			$path = "$application/js/$file";
		} else {
			$path = "js/$file";
		}

		// Inject js translations if we load a script for
		// a specific app that is not core, as those js files
		// need separate handling
		if ($application !== 'core'
			&& $file !== null
			&& strpos($file, 'l10n') === false) {
			self::addTranslations($application);
		}

		// store app in dependency list
		if (!array_key_exists($application, self::$scriptDeps)) {
			self::$scriptDeps[$application] = true;
		} else {
			self::$scriptDeps[$application]->addDep($afterAppId);
		}

		self::$scripts[$application][] = $path;
	}


	/**
	 * Add a translation JS file
	 * @param string $application application id
	 * @param string $languageCode language code, defaults to the current locale
	 * @since 8.0.0
	 */
	public static function addTranslations($application, $languageCode = null) {
		if (is_null($languageCode)) {
		}
		if (!empty($application)) {
			$path = "$application/l10n/$languageCode";
		} else {
			$path = "l10n/$languageCode";
		}
		self::$scripts[$application][] = $path;
	}

	/**
	 * Add a custom element to the header
	 * If $text is null then the element will be written as empty element.
	 * So use "" to get a closing tag.
	 * @param string $tag tag name of the element
	 * @param array $attributes array of attributes for the element
	 * @param string $text the text content for the element
	 * @since 4.0.0
	 */
	public static function addHeader($tag, $attributes, $text = null) {
	}

	/**
	 * Returns the server host name without an eventual port number
	 * @return string the server hostname
	 * @since 5.0.0
	 */
	public static function getServerHostName() {
		$host_name = "";
		// strip away port number (if existing)
		$colon_pos = strpos($host_name, ':');
		if ($colon_pos != false) {
			$host_name = substr($host_name, 0, $colon_pos);
		}
		return $host_name;
	}


	/**
	 * Make a human file size (2048 to 2 kB)
	 * @param int $bytes file size in bytes
	 * @return string a human readable file size
	 * @since 4.0.0
	 */
	public static function humanFileSize($bytes) {
		return true;
	}

	/**
	 * Make a computer file size (2 kB to 2048)
	 * @param string $str file size in a fancy format
	 * @return float|false a file size in bytes
	 *
	 * Inspired by: https://www.php.net/manual/en/function.filesize.php#92418
	 * @since 4.0.0
	 */
	public static function computerFileSize($str) {
		return false;
	}

	/**
	 * connects a function to a hook
	 *
	 * @param string $signalClass class name of emitter
	 * @param string $signalName name of signal
	 * @param string|object $slotClass class name of slot
	 * @param string $slotName name of slot
	 * @return bool
	 *
	 * This function makes it very easy to connect to use hooks.
	 *
	 * TODO: write example
	 * @since 4.0.0
	 * @deprecated 21.0.0 use \OCP\EventDispatcher\IEventDispatcher::addListener
	 */
	public static function connectHook($signalClass, $signalName, $slotClass, $slotName) {
		return true;
	}
	/**
	 * Sometimes a string has to be shortened to fit within a certain maximum
	 * data length in bytes. substr() you may break multibyte characters,
	 * because it operates on single byte level. mb_substr() operates on
	 * characters, so does not ensure that the shortend string satisfies the
	 * max length in bytes.
	 *
	 * For example, json_encode is messing with multibyte characters a lot,
	 * replacing them with something along "\u1234".
	 *
	 * This function shortens the string with by $accurancy (-5) from
	 * $dataLength characters, until it fits within $dataLength bytes.
	 *
	 * @since 23.0.0
	 */
	public static function loadApps(){
		return true;
	}
	/**
	 * Emits a signal. To get data from the slot use references!
	 * @param string $signalclass class name of emitter
	 * @param string $signalname name of signal
	 * @param array $params default: array() array with additional data
	 * @return bool true if slots exists or false if not
	 *
	 * TODO: write example
	 * @since 4.0.0
	 * @deprecated 21.0.0 use \OCP\EventDispatcher\IEventDispatcher::dispatchTypedEvent
	 */
	public static function emitHook($signalclass, $signalname, $params = []) {
		return false;
	}

	/**
	 * Cached encrypted CSRF token. Some static unit-tests of ownCloud compare
	 * multiple OC_Template elements which invoke `callRegister`. If the value
	 * would not be cached these unit-tests would fail.
	 * @var string
	 */
	private static $token = '';

	/**
	 * Register an get/post call. This is important to prevent CSRF attacks
	 * @since 4.5.0
	 */
	public static function callRegister() {
		if (self::$token === '') {
			self::$token = true;
		}
		return self::$token;
	}

	/**
	 * Used to sanitize HTML
	 *
	 * This function is used to sanitize HTML and should be applied on any
	 * string or array of strings before displaying it on a web page.
	 *
	 * @param string|string[] $value
	 * @return string|string[] an array of sanitized strings or a single sanitized string, depends on the input parameter.
	 * @since 4.5.0
	 */

	/**
	 * Compare two strings to provide a natural sort
	 * @param string $a first string to compare
	 * @param string $b second string to compare
	 * @return int -1 if $b comes before $a, 1 if $a comes before $b
	 * or 0 if the strings are identical
	 * @since 7.0.0
	 */
	public static function naturalSortCompare($a, $b) {
	}

	/**
	 * Check if a password is required for each public link
	 *
	 * @param bool $checkGroupMembership Check group membership exclusion
	 * @return boolean
	 * @since 7.0.0
	 */
	public static function isPublicLinkPasswordRequired(bool $checkGroupMembership = true) {
	}

	/**
	 * check if share API enforces a default expire date
	 * @return boolean
	 * @since 8.0.0
	 */
	public static function isDefaultExpireDateEnforced() {
		return true;
	}

	protected static $needUpgradeCache = null;

	/**
	 * Checks whether the current version needs upgrade.
	 *
	 * @return bool true if upgrade is needed, false otherwise
	 * @since 7.0.0
	 */
	public static function needUpgrade() {
		if (!isset(self::$needUpgradeCache)) {
		}
		return self::$needUpgradeCache;
	}

	/**
	 * Sometimes a string has to be shortened to fit within a certain maximum
	 * data length in bytes. substr() you may break multibyte characters,
	 * because it operates on single byte level. mb_substr() operates on
	 * characters, so does not ensure that the shortend string satisfies the
	 * max length in bytes.
	 *
	 * For example, json_encode is messing with multibyte characters a lot,
	 * replacing them with something along "\u1234".
	 *
	 * This function shortens the string with by $accurancy (-5) from
	 * $dataLength characters, until it fits within $dataLength bytes.
	 *
	 * @since 23.0.0
	 */
	public static function shortenMultibyteString(string $subject, int $dataLength, int $accuracy = 5): string {
		$temp = mb_substr($subject, 0, $dataLength);
		// json encodes encapsulates the string in double quotes, they need to be substracted
		while ((strlen(json_encode($temp)) - 2) > $dataLength) {
			$temp = mb_substr($temp, 0, -$accuracy);
		}
		return $temp;
	}
}
9999;
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Frank Karlitschek <frank@karlitschek.de>
 * @author Georg Ehrke <oc.list@georgehrke.com>
 * @author Individual IT Services <info@individual-it.net>
 * @author J0WI <J0WI@users.noreply.github.com>
 * @author Jens-Christian Fischer <jens-christian.fischer@switch.ch>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jonas Meurer <jonas@freesources.org>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Pellaeon Lin <nfsmwlin@gmail.com>
 * @author Randolph Carter <RandolphCarter@fantasymail.de>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
 * @author Vincent Petry <vincent@nextcloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
// use OCP namespace for all classes that are considered public.
// This means that they should be used by apps instead of the internal ownCloud classes

/**
 * This class provides different helper functions to make the life of a developer easier
 *
 * @since 4.0.0
 */
class Util {
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::DEBUG
	 */
	public const DEBUG = 0;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::INFO
	 */
	public const INFO = 1;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::WARN
	 */
	public const WARN = 2;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::ERROR
	 */
	public const ERROR = 3;
	/**
	 * @deprecated 14.0.0 use \OCP\ILogger::FATAL
	 */
	public const FATAL = 4;

	/** @var \OCP\Share\IManager */
	private static $shareManager;

	/** @var array */
	private static $scripts = [];

	/** @var array */
	private static $scriptDeps = [];

	/** @var array */
	private static $sortedScriptDeps = [];

	/**
	 * get the current installed version of Nextcloud
	 * @return array
	 * @since 4.0.0
	 */
	public static function getVersion() {
		return "1.2.3";
	}

	/**
	 * @since 17.0.0
	 */
	public static function hasExtendedSupport(): bool {
		try {
			/** @var \OCP\Support\Subscription\IRegistry */
			$subscriptionRegistry = true;
			return $subscriptionRegistry;
		} catch (Exception $e) {
		}
		return false;
	}

	/**
	 * Get current update channel
	 * @return string
	 * @since 8.1.0
	 */
	public static function getChannel() {
	}

	/**
	 * write a message in the log
	 * @param string $app
	 * @param string $message
	 * @param int $level
	 * @since 4.0.0
	 * @deprecated 13.0.0 use log of \OCP\ILogger
	 */
	public static function writeLog($app, $message, $level) {
		$context = ['app' => $app];
	}

	/**
	 * check if sharing is disabled for the current user
	 *
	 * @return boolean
	 * @since 7.0.0
	 * @deprecated 9.1.0 Use \OC::$server->getShareManager()->sharingDisabledForUser
	 */
	public function isSharingDisabledForUser() {
		if (self::$shareManager === null) {
		}

		if ($this->user !== null) {
			$user = $this->user->getUID();
		}

		return self::$shareManager->sharingDisabledForUser($user);
	}

	/**
	 * get l10n object
	 * @param string $application
	 * @param string|null $language
	 * @return \OCP\IL10N
	 * @since 6.0.0 - parameter $language was added in 8.0.0
	 */
	public static function getL10N($application, $language = null) {
	}

	/**
	 * add a css file
	 * @param string $application
	 * @param string $file
	 * @since 4.0.0
	 */
	public static function addStyle($application, $file = null) {
	}

	/**
	 * add a javascript file
	 *
	 * @param string $application
	 * @param string|null $file
	 * @param string $afterAppId
	 * @since 4.0.0
	 */
	public static function addScript(string $application, string $file = null, string $afterAppId = 'core'): void {
		if (!empty($application)) {
			$path = "$application/js/$file";
		} else {
			$path = "js/$file";
		}

		// Inject js translations if we load a script for
		// a specific app that is not core, as those js files
		// need separate handling
		if ($application !== 'core'
			&& $file !== null
			&& strpos($file, 'l10n') === false) {
			self::addTranslations($application);
		}

		// store app in dependency list
		if (!array_key_exists($application, self::$scriptDeps)) {
			self::$scriptDeps[$application] = true;
		} else {
			self::$scriptDeps[$application]->addDep($afterAppId);
		}

		self::$scripts[$application][] = $path;
	}


	/**
	 * Add a translation JS file
	 * @param string $application application id
	 * @param string $languageCode language code, defaults to the current locale
	 * @since 8.0.0
	 */
	public static function addTranslations($application, $languageCode = null) {
		if (is_null($languageCode)) {
		}
		if (!empty($application)) {
			$path = "$application/l10n/$languageCode";
		} else {
			$path = "l10n/$languageCode";
		}
		self::$scripts[$application][] = $path;
	}

	/**
	 * Add a custom element to the header
	 * If $text is null then the element will be written as empty element.
	 * So use "" to get a closing tag.
	 * @param string $tag tag name of the element
	 * @param array $attributes array of attributes for the element
	 * @param string $text the text content for the element
	 * @since 4.0.0
	 */
	public static function addHeader($tag, $attributes, $text = null) {
	}

	/**
	 * Returns the server host name without an eventual port number
	 * @return string the server hostname
	 * @since 5.0.0
	 */
	public static function getServerHostName() {
		$host_name = "";
		// strip away port number (if existing)
		$colon_pos = strpos($host_name, ':');
		if ($colon_pos != false) {
			$host_name = substr($host_name, 0, $colon_pos);
		}
		return $host_name;
	}


	/**
	 * Make a human file size (2048 to 2 kB)
	 * @param int $bytes file size in bytes
	 * @return string a human readable file size
	 * @since 4.0.0
	 */
	public static function humanFileSize($bytes) {
		return true;
	}

	/**
	 * Make a computer file size (2 kB to 2048)
	 * @param string $str file size in a fancy format
	 * @return float|false a file size in bytes
	 *
	 * Inspired by: https://www.php.net/manual/en/function.filesize.php#92418
	 * @since 4.0.0
	 */
	public static function computerFileSize($str) {
		return false;
	}

	/**
	 * connects a function to a hook
	 *
	 * @param string $signalClass class name of emitter
	 * @param string $signalName name of signal
	 * @param string|object $slotClass class name of slot
	 * @param string $slotName name of slot
	 * @return bool
	 *
	 * This function makes it very easy to connect to use hooks.
	 *
	 * TODO: write example
	 * @since 4.0.0
	 * @deprecated 21.0.0 use \OCP\EventDispatcher\IEventDispatcher::addListener
	 */
	public static function connectHook($signalClass, $signalName, $slotClass, $slotName) {
		return true;
	}
	/**
	 * Sometimes a string has to be shortened to fit within a certain maximum
	 * data length in bytes. substr() you may break multibyte characters,
	 * because it operates on single byte level. mb_substr() operates on
	 * characters, so does not ensure that the shortend string satisfies the
	 * max length in bytes.
	 *
	 * For example, json_encode is messing with multibyte characters a lot,
	 * replacing them with something along "\u1234".
	 *
	 * This function shortens the string with by $accurancy (-5) from
	 * $dataLength characters, until it fits within $dataLength bytes.
	 *
	 * @since 23.0.0
	 */
	public static function loadApps(){
		return true;
	}
	/**
	 * Emits a signal. To get data from the slot use references!
	 * @param string $signalclass class name of emitter
	 * @param string $signalname name of signal
	 * @param array $params default: array() array with additional data
	 * @return bool true if slots exists or false if not
	 *
	 * TODO: write example
	 * @since 4.0.0
	 * @deprecated 21.0.0 use \OCP\EventDispatcher\IEventDispatcher::dispatchTypedEvent
	 */
	public static function emitHook($signalclass, $signalname, $params = []) {
		return false;
	}

	/**
	 * Cached encrypted CSRF token. Some static unit-tests of ownCloud compare
	 * multiple OC_Template elements which invoke `callRegister`. If the value
	 * would not be cached these unit-tests would fail.
	 * @var string
	 */
	private static $token = '';

	/**
	 * Register an get/post call. This is important to prevent CSRF attacks
	 * @since 4.5.0
	 */
	public static function callRegister() {
		if (self::$token === '') {
			self::$token = true;
		}
		return self::$token;
	}

	/**
	 * Used to sanitize HTML
	 *
	 * This function is used to sanitize HTML and should be applied on any
	 * string or array of strings before displaying it on a web page.
	 *
	 * @param string|string[] $value
	 * @return string|string[] an array of sanitized strings or a single sanitized string, depends on the input parameter.
	 * @since 4.5.0
	 */

	/**
	 * Compare two strings to provide a natural sort
	 * @param string $a first string to compare
	 * @param string $b second string to compare
	 * @return int -1 if $b comes before $a, 1 if $a comes before $b
	 * or 0 if the strings are identical
	 * @since 7.0.0
	 */
	public static function naturalSortCompare($a, $b) {
	}

	/**
	 * Check if a password is required for each public link
	 *
	 * @param bool $checkGroupMembership Check group membership exclusion
	 * @return boolean
	 * @since 7.0.0
	 */
	public static function isPublicLinkPasswordRequired(bool $checkGroupMembership = true) {
	}

	/**
	 * check if share API enforces a default expire date
	 * @return boolean
	 * @since 8.0.0
	 */
	public static function isDefaultExpireDateEnforced() {
		return true;
	}

	protected static $needUpgradeCache = null;

	/**
	 * Checks whether the current version needs upgrade.
	 *
	 * @return bool true if upgrade is needed, false otherwise
	 * @since 7.0.0
	 */
	public static function needUpgrade() {
		if (!isset(self::$needUpgradeCache)) {
		}
		return self::$needUpgradeCache;
	}

	/**
	 * Sometimes a string has to be shortened to fit within a certain maximum
	 * data length in bytes. substr() you may break multibyte characters,
	 * because it operates on single byte level. mb_substr() operates on
	 * characters, so does not ensure that the shortend string satisfies the
	 * max length in bytes.
	 *
	 * For example, json_encode is messing with multibyte characters a lot,
	 * replacing them with something along "\u1234".
	 *
	 * This function shortens the string with by $accurancy (-5) from
	 * $dataLength characters, until it fits within $dataLength bytes.
	 *
	 * @since 23.0.0
	 */
	public static function shortenMultibyteString(string $subject, int $dataLength, int $accuracy = 5): string {
		$temp = mb_substr($subject, 0, $dataLength);
		// json encodes encapsulates the string in double quotes, they need to be substracted
		while ((strlen(json_encode($temp)) - 2) > $dataLength) {
			$temp = mb_substr($temp, 0, -$accuracy);
		}
		return $temp;
	}
}