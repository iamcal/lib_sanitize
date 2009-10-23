
== lib_sanitize ==

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



	# the default mode - strip out bad UTF-8
	$GLOBALS[sanatize_mode] = SANATIZE_INVALID_STRIP;

	# alternative mode - if the input isn't valid UTF-8, convert from anothr character set
	$GLOBALS[sanatize_mode] = SANATIZE_INVALID_CONVERT;
	$GLOBALS[sanatize_convert_from] = 'ISO-8859-1'; # Latin-1

	# alternative mode - if the input isn't valid UTF-8, throw an exception
	$GLOBALS[sanatize_mode] = SANATIZE_INVALID_THROW;



	# if you know your input encoding, set it first (all input is converted to UTF-8)
	$GLOBALS[sanatize_input_encoding] = 'SJIS'; # Shift-JIS



	# if you don't have mbstring, you can use iconv instead
	$GLOBALS['sanitize_extension'] = SANITIZE_EXTENSION_ICONV;

	# if you don't have iconv either, you can use pure php
	$GLOBALS['sanitize_extension'] = SANITIZE_EXTENSION_PHP;

	# mbstring (the default) is the fastest.
	# iconv is still fast, but slower than mbstring and supports less encodings.
	# pure php mode only supports UTF-8 and ISO-8859-1 (Latin-1) and is very slow.
?>


CREDITS
------------------------------------------------------------

By Cal Henderson <cal@iamcal.com>
