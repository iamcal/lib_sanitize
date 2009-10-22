<?
	include("lib_sanitize.php");
	include("test_harness.php");


	$GLOBALS[sanatize_mode] = SANATIZE_INVALID_STRIP;


	###########################################################################################

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

	###########################################################################################

	#
	# make sure we strip stray leading and trailing bytes
	#

	#
	# trailing bytes (BF = 10111111) by themselves are bad
	#

	test_string("a\xBFb", "ab",	"lone trail");


	#
	# bytes with the highest bit unset (like x41) can't have trailers
	#

	test_string("a\x41b", "a\x41b",		"0 leader w/ 0 trail");
	test_string("a\x41\xBFb", "a\x41b",	"0 leader w/ 1 trail");


	#
	# C2 (11000010) expects 1 trailing byte
	# C2-BF is U+00BF
	#

	test_string("a\xC2b", "ab",			"1 leader w/ 0 trail");
	test_string("a\xC2\xBFb", "a\xC2\xBFb",		"1 leader w/ 1 trail");
	test_string("a\xC2\xBF\xBFb", "a\xC2\xBFb",	"1 leader w/ 2 trail");


	#
	# we can't use E0-BF-BF since U+0FFF isn't a valid codepoint.
	# instead we use E1-80-80 since U+1000 is valid
	#

	test_string("a\xE1b", "ab",				"2 leader w/ 0 trail");
	test_string("a\xE1\x80b", "ab",				"2 leader w/ 1 trail");
	test_string("a\xE1\x80\x80b", "a\xE1\x80\x80b",		"2 leader w/ 2 trail");
	test_string("a\xE1\x80\x80\x80b", "a\xE1\x80\x80b",	"2 leader w/ 3 trail");


	#
	# we can't use F0-BF-BF-BF since U+3FFFF is always invalid.
	# not much is defined on this plane, but U+20000 (F0-A0-80-80) is
	# a valid ideograph (extension b)
	#

	test_string("a\xF0b", "ab",					"3 leader w/ 0 trail");
	test_string("a\xF0\xA0b", "ab",					"3 leader w/ 1 trail");
	test_string("a\xF0\xA0\x80b", "ab",				"3 leader w/ 2 trail");
	test_string("a\xF0\xA0\x80\x80b", "a\xF0\xA0\x80\x80b",		"3 leader w/ 3 trail");
	test_string("a\xF0\xA0\x80\x80\x80b", "a\xF0\xA0\x80\x80b",	"3 leader w/ 4 trail");

	###########################################################################################

	#
	# encodings that are out of range
	#

	#
	# only *some* 4-byte encodings are out of range - those over U+10FFFF.
	# that means some starting with F4 are invalid, while all starting with F5-F7 are invalid
	#

	test_string("a\xf4\x90\x80\x80b", "ab", "lowest out of range 4-byte starting with F4 - U+110000");
	test_string("a\xF4\xBF\xBF\xBFb", "ab", "highest out of range 4-byte starting with F4 - U+13FFFF");

	test_string("a\xF5\x80\x80\x80b", "ab", "lowest out of range 4-byte starting over F4 - U+140000");
	test_string("a\xf7\xbf\xbf\xbfb", "ab", "highest out of range 4-byte starting over F4 - U+1FFFFF");

	#
	# 5's are easiest to test
	#

	test_string("a\xF8\x88\x80\x80\x80b", "ab", "lowest 5-byte U+200000");
	test_string("a\xFB\xBF\xBF\xBF\xBFb", "ab", "highest 5-byte U+3FFFFFF");

	#
	# we test this separately since having the FD byte causes mbstring to
	# insert crap like 'BAD+FFFFFF' which made for weird results. this is no
	# longer true, since we pre-strip, but these tests still work
	#

	test_string("a\xFC\x84\x80\x80\x80\x80b", "ab", "lowest 6-byte starting with FC - U+4000000");
	test_string("a\xFC\xBF\xBF\xBF\xBF\xBFb", "ab", "highest 6-byte starting with FC - U+3FFFFFFF");

	test_string("a\xFD\x80\x80\x80\x80\x80b", "ab", "lowest 6-byte starting with FD - U+40000000");
	test_string("a\xFD\xBF\xBF\xBF\xBF\xBFb", "ab", "highest 6-byte starting with FD - U+7FFFFFFF");

	###########################################################################################

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

	###########################################################################################


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