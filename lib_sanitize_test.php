<?php
	#
	# $Id$
	#
	# By Cal Henderson <cal@iamcal.com> 
	# This code is licensed under a Creative Commons Attribution-ShareAlike 2.5 License
	# http://creativecommons.org/licenses/by-sa/2.5/
	#

	include("lib_sanitize.php");
	include("test_harness.php");


	# basics
	test_sanitize("", "str", "");
	test_sanitize("hello", "str", "hello");
	test_sanitize(1, "str", "1");



	#
	# carriage return normalization
	#

	test_sanitize(c8(0x2028), 'str', " ");
	test_sanitize(c8(0x2028), 'str_multi', "\n");

	test_sanitize(c8(0x2029), 'str', " ");
	test_sanitize(c8(0x2029), 'str_multi', "\n\n");


	test_summary();




	function test_sanitize($in, $type, $out, $name=null){
		$GLOBALS[tests][sanitize]++;
		if (!isset($name)) $name = "Unknown sanatize test {$GLOBALS[tests][sanitize]} ($type)";

		$got = sanitize($in, $type);
		test_harness($in, $out, $got, $name);
	}

	function test_string($in, $out, $name=null){
		$GLOBALS[tests][string]++;
		if (!isset($name)) $name = "Unknown string test ".$GLOBALS[tests][string];

		$got = sanitize($in, 'str');
		test_harness($in, $out, $got, $name);
	}
?>