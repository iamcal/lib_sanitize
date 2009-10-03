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

		$test = mb_convert_encoding($input, 'UTF-8', 'UTF-8');

		return $test;

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