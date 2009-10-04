<?php
	#
	# lib_santize.php
	#
	# A PHP input filtering library
	#
	# $Id$
	#
	# *article urls here*
	#
	# By Cal Henderson <cal@iamcal.com>
	# This code is licensed under a Creative Commons Attribution-ShareAlike 2.5 License
	# http://creativecommons.org/licenses/by-sa/2.5/
	#

	#
	#
	# !!!!!!!!!!!!!!!!!! DO NOT USE THIS - IT IS A WORK IN PROGRESS !!!!!!!!!!!!!!!!!!
	#
	#

	##############################################################################

	#
	# what to do when invalid 
	#

	define('SANATIZE_INVALID_STRIP',	1); # strip out the offending bytes
	define('SANATIZE_INVALID_REPLACE',	2); # replace offending bytes with the replacement character
	define('SANATIZE_INVALID_THROW',	3); # throw an error
	define('SANATIZE_INVALID_CONVERT',	4); # convert from another encoding

	$GLOBALS[sanatize_mode]		= SANATIZE_INVALID_STRIP;
	$GLOBALS[sanatize_replace]	= ord('?');
	$GLOBALS[sanatize_convert_from]	= 'ISO-8859-1'; # Latin-1

	##############################################################################

	function sanitize($input, $type, $default=null, $more=null){

		#
		# if we get a null in, always return a null
		#

		if ($type == 'isset') return sset($input);

		if (!isset($input)) return $default;

		switch ($type){

			case 'str':
				return sanitize_string($input, false);

			case 'str_multi':
				return sanitize_string($input, true);

			case 'int32':
				return sanitize_int32($input);

			case 'int64':
				return sanitize_int64($input);

			case 'html':
				# this needs to do class_exists('lib_filter')
				die("not implemented");
			
			case 'bool':
				return $input ? true : false;

			case 'rx':
				if (preg_match($more, $input)) return $input;
				return $default;

			case 'in':
				foreach ($more as $match){
					if ($input === $match){
						return $input;
					}
				}
				return $default;
		}

		die("Unknown data conversion type: $type");
	}

	##############################################################################

	function sanitize_string($input, $allow_newlines){

		#
		# first, do we need to convert from another character set or encoding?
		#


		#
		# next, check that it's valid UTF-8
		#

		#mb_substitute_character($GLOBALS[sanatize_mode] == SANATIZE_INVALID_REPLACE ? $GLOBALS[sanatize_replace] : 'none' );
		mb_substitute_character('long');

		$test = mb_convert_encoding($input, 'UTF-8', 'UTF-8');

		if ($test != $input){

			switch ($GLOBALS[sanatize_mode]){

				case SANATIZE_INVALID_THROW:
					throw new Exception('Sanatize found invalid input');

				case SANATIZE_INVALID_CONVERT:
					$input = mb_convert_encoding($input, $GLOBALS[sanatize_convert_from], 'UTF-8');
					break;

				case SANATIZE_INVALID_STRIP:
				case SANATIZE_INVALID_REPLACE:
					$input = $test;
			}
		}


		#
		#
		#

		return $input;
	}

	##############################################################################

	function sanitize_intval32($input, $complain=false){

		$r = intval($input);

		if ($r == 2147483647 && $complain){
			die("sanitize_intval32($input) overflowed");
		}

		return $r;
	}

	##############################################################################

	function sanitize_int64($input){

		if (preg_match('!^(\d+)!', $input, $m)){
			return $m[1];
		}

		return 0;
	}

	##############################################################################
?>