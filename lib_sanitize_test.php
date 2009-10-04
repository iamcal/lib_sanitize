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


	test_summary();

	function test_sanitize($in, $type, $out){
		$got = sanitize($in, $type);
		test_harness($in, $out, $got, "Sanitize test ".++$GLOBALS[tests][sanitize]." ($type)");
	}

?>