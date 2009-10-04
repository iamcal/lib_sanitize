<?
	include("lib_sanitize.php");
	include("test_harness.php");


	$GLOBALS[sanatize_mode] = SANATIZE_INVALID_STRIP;

	#
	# make sure we filter out bytes that are never valid UTF-8
	#
	# C0-C1 - overlong encoding
	# F5-F7 - 4 byte with code point over U+10FFFF
	# F8-FD - start of 5-6 bytes sequences
	# FE-FF - not used
	#

	test_string("hello\xC0world", "helloworld", "overlong encoding byte C0");
	test_string("hello\xC1world", "helloworld", "overlong encoding byte C1");
	test_string("hello\xF5world", "helloworld", "start of 4 byte code point over U+10FFFF");
	test_string("hello\xF6world", "helloworld", "start of 4 byte code point over U+10FFFF");
	test_string("hello\xF7world", "helloworld", "start of 4 byte code point over U+10FFFF");
	test_string("hello\xF8world", "helloworld", "start of deprecated 5 byte sequence");
	test_string("hello\xF9world", "helloworld", "start of deprecated 5 byte sequence");
	test_string("hello\xFAworld", "helloworld", "start of deprecated 5 byte sequence");
	test_string("hello\xFBworld", "helloworld", "start of deprecated 5 byte sequence");
	test_string("hello\xFCworld", "helloworld", "start of deprecated 6 byte sequence");
	test_string("hello\xFDworld", "helloworld", "start of deprecated 6 byte sequence");
	test_string("hello\xFEworld", "helloworld", "invalid byte FE");
	test_string("hello\xFFworld", "helloworld", "invalid byte FF");


	#
	# make sure we strip stray leading and trailing bytes
	#
	# 00000001 / 01 expects 0 trailing
	# 11000010 / C2 expects 1 trailing
	# 11100000 / E0 expects 2 trailing
	# 11110000 / F0 expects 3 trailing
	# 10111111 / BF is a trailing byte
	#

	test_string("a\xBFb", "ab",	"lone trail");

	test_string("a\x41b", "a\x41b",		"0 leader w/ 0 trail");
	test_string("a\x41\xBFb", "a\x41b",	"0 leader w/ 1 trail");

	test_string("a\xC2b", "ab",			"1 leader w/ 0 trail");
	test_string("a\xC2\xBFb", "a\xC2\xBFb",		"1 leader w/ 1 trail");
	test_string("a\xC2\xBF\xBFb", "a\xC2\xBFb",	"1 leader w/ 2 trail");

	test_string("a\xE0b", "ab",				"2 leader w/ 0 trail");
	test_string("a\xE0\xBFb", "ab",				"2 leader w/ 1 trail");
	test_string("a\xE0\xBF\xBFb", "a\xE0\xBF\xBFb",		"2 leader w/ 2 trail");
	test_string("a\xE0\xBF\xBF\xBFb", "a\xE0\xBF\xBFb",	"2 leader w/ 3 trail");

	test_string("a\xF0b", "ab",					"3 leader w/ 0 trail");
	test_string("a\xF0\xBFb", "ab",					"3 leader w/ 1 trail");
	test_string("a\xF0\xBF\xBFb", "ab",				"3 leader w/ 2 trail");
	test_string("a\xF0\xBF\xBF\xBFb", "a\xF0\xBF\xBF\xBFb",		"3 leader w/ 3 trail");
	test_string("a\xF0\xBF\xBF\xBF\xBFb", "a\xF0\xBF\xBF\xBFb",	"3 leader w/ 4 trail");


	#
	# encoding that are out of range
	#
	# 4 bytes over U+10FFFF
	# 5 bytes
	# 6 bytes
	#

	test_string(c8(0x110000), "", "lowest out of range 4-byte U+110000");
	test_string(c8(0x1FFFFF), "", "highest out of range 4-byte U+1FFFFF");

	test_string("\xF8\x88\x80\x80\x80", "", "lowest 5-byte U+200000");
	test_string("\xFB\xBF\xBF\xBF\xBF", "", "highest 5-byte U+3FFFFFF");

	test_string("\xFC\x84\x80\x80\x80\x80", "", "lowest 6-byte U+4000000");
	test_string("\xFD\xBF\xBF\xBF\xBF\xBF", "", "highest 6-byte U+7FFFFFFF");


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

	test_string("a\xC0\x80b", "ab", "lowest overlong 2-byte");
	test_string("a\xC1\xBFb", "ab", "highest overlong 2-byte");

	test_string("a\xE0\x80\x80b", "ab", "lowest overlong 3-byte");
	test_string("a\xE0\x9F\xBFb", "ab", "highest overlong 3-byte");

	test_string("a\xF0\x80\x80\x80b", "ab", "lowest overlong 4-byte");
	test_string("a\xF0\x8F\xBF\xBFb", "ab", "highest overlong 4-byte");

	# TODO: overlong 5 & 6 bytes


	#
	# test the replacement mode
	#
if (0){

	$GLOBALS[sanatize_mode]		= SANATIZE_INVALID_REPLACE;
	$GLOBALS[sanatize_replace]	= ord('!');

	test_string("hello\xC0world", "hello!world");
	test_string("hello\xF5world", "hello!world");

	test_string("a\x41b", "a\x41b");	# 0 leader w/ 0 trail
	test_string("a\x41\xBFb", "a\x41b");	# 0 leader w/ 1 trail

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
}

	#define('SANATIZE_INVALID_THROW',	3); # throw an error
	#define('SANATIZE_INVALID_CONVERT',	4); # convert from another encoding
	#$GLOBALS[sanatize_convert_from]	= 'ISO-8859-1'; # Latin-1



	test_summary();

	function test_string($in, $out, $name=null){
		$GLOBALS[tests][string]++;
		if (!isset($name)) $name = "Unknown string test ".$GLOBALS[tests][string];		

		$got = sanitize($in, 'str');
		test_harness($in, $out, $got, $name);
	}

?>