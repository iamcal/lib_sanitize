<?
	include("lib_sanitize.php");
	include("test_harness.php");


	$GLOBALS[sanatize_mode] = SANATIZE_INVALID_STRIP;

	#
	# make sure we filter out bytes that are never valid UTF-8
	#
	# C0-C1 - overlong encoding
	# F5-FD - start of 4-6 bytes sequences
	# FE-FF - not used
	#

	test_string("hello\xC0world", "helloworld");
	test_string("hello\xC1world", "helloworld");
	test_string("hello\xF5world", "helloworld");
	test_string("hello\xF6world", "helloworld");
	test_string("hello\xF7world", "helloworld");
	test_string("hello\xF8world", "helloworld");
	test_string("hello\xF9world", "helloworld");
	test_string("hello\xFAworld", "helloworld");
	test_string("hello\xFBworld", "helloworld");
	test_string("hello\xFCworld", "helloworld");
	test_string("hello\xFDworld", "helloworld");
	test_string("hello\xFEworld", "helloworld");
	test_string("hello\xFFworld", "helloworld");


	#
	# make sure we strip stray leading and trailing bytes
	#
	# 00000001 / 01 expects 0 trailing
	# 11000010 / C2 expects 1 trailing
	# 11100000 / E0 expects 2 trailing
	# 11110000 / F0 expects 3 trailing
	# 10111111 / BF is a trailing byte
	#

	test_string("a\xBFb", "ab");	# trail

	test_string("a\x01b", "a\x01b");	# 0 leader w/ 0 trail
	test_string("a\x01\xBFb", "a\x01b");	# 0 leader w/ 1 trail

	test_string("a\xC2b", "ab");			# 1 leader w/ 0 trail
	test_string("a\xC2\xBFb", "a\xC2\xBFb");	# 1 leader w/ 1 trail
	test_string("a\xC2\xBF\xBFb", "a\xC2\xBFb");	# 1 leader w/ 2 trail

	test_string("a\xE0b", "ab");				# 2 leader w/ 0 trail
	test_string("a\xE0\xBFb", "ab");			# 2 leader w/ 1 trail
	test_string("a\xE0\xBF\xBFb", "a\xE0\xBF\xBFb");	# 2 leader w/ 2 trail
	test_string("a\xE0\xBF\xBF\xBFb", "a\xE0\xBF\xBFb");	# 2 leader w/ 3 trail

	test_string("a\xF0b", "ab");					# 3 leader w/ 0 trail
	test_string("a\xF0\xBFb", "ab");				# 3 leader w/ 1 trail
	test_string("a\xF0\xBF\xBFb", "ab");				# 3 leader w/ 2 trail
	test_string("a\xF0\xBF\xBF\xBFb", "a\xF0\xBF\xBF\xBFb");	# 3 leader w/ 3 trail
	test_string("a\xF0\xBF\xBF\xBF\xBFb", "a\xF0\xBF\xBF\xBFb");	# 3 leader w/ 4 trail


	#
	# check we remove overlong encodings (using 3 bytes when only 2 were needed)
	#
	# 2 byte: 1100000x 10xxxxxx
	# 3 byte: 11100000 100xxxxx 10xxxxxx
	# 4 byte: 11110000 1000xxxx 10xxxxxx 10xxxxxx
	#
	# Note: it appears that we convert them down into
	# the correct number of bytes, if that would be more than 1?
	#

	test_string("a\xC0\x80b", "ab"); # lowest bad 2-byte
	test_string("a\xC1\xBFb", "ab"); # highest bad 2-byte

	test_string("a\xE0\x80\x80b", "ab"); # lowest bad 3-byte
	test_string("a\xE0\x9F\xBFb", "ab"); # lowest bad 3-byte

	test_string("a\xF0\x80\x80\x80b", "ab"); # lowest bad 4-byte
	test_string("a\xF0\x8F\xBF\xBFb", "ab"); # lowest bad 4-byte
	



	#
	# test the replacement mode
	#

	$GLOBALS[sanatize_mode]		= SANATIZE_INVALID_REPLACE;
	$GLOBALS[sanatize_replace]	= ord('!');

	test_string("hello\xC0world", "hello!world");
	test_string("hello\xF5world", "hello!world");

	test_string("a\x01b", "a\x01b");	# 0 leader w/ 0 trail
	test_string("a\x01\xBFb", "a\x01b");	# 0 leader w/ 1 trail

	test_string("a\xC2b", "ab");			# 1 leader w/ 0 trail
	test_string("a\xC2\xBFb", "a\xC2\xBFb");	# 1 leader w/ 1 trail
	test_string("a\xC2\xBF\xBFb", "a\xC2\xBFb");	# 1 leader w/ 2 trail

	test_string("a\xE0b", "ab");				# 2 leader w/ 0 trail
	test_string("a\xE0\xBFb", "ab");			# 2 leader w/ 1 trail
	test_string("a\xE0\xBF\xBFb", "a\xE0\xBF\xBFb");	# 2 leader w/ 2 trail
	test_string("a\xE0\xBF\xBF\xBFb", "a\xE0\xBF\xBFb");	# 2 leader w/ 3 trail

	test_string("a\xF0b", "ab");					# 3 leader w/ 0 trail
	test_string("a\xF0\xBFb", "ab");				# 3 leader w/ 1 trail
	test_string("a\xF0\xBF\xBFb", "ab");				# 3 leader w/ 2 trail
	test_string("a\xF0\xBF\xBF\xBFb", "a\xF0\xBF\xBF\xBFb");	# 3 leader w/ 3 trail
	test_string("a\xF0\xBF\xBF\xBF\xBFb", "a\xF0\xBF\xBF\xBFb");	# 3 leader w/ 4 trail

	test_string("a\xC0\x80b", "ab"); # lowest bad 2-byte
	test_string("a\xC1\xBFb", "ab"); # highest bad 2-byte

	test_string("a\xE0\x80\x80b", "ab"); # lowest bad 3-byte
	test_string("a\xE0\x9F\xBFb", "ab"); # lowest bad 3-byte

	test_string("a\xF0\x80\x80\x80b", "ab"); # lowest bad 4-byte
	test_string("a\xF0\x8F\xBF\xBFb", "ab"); # lowest bad 4-byte


	#define('SANATIZE_INVALID_THROW',	3); # throw an error
	#define('SANATIZE_INVALID_CONVERT',	4); # convert from another encoding
	#$GLOBALS[sanatize_convert_from]	= 'ISO-8859-1'; # Latin-1



	test_summary();

	function test_string($in, $out){
		$got = sanitize($in, 'str');
		test_harness($in, $out, $got, "String test ".++$GLOBALS[tests][string]);
	}

?>