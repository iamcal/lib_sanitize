
lib_sanitize
============

A PHP input sanitizing library.


USAGE
------------------------------------------------------------

	<?php
		include('lib_sanitize.php');

		# in essence
		$clean = sanitize($dirty, $type[, $default_value = null]);

		# various formats
		$a = sanitize($input, 'str');		# UTF-8 string
		$b = sanitize($input, 'str_multi');	# UTF-8 string allowing newlines
		$c = sanitize($input, 'int32'); 	# PHP's native int type
		$d = sanitize($input, 'int64'); 	# A 64bit number as a string
		$e = sanitize($input, 'html');		# HTML filtered by lib_filter
		$f = sanitize($input, 'bool');		# A boolean
		$g = sanitize($input, 'isset');		# True is the input was set
		$h = sanitize($input, 'rx', null, $rx);	# Returns input it matches $rx (a preg regex)
		$i = sanitize($input, 'in', null, $in);	# Returns input if it exists in array $in

		# GET & POST variables
		$a = get_bool('key_name');		# $_GET
		$b = post_int32('key_name');		# $_POST
		$c = request_str_multi('key_name');	# $_REQUEST

		# just care about strings?
		$a = sanitize_string($input, $allow_newlines);



		# the default mode - strip out bad UTF-8
		$GLOBALS['sanitize_mode'] = SANITIZE_INVALID_STRIP;

		# alternative mode - if the input isn't valid UTF-8, convert from anothr character set
		$GLOBALS['sanitize_mode'] = SANITIZE_INVALID_CONVERT;
		$GLOBALS['sanitize_convert_from'] = 'ISO-8859-1'; # Latin-1

		# alternative mode - if the input isn't valid UTF-8, throw an exception
		$GLOBALS['sanitize_mode'] = SANITIZE_INVALID_THROW;



		# if you know your input encoding, set it first (all input is converted to UTF-8)
		$GLOBALS['sanitize_input_encoding'] = 'SJIS'; # Shift-JIS



		# if you don't have mbstring, you can use iconv instead
		$GLOBALS['sanitize_extension'] = SANITIZE_EXTENSION_ICONV;

		# if you don't have iconv either, you can use pure php
		$GLOBALS['sanitize_extension'] = SANITIZE_EXTENSION_PHP;

		# iconv is the fastest, but supports less encodings and is broken on some platforms.
		# mbstring (the default) is still very fast and supports many encodings.
		# pure php mode only supports UTF-8 and ISO-8859-1 (Latin-1) and is very slow.



		# by default, the string filter will remove all 'unassigned' (property: Cn) unicode
		# characters. you may need to disable this if your PCRE library does not support
		# unicode properties (--enable-unicode-properties compilation flag)
		$GLOBALS['sanitize_strip_reserved'] = false;

	?>


CREDITS
------------------------------------------------------------

By Cal Henderson <cal@iamcal.com>
