
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
?>


CREDITS
------------------------------------------------------------

By Cal Henderson <cal@iamcal.com>
