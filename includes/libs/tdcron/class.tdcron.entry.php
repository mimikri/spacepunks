<?php

	/**
	 * tinyCronEntry is part of tdCron. Its a class to parse Cron-Expressions like "1-45 1,2,3 1-30/5 January,February Mon,Tue"
	 * and convert it to an easily useable format.
	 *
	 * The parser is quite powerful and understands pretty much everything you will ever find in a Cron-Expression.
	 *
	 * A Cron-Expression consists of 5 segments:
	 *
	 * <pre>
	 *  .---------------- minute (0 - 59)
	 *  |   .------------- hour (0 - 23)
	 *  |   |   .---------- day of month (1 - 31)
	 *  |   |   |   .------- month (1 - 12)
	 *  |   |   |   |  .----- day of week (0 - 6)
	 *  |   |   |   |  |
	 *  *   *   *   *  *
	 * </pre>
	 *
	 * Each segment can contain values, ranges and intervals. A range is always written as "value1-value2" and
	 * intervals as "value1/value2".
	 *
	 * Of course each segment can contain multiple values seperated by commas.
	 *
	 * Some valid examples:
	 *
	 * <pre>
	 * 1,2,3,4,5
	 * 1-5
	 * 10-20/*
	 * Jan,Feb,Oct
	 * Monday-Friday
	 * 1-10,15,20,40-50/2
	 * </pre>
	 *
	 * The current version of the parser understands all weekdays and month names in german and english!
	 *
	 * Usually you won't need to call this class directly.
	 *
	 * @author	Christian Land <devel@tagdocs.de>
	 * @package	tdCron
	 * @copyright	Copyright (c) 2010-2018, Christian Land / tagdocs.de
	 * @license	http://opensource.org/licenses/MIT	MIT License
	 * @version	1.0.0
	 */

	class tdCronEntry {

		/**
		 * The parsed cron-expression.
		 * @var mixed
		 */
		static private array $cron		= [];

		/**
		 * Ranges.
		 * @var mixed
		 */
		static private array $ranges		= [
      IDX_MINUTE		=> ['min'	=> 0, 'max'	=> 59],
      // Minutes
      IDX_HOUR		=> ['min'	=> 0, 'max'	=> 23],
      // Hours
      IDX_DAY			=> ['min'	=> 1, 'max'	=> 31],
      // Days
      IDX_MONTH		=> ['min'	=> 1, 'max'	=> 12],
      // Months
      IDX_WEEKDAY		=> ['min'	=> 0, 'max'	=> 7],
  ];	// Weekdays

		/**
		 * Named intervals.
		 * @var mixed
		 */
		static private array $intervals	= ['@yearly'	=> '0 0 1 1 *', '@annualy'	=> '0 0 1 1 *', '@monthly'	=> '0 0 1 * *', '@weekly'	=> '0 0 * * 0', '@midnight'	=> '0 0 * * *', '@daily'	=> '0 0 * * *', '@hourly'	=> '0 * * * *'];


		/**
		 * Possible keywords for months/weekdays.
		 * @var mixed
		 */
		static private array $keywords	= [IDX_MONTH	=> ['/(january|januar|jan)/i'			=> 1, '/(february|februar|feb)/i'			=> 2, '/(march|maerz|m�rz|mar|mae|m�r)/i'		=> 3, '/(april|apr)/i'				=> 4, '/(may|mai)/i'					=> 5, '/(june|juni|jun)/i'				=> 6, '/(july|juli|jul)/i'				=> 7, '/(august|aug)/i'				=> 8, '/(september|sep)/i'				=> 9, '/(october|oktober|okt|oct)/i'			=> 10, '/(november|nov)/i'				=> 11, '/(december|dezember|dec|dez)/i'		=> 12], IDX_WEEKDAY	=> ['/(sunday|sonntag|sun|son|su|so)/i'		=> 0, '/(monday|montag|mon|mo)/i'			=> 1, '/(tuesday|dienstag|die|tue|tu|di)/i'		=> 2, '/(wednesdays|mittwoch|mit|wed|we|mi)/i'	=> 3, '/(thursday|donnerstag|don|thu|th|do)/i'	=> 4, '/(friday|freitag|fre|fri|fr)/i'		=> 5, '/(saturday|samstag|sam|sat|sa)/i'		=> 6]];

		/**
		 * parseExpression() analyses crontab-expressions like "* * 1,2,3 * mon,tue" and returns an array
		 * containing all values. If it can't be parsed, an exception is thrown.
		 *
		 * @access		public
		 * @param		string		$expression	The cron-expression to parse.
		 * @return		mixed
		 */

		static public function parse($expression) {

			// Convert named expressions if neccessary

			if (str_starts_with($expression, '@')) {

				$expression	= strtr($expression, self::$intervals);

				if (str_starts_with($expression, '@')) {

					// Oops... unknown named interval!?!!
					throw new Exception('Unknown named interval ['.$expression.']', 10000);

				}

			}

			// Next basic check... do we have 5 segments?

			$cron	= explode(' ',$expression);

			if (count($cron) <> 5) {

				// No... we haven't...
				throw new Exception('Wrong number of segments in expression. Expected: 5, Found: '.count($cron), 10001);

			} else {

				// Yup, 5 segments... lets see if we can work with them

				foreach ($cron as $idx=>$segment) {

					try {

						$dummy[$idx]	= self::expandSegment($idx, $segment);

					} catch (Exception $e) {

						throw $e;

					}

				}

			}

			return $dummy;

		}

		/**
		 * expandSegment() analyses a single segment
		 *
		 * @access		public
		 * @param		void
		 * @return		void
		 */

		static private function expandSegment(int $idx, string $segment): array {

			// Store original segment for later use

			$osegment	= $segment;

			// Replace months/weekdays like "January", "February", etc. with numbers

	                if (isset(self::$keywords[$idx])) {

	                	$segment	= preg_replace( array_keys(self::$keywords[$idx]),
	                					array_values(self::$keywords[$idx]),
	                					$segment				);

			}

			// Replace wildcards

			if (str_starts_with((string) $segment, '*')) {

				$segment	= preg_replace('/^\*(\/\d+)?$/i',
								self::$ranges[$idx]['min'].'-'.self::$ranges[$idx]['max'].'$1',
								(string) $segment);

			}

			// Make sure that nothing unparsed is left :)

			$dummy		= preg_replace('/[0-9\-\/\,]/','',(string) $segment);

			if (!empty($dummy)) {

				// Ohoh.... thats not good :-)
				throw new Exception('Failed to parse segment: '.$osegment, 10002);

			}

			// At this point our string should be OK - lets convert it to an array

			$result		= [];
			$atoms		= explode(',',(string) $segment);

			foreach ($atoms as $curatom) {

				$result	= array_merge($result, self::parseAtom($curatom));

			}

			// Get rid of duplicates and sort the array

			$result		= array_unique($result);
			sort($result);

			// Check for invalid values

			if ($idx == IDX_WEEKDAY) {

				if (end($result) == 7) {

					if (reset($result) <> 0) {
						array_unshift($result, 0);
					}

					array_pop($result);

				}

			}

			foreach ($result as $key=>$value) {

				if (($value < self::$ranges[$idx]['min']) || ($value > self::$ranges[$idx]['max'])) {
					throw new Exception('Failed to parse segment, invalid value ['.$value.']: '.$osegment, 10003);
				}

			}

			return $result;

		}

		/**
		 * parseAtom() analyses a single segment
		 *
		 * @access		public
		 * @param		string		$atom		The segment to parse
		 * @return		array
		 */

		static private function parseAtom(string $atom): array {

			$expanded	= [];

			if (preg_match('/^(\d+)-(\d+)(\/(\d+))?/i', $atom, $matches)) {

				$low	= $matches[1];
				$high	= $matches[2];

				if ($low > $high) {
					[$low, $high]	= [$high, $low];
				}

				$step	= $matches[4] ?? 1;

				for($i = $low; $i <= $high; $i += $step) {
					$expanded[]	= (int)$i;
				}

			} else {

				$expanded[]	= (int)$atom;

			}

			$expanded2	= array_unique($expanded);

			return $expanded;

		}

	}