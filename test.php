<h1>lib_sanitize Tests</h1>

<ul>
	<li> The tests for mbstring will fail if you don't have the mbstring extension. </li>
	<li> The tests for iconv will fail if you don't have the iconv extension. </li>
	<li> Iconv can't do ISO-2022-JP conversion (tested on windows & fedora). </li>
	<li> Pure-PHP mode skips all the encoding conversion tests apart from Latin-1. </li>
</ul>

<?
	#
	# $Id$
	#

	error_reporting(30719 | 2048); # E_ALL | E_STRICT

	include("lib_sanitize.php");

	test_start();

	define('RUN_TESTS_BAD_BYTES'		, 1);
	define('RUN_TESTS_LEADS_TRAILS'		, 1);
	define('RUN_TESTS_OUT_OF_RANGE'		, 1);
	define('RUN_TESTS_OVERLONG'		, 1);
	define('RUN_TESTS_REPLACE'		, 1);
	define('RUN_TESTS_CONVERT_FROM'		, 1);
	define('RUN_TESTS_THROW'		, 1);
	define('RUN_TESTS_BAD_MODE'		, 1);
	define('RUN_TESTS_INPUT_CONVERSION'	, 1);
	define('RUN_TESTS_BASICS'		, 1);
	define('RUN_TESTS_STRIPPING'		, 1);

	###########################################################################################

	if (!$GLOBALS['sanitize_pcre_has_props']){

		$GLOBALS['sanitize_strip_reserved'] = false;
		echo '<p style="color: red">Reserved character stripping will not be tested, because your PHP install does not support it.</p>';
	}

	###########################################################################################

	#
	# we run all of these character encoding tests in all three extension modes
	#

	$extensions = array(
		SANITIZE_EXTENSION_PHP		=> 'PHP',
		SANITIZE_EXTENSION_MBSTRING	=> 'mbstring',
		SANITIZE_EXTENSION_ICONV	=> 'iconv',
	);

	foreach ($extensions as $extension => $extension_name){

		$GLOBALS['sanitize_extension'] = $extension;
		$GLOBALS['test_name_prefix'] = "[$extension_name] ";


	###########################################################################################

	if (RUN_TESTS_BAD_BYTES){

	$GLOBALS['sanitize_mode'] = SANITIZE_INVALID_STRIP;

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

	}

	###########################################################################################

	if (RUN_TESTS_LEADS_TRAILS){

	#
	# make sure we strip stray leading and trailing bytes
	#

	#
	# trailing bytes (BF = 10111111) by themselves are bad
	#

	test_string("a\xBFb",		"ab",	"lone trail");
	test_string("a\xBF\xBFb",	"ab",	"2 lone trails");
	test_string("a\xBF\xBF\xBFb",	"ab",	"3 lone trails");


	#
	# bytes with the highest bit unset (like x41) can't have trailers
	#

	test_string("a\x41b",		"a\x41b", "0 leader w/ 0 trail");
	test_string("a\x41\xBFb",	"a\x41b", "0 leader w/ 1 trail");
	test_string("a\x41\xBF\xBFb",	"a\x41b", "0 leader w/ 2 trail");


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


	#
	# some runs of leaders to check we're not skipping any bytes
	# (the pure php filter couldn't do these at one point)
	#

	test_string("a\xC2\xC2\xBFb", "a\xC2\xBFb",			"2L1 1T");
	test_string("a\xC2\xC2\xBF\xBFb", "a\xC2\xBFb",			"2L1 2T");

	test_string("a\xE1\xE1\x80\x80b", "a\xE1\x80\x80b",		"2L2 2T");
	test_string("a\xE1\xE1\x80\x80\x80b", "a\xE1\x80\x80b",		"2L2 3T");
	test_string("a\xE1\xE1\xE1\x80\x80\x80b", "a\xE1\x80\x80b",	"3L2 3T");

	test_string("a\xE1\x80\xE1\xE1\x80\x80\x80b", "a\xE1\x80\x80b",	"L2 T 2L2 3T");

	test_string("\xC2\xC2\xBF", "\xC2\xBF",				"2L1 1T at egdes");
	test_string("\xC2\xC2\xBF\xBF", "\xC2\xBF",			"2L1 2T at egdes");

	test_string("\xE1\xE1\x80\x80", "\xE1\x80\x80",			"2L2 2T at egdes");
	test_string("\xE1\xE1\x80\x80\x80", "\xE1\x80\x80",		"2L2 3T at egdes");
	test_string("\xE1\xE1\xE1\x80\x80\x80", "\xE1\x80\x80",		"3L2 3T at egdes");

	test_string("\xE1\x80\xE1\xE1\x80\x80\x80", "\xE1\x80\x80",	"L2 T 2L2 3T at egdes");
	

	}

	###########################################################################################

	if (RUN_TESTS_OUT_OF_RANGE){

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

	}

	###########################################################################################

	if (RUN_TESTS_OVERLONG){

	#
	# check we remove overlong encodings (using 3 bytes when only 2 were needed)
	#
	# 2 byte: 1100000x 10xxxxxx
	# 3 byte: 11100000 100xxxxx 10xxxxxx
	# 4 byte: 11110000 1000xxxx 10xxxxxx 10xxxxxx
	#

	test_string("a\xC0\x80b", "ab", "lowest overlong 2-byte - U+0000");
	test_string("a\xC1\xBFb", "ab", "highest overlong 2-byte - U+007F");

	#
	# the lowest valid 2-byte would be U+0080, but that's a control character.
	# A0 is the first non-control after that (it's a non-breaking space)
	#

	test_string("a\xC2\xA0b", "a\xC2\xA0b", "lowest valid 2-byte - U+00A0");

	#
	# the highest 2-byte is U+07FF, but it's reserved. we will strip it if
	# '' is enabled. U+07FA is the highest valid 2-byte.
	#

	if ($GLOBALS['sanitize_pcre_has_props']){

		test_string("a\xDF\xBAb", "a\xDF\xBAb", "highest valid 2-byte - U+07FA");

		$GLOBALS['sanitize_strip_reserved'] = false;
		test_string("a\xDF\xBFb", "a\xDF\xBFb", "highest possible 2-byte (no strip) - U+07FF");

		$GLOBALS['sanitize_strip_reserved'] = true;
		test_string("a\xDF\xBFb", "ab", "highest possible 2-byte (strip) - U+07FF");

	}else{
		test_string("a\xDF\xBAb", "a\xDF\xBAb", "highest valid 2-byte - U+07FA");
		test_string("a\xDF\xBFb", "a\xDF\xBFb", "highest possible 2-byte - U+07FF");
	}

	test_string("a\xE0\x80\x80b", "ab", "lowest overlong 3-byte - U+0000");
	test_string("a\xE0\x9F\xBFb", "ab", "highest overlong 3-byte - U+07FF");

	test_string("a\xe0\xa0\x80b", "a\xe0\xa0\x80b", "lowest valid 3-byte - U+0800");
	test_string("a\xEF\xBF\xBBb", "a\xEF\xBF\xBBb", "highest valid 3-byte - U+FFFB"); # FFFC/D are replaced, FFFE/F are invalid

	test_string("a\xF0\x80\x80\x80b", "ab", "lowest overlong 4-byte - U+0000");
	test_string("a\xF0\x8F\xBF\xBBb", "ab", "highest overlong 4-byte - U+FFFB"); # FFFC/D are replaced, FFFE/F are invalid

	test_string("a\xf0\x90\x80\x80b", "a\xf0\x90\x80\x80b", "lowest valid 4-byte - U+10000");
	test_string("a\xf4\x8f\xbf\xbdb", "a\xf4\x8f\xbf\xbdb", "highest valid 4-byte - U+10FFFD"); # 10FFFE/F are invalid

	test_string("a\xF8\x80\x80\x80\x80b", "ab", "lowest overlong 5-byte - U+0000");
	test_string("a\xF8\x87\xBF\xBF\xBDb", "ab", "highest overlong 5-byte - U+1FFFFD"); # 1FFFFE/F are invalid

	test_string("a\xFC\x80\x80\x80\x80\x80b", "ab", "lowest overlong 6-byte - U+0000");
	test_string("a\xFC\x83\xBF\xBF\xBF\xBFb", "ab", "highest overlong 6-byte - U+3FFFFFF");

	}

	###########################################################################################

	if (RUN_TESTS_CONVERT_FROM){

	#
	# test invalid conversion
	#

	$GLOBALS['sanitize_mode'] = SANITIZE_INVALID_CONVERT;
	$GLOBALS['sanitize_convert_from'] = 'ISO-8859-1';

	test_string("\x76", "\x76", "Latin-1 fallback 0x76");
	test_string("\xEB", "\xc3\xab", "Latin-1 fallback 0xEB");
	test_string("H\xEBllo", "H\xc3\xabllo", "Latin-1 fallback word");

	}

	###########################################################################################

	if (RUN_TESTS_THROW){

	#
	# test invalid exceptions
	#

	$GLOBALS['sanitize_mode'] = SANITIZE_INVALID_THROW;
	
	}

	###########################################################################################

	if (RUN_TESTS_BAD_MODE){

	#
	# test a non-existent mode
	#

	$GLOBALS['sanitize_mode']		= 1000;

	test_exception('sanitize("hello\xC0world", "str");', 'Unknown sanitize mode exception');

	}

	###########################################################################################

	if (RUN_TESTS_INPUT_CONVERSION){

	#
	# test input conversion
	#

	$GLOBALS['sanitize_mode']	= SANITIZE_INVALID_STRIP;


	#
	# Shift-JIS / SJIS / MS_Kanji
	# http://unicode.org/Public/MAPPINGS/OBSOLETE/EASTASIA/JIS/SHIFTJIS.TXT
	#

	if ($extension != SANITIZE_EXTENSION_PHP){

		$GLOBALS['sanitize_input_encoding']	= 'SJIS';

		test_string("\x76"    , "\x76"        , "SJIS 0x76   -> U+0076");
		test_string("\xa6"    , "\xef\xbd\xa6", "SJIS 0xA6   -> U+FF66");
		test_string("\x81\x40", "\xe3\x80\x80", "SJIS 0x8140 -> U+3000");
		test_string("\x82\xdc", "\xe3\x81\xbe", "SJIS 0x82DC -> U+307E");
		test_string("\x8c\xcc", "\xe6\x95\x85", "SJIS 0x8CCC -> U+6545");
	}


	#
	# ISO-2022-JP => US-ASCII + JIS X0201:1976 + JIS X0208:1978 + JIS X0208:1983 
	# http://unicode.org/Public/MAPPINGS/OBSOLETE/EASTASIA/JIS/JIS0201.TXT
	# http://unicode.org/Public/MAPPINGS/OBSOLETE/EASTASIA/JIS/JIS0208.TXT
	#
	# 208-1978 is escaped with 0x1B 0x24($) 0x40(@)
	# 208-1983 is escaped with 0x1B 0x24($) 0x42(B)
	#

	if ($extension != SANITIZE_EXTENSION_PHP && $extension != SANITIZE_EXTENSION_ICONV){

		$GLOBALS['sanitize_input_encoding'] = 'ISO-2022-JP';

		test_string("\x76", "\x76"        , "ISO-2022-JP US-ASCII 0x76   -> U+0076");
		test_string("\xa6", "\xef\xbd\xa6", "ISO-2022-JP JIS-X-201 0xA6   -> U+FF66");
		test_string("\xbe", "\xef\xbd\xbe", "ISO-2022-JP JIS-X-201 0xBE   -> U+FF7E");

		test_string("\x1B\$@\x21\x4a", "\xef\xbc\x88", "ISO-2022-JP JIS-X-208-1978 0x214A -> U+FF08");
		test_string("\x1B\$@\x3c\x2d", "\xe8\xbe\x9e", "ISO-2022-JP JIS-X-208-1978 0x3C2D -> U+8F9E");

		test_string("\x1B\$B\x21\x4a", "\xef\xbc\x88", "ISO-2022-JP JIS-X-208-1983 0x214A -> U+FF08");
		test_string("\x1B\$B\x3c\x2d", "\xe8\xbe\x9e", "ISO-2022-JP JIS-X-208-1983 0x3C2D -> U+8F9E");
	}


	#
	# EUC-JP => US-ASCII + JIS X0201:1997 (hankaku kana part) + JIS X0208:1990 + JIS X0212:1990 
	# http://unicode.org/Public/MAPPINGS/OBSOLETE/EASTASIA/JIS/JIS0201.TXT
	# http://unicode.org/Public/MAPPINGS/OBSOLETE/EASTASIA/JIS/JIS0208.TXT
	# http://unicode.org/Public/MAPPINGS/OBSOLETE/EASTASIA/JIS/JIS0212.TXT
	#
	# to encode 208 in EUC-JP, just add 0x8080
	# to encode 212 in EUC-JP put 0x8f followed by the code+0x8080
	#

	if ($extension != SANITIZE_EXTENSION_PHP){

		$GLOBALS['sanitize_input_encoding'] = 'EUC-JP';

		test_string("\x76", "\x76", "EUC-JP US-ASCII 0x76 -> U+0076");

		test_string("\x8E\xB6", "\xef\xbd\xb6", "EUC-JP JIS-X-201 0xB6 -> U+FF76");
		test_string("\x8E\xDE", "\xef\xbe\x9e", "EUC-JP JIS-X-201 0xDE -> U+FF9E");

		test_string("\xA1\xB3", "\xe3\x83\xbd", "EUC-JP JIS-X-0208 0x2133 -> U+30FD");
		test_string("\xB0\xD3", "\xe5\xb0\x89", "EUC-JP JIS-X-0208 0x3053 -> U+5C09");

		test_string("\x8f\xB0\xB1", "\xe4\xb9\x84", "EUC-JP JIS-X-0212 0x3031 - U+4E44");
		test_string("\x8f\xC2\xD8", "\xe6\x9a\xa4", "EUC-JP JIS-X-0212 0x4258 - U+66A4");
	}


	#
	# ISO-8859-1 / Latin-1
	# http://unicode.org/Public/MAPPINGS/ISO8859/8859-1.TXT
	#

	$GLOBALS['sanitize_input_encoding'] = 'ISO-8859-1';

	test_string("\x76", "\x76",     "ISO-8859-1 0x76 -> U+0076");
	test_string("\xE6", "\xc3\xa6", "ISO-8859-1 0xE6 -> U+00E6");


	#
	# ISO-8859-2 / Latin-2
	# http://unicode.org/Public/MAPPINGS/ISO8859/8859-2.TXT
	#	

	if ($extension != SANITIZE_EXTENSION_PHP){

		$GLOBALS['sanitize_input_encoding'] = 'ISO-8859-2';

		test_string("\x76", "\x76",     "ISO-8859-2 0x76 -> U+0076");
		test_string("\xE6", "\xc4\x87", "ISO-8859-2 0xE6 -> U+0107");
	}


	#
	# ISO-8859-15 / Latin-9
	# http://unicode.org/Public/MAPPINGS/ISO8859/8859-15.TXT
	#	

	if ($extension != SANITIZE_EXTENSION_PHP){

		$GLOBALS['sanitize_input_encoding'] = 'ISO-8859-15';

		test_string("\x76", "\x76",     "ISO-8859-15 0x76 -> U+0076");
		test_string("\xBE", "\xc5\xb8", "ISO-8859-15 0xBE -> U+0178");
	}


	#
	# remember to reset this, or further tests will get fucked up
	#

	$GLOBALS['sanitize_input_encoding']	= 'UTF-8';

	}

	###########################################################################################

	#
	# end of the extension mode loop
	#

	}

	$GLOBALS['sanitize_extension'] = SANITIZE_EXTENSION_MBSTRING;
	$GLOBALS['test_name_prefix'] = '';

	###########################################################################################

	if (RUN_TESTS_BASICS){

	#
	# basics
	#

	test_sanitize("", "str", "");
	test_sanitize("hello", "str", "hello");
	test_sanitize(1, "str", "1");


	#
	# TODO: more!
	#

	}

	###########################################################################################

	if (RUN_TESTS_STRIPPING){

	#
	# carriage return normalization
	#

	test_sanitize(c8(0x2028), 'str', " ");
	test_sanitize(c8(0x2028), 'str_multi', "\n");

	test_sanitize(c8(0x2029), 'str', " ");
	test_sanitize(c8(0x2029), 'str_multi', "\n\n");

	test_sanitize(c8(0x0B), 'str', " ");
	test_sanitize(c8(0x0B), 'str_multi', "\n\n");

	test_sanitize(c8(0x0C), 'str', " ");
	test_sanitize(c8(0x0C), 'str_multi', "\n\n");

	test_sanitize("\r\n", 'str', " ");
	test_sanitize("\r\n", 'str_multi', "\n");

	test_sanitize("\n", 'str', " ", "LF [single line mode]");
	test_sanitize("\n", 'str_multi', "\n", "LF [multi line mode]");

	test_sanitize("\r", 'str', " ");
	test_sanitize("\r", 'str_multi', "\n");

	test_sanitize("\r\r\n", 'str', "  ", "mix of CRLF and CR [single line mode]");
	test_sanitize("\r\r\n", 'str_multi', "\n\n", "mix of CRLF and CR [multi line mode]");

	test_sanitize("\t", 'str', " ", "TAB [single line mode]");
	test_sanitize("\t", 'str_multi', " ", "TAB [multi line mode]");

	test_sanitize(c8(0x85), 'str', " ", "NEL [single line mode]");
	test_sanitize(c8(0x85), 'str_multi', "\n", "NEL [multi line mode]");


	#
	# Cc removals
	#

	$strip_points = array_merge(
		range(0x00, 0x08),
		range(0x0E, 0x1F),
		range(0x7F, 0x84),
		range(0x86, 0x9F)
	);

	foreach ($strip_points as $point){

		test_sanitize("foo".c8($point)."bar", 'str', "foobar", "Strip Cc U+00".sprintf('%02X', $point));
	}


	#
	# Cf removals
	#

	$strip_points = array_merge(
		array(0xFEFF),
		range(0x206A, 0x206F),
		range(0xFFF9, 0xFFFA),
		array(
			0xE0000,	# U+E0000..U+E007F is a big
			0xE003F,	# range, so we'll test the
			0xE007F,	# edges and the middle.
		)
	);

	foreach ($strip_points as $point){

		test_sanitize("foo".c8($point)."bar", 'str', "foobar", "Strip Cf U+".sprintf('%04X', $point));
	}


	#
	# Cs removals
	#

	$strip_points = array(
		0xD800,	# lowest leading surrogate
		0xDA00, # mid-point
		0xDBFF, # highest leading surrogate
		0xDC00, # lowest trailing surrogate
		0xDE00, # mid-point
		0xDFFF, # highest trailing surrogate
	);

	foreach ($strip_points as $point){

		test_sanitize("foo".c8($point)."bar", 'str', "foobar", "Strip Cs U+".sprintf('%04X', $point));
	}


	#
	# Cn removals
	#

	#
	# non-characters
	#

		$strip_points = array(
			0xFFFE, 0xFFFF,
			0x1FFFE, 0x1FFFF,
			0x2FFFE, 0x2FFFF,
			# etc etc
			0x10FFFE, 0x10FFFF,

			0xFDD0, # lowest of the extras
			0xFDE0, # mid-point
			0xFDEF, # highest of the extras
		);

		foreach ($strip_points as $point){

			test_sanitize("foo".c8($point)."bar", 'str', "foobar", "Strip Cn non-character U+".sprintf('%04X', $point));
		}

		


	#
	# So replacements
	#

	test_sanitize("foo".c8(0xFFFC)."bar", 'str', "foo?bar", "Replace So U+FFFC with ?");
	test_sanitize("foo".c8(0xFFFD)."bar", 'str', "foo?bar", "Replace So U+FFFD with ?");

	}

	###########################################################################################

	#
	# TODO: bidi balancing tests
	#

	###########################################################################################

	test_summary();

	###########################################################################################

	function test_start(){

		$GLOBALS['tests'] = array(
			'string' => 0,
			'sanitize' => 0,
			'exception' => 0,
		);
		$GLOBALS['verbose'] = isset($_GET['verbose']) ? 1 : 0;
		$GLOBALS['test_passed'] = 0;
		$GLOBALS['test_failed'] = 0;
		$GLOBALS['test_header_done'] = 0;
		$GLOBALS['test_name_prefix'] = '';
	}

	function test_header(){
		if ($GLOBALS['test_header_done']) return;
		$GLOBALS['test_header_done'] = 1;
		echo '<table border="1">';
		echo "<tr>\n";
		echo "<th>Name</th>";
		echo "<th>Status</th>";
		echo "<th>Input</th>";
		echo "<th>Expected</th>";
		echo "<th>Got</th>";
		echo "</tr>\n";
	}

	function test_harness($in, $out, $got, $name){

		$output = 0;
		$pass = 0;

		if ($out === $got){
			$GLOBALS['test_passed']++;
			if ($GLOBALS['verbose']){
				$pass = 1;
				$output = 1;
			}
		}else{
			$GLOBALS['test_failed']++;
			$output = 1;
		}
		if ($output){
			if ($GLOBALS['verbose'] || ($out != $got)){
				$out_type = gettype($out);
				$got_type = gettype($got);

				test_header();

				echo "<tr>\n";
				echo "\t<td>".HtmlSpecialChars($GLOBALS['test_name_prefix'].$name)."</td>\n";
				if ($pass){
					echo "\t<td style=\"color: green\">pass</td>\n";
				}else{
					echo "\t<td style=\"background-color: red; color: white\">fail</td>\n";
				}
				echo "\t<td>".byteify($in)."</td>\n";

				if ($out_type == $got_type){
					echo "\t<td>".byteify($out)."</td>\n";
					echo "\t<td>".byteify($got)."</td>\n";
				}else{
					echo "\t<td>$out_type:".byteify($out)."</td>\n";
					echo "\t<td>$got_type:".byteify($got)."</td>\n";
				}

				echo "</tr>\n";
			}
		}
	}

	function test_summary(){

		if ($GLOBALS['test_header_done']){
			echo '</table>';
		}

		$total = $GLOBALS['test_passed'] + $GLOBALS['test_failed'];
		if ($total){
			$percent = Round(10000 * $GLOBALS['test_passed'] / $total) / 100;
		}else{
			$percent = 0;
		}

		$color = $GLOBALS['test_failed'] ? 'red' : 'green';

		echo "<h2 style=\"color: $color\">Passed $GLOBALS[test_passed] of $total tests ($percent%)</h2>\n";

		if ($GLOBALS['verbose']){
			echo '<a href="test.php">Hide test details</a>';
		}else{
			echo '<a href="test.php?verbose=1">Show test details</a>';
		}
	}

	function byteify($s){
		$out = '';
		for ($i=0; $i<strlen($s); $i++){
			$c = ord(substr($s,$i,1));
			if ($c == 0x0A){
				$out .= '<span style="color: blue">[\\n]</span>';
			}elseif ($c >= 0x20 && $c <= 0x7f){
				$out .= htmlentities(chr($c));
			}else{
				$out .= '<span style="color: blue">'.sprintf('[%02X]', $c)."</span>";
			}
		}
		return trim($out);
	}

	function c8($i){
		# encode a unicode code point into UTF-8

		if ($i > 0x10000){ # 4 byte
			return	 chr(0xF0 | (($i & 0x1C0000) >> 18))
				.chr(0x80 | (($i & 0x3F000) >> 12))
				.chr(0x80 | (($i & 0xFC0) >> 6))
				.chr(0x80 | ($i & 0x3F));
		}

		if ($i > 0x800){ # 3 byte
			return	 chr(0xE0 | (($i & 0xF000) >> 12))
				.chr(0x80 | (($i & 0xFC0) >> 6))
				.chr(0x80 | ($i & 0x3F));
		}

		if ($i > 0x80){ # 2 byte
			return	 chr(0xC0 | (($i & 0x7C0) >> 6))
				.chr(0x80 | ($i & 0x3F));
		}

		# 1 byte
		return chr($i);
	}

	function test_sanitize($in, $type, $out, $name=null){
		$GLOBALS['tests']['sanitize']++;
		if (!isset($name)) $name = "Untitled sanitize test {$GLOBALS['tests']['sanitize']} ($type)";

		$got = sanitize($in, $type);
		test_harness($in, $out, $got, $name);
	}

	function test_string($in, $out, $name=null){
		$GLOBALS['tests']['string']++;
		if (!isset($name)) $name = "Untitled string test ".$GLOBALS['tests']['string'];

		$got = sanitize($in, 'str');
		test_harness($in, $out, $got, $name);
	}

	function test_exception($code, $name=null){
		$GLOBALS['tests']['exception']++;
		if (!isset($name)) $name = "Untitled exception test ".$GLOBALS['tests']['exception'];

		$thrown = null;

		try {
			eval($code);
		}
		catch (Exception $e){
			$thrown = $e;
		}

		test_harness($code, 'thrown', $thrown ? 'thrown' : 'not thrown', $name);
	}
?>
