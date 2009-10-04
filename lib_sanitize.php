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

		if (!is_string($input)) $input = "$input";

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
		# filter out evil codepoints
		#
		# U+0000..U+0008	00000000..00001000				\x00..\x08				[\x00-\x08]
		# U+000E..U+001F	00001110..00011111				\x0E..\x1F				[\x0E-\x1F]
		# U+007F..U+009F	01111111..10011111				\x7F,\xC2\x80..\xC2\x9F			\x7F|\xC2[\x80-\x9F]
		# U+FEFF		1111111011111111				\xEF\xBB\xBF				\xEF\xBB\xBF
		# U+206A..U+206F	10000001101010..10000001101111			\xE2\x81\xAA..\xE2\x81\xAF		\xE2\x81[\xAA-\xAF]
		# U+FFF9..U+FFFA	1111111111111001..1111111111111010		\xEF\xBF\xB9..\xEF\xBF\xBA		\xEF\xBF[\xB9-\xBA]
		# U+E0000..U+E007F	11100000000000000000..11100000000001111111	\xF3\xA0\x80\x80..\xF3\xA0\x81\xBF	\xF3\xA0[\x80-\x81][\x80-\xBF]
		# U+D800..U+DFFF	1101100000000000..1101111111111111		\xED\xA0\x80..\xED\xBF\xBF		\xED[\xA0-\xBF][\x80-\xBF]
		#

		$rx = '[\x00-\x08]|[\x0E-\x1F]|\x7F|\xC2[\x80-\x9F]|\xEF\xBB\xBF|\xE2\x81[\xAA-\xAF]|\xEF\xBF[\xB9-\xBA]|\xF3\xA0[\x80-\x81][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]|\p{Cn}';

		$input = preg_replace('!'.$rx.'!u', '', $input);		


		#
		# convert some others into new lines
		#

		$lf = $allow_newlines ? "\n" : " ";
		$ff = $allow_newlines ? "\n\n" : " ";

		$map = array(
			"\xE2\x80\xA8"	=> $lf, # U+2028
			"\xE2\x80\xA9"	=> $ff, # U+2029
			"\x09"		=> " ",
			"\x0B"		=> $ff,
			"\x0C"		=> $ff,
			"\r\n"		=> $lf,
			"\r"		=> $lf,
		);

		$input = str_replace(array_keys($map), $map, $input);


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