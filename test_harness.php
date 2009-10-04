<?php
	#
	# A simple PHP test harness
	#
	# $Id$
	#
	# By Cal Henderson <cal@iamcal.com>
	# This code is licensed under a Creative Commons Attribution-ShareAlike 2.5 License
	# http://creativecommons.org/licenses/by-sa/2.5/
	#

	$GLOBALS[tests] = array();
	$GLOBALS[verbose] = $_GET[verbose];

	echo '<table border="1">';
	echo "<tr>\n";
	echo "<th>Name</th>";
	echo "<th>Status</th>";
	echo "<th>Input</th>";
	echo "<th>Expected</th>";
	echo "<th>Got</th>";
	echo "</tr>\n";

	function test_harness($in, $out, $got, $name){

		$output = 0;
		$pass = 0;

		if ($out === $got){
			$GLOBALS[test_passed]++;
			if ($GLOBALS[verbose]){
				$pass = 1;
				$output = 1;
			}
		}else{
			$GLOBALS[test_failed]++;
			$output = 1;
		}
		if ($output){
			if ($GLOBALS[verbose] || ($out != $got)){
				$out_type = gettype($out);
				$got_type = gettype($got);

				echo "<tr>\n";
				echo "\t<td>".HtmlSpecialChars($name)."</td>\n";
				if ($pass){
					echo "\t<td style=\"color: green\">pass</td>\n";
				}else{
					echo "\t<td style=\"background-color: red; color: white\">fail</td>\n";
				}
				echo "\t<td>".HtmlSpecialChars($in)."</td>\n";
				echo "\t<td>$out_type:".htmlentities($out)."</td>\n";
				echo "\t<td>$got_type:".htmlentities($got)."</td>\n";

				echo "</tr>\n";
			}
		}
	}

	function test_summary(){

		echo '</table>';

		$total = $GLOBALS[test_passed] + $GLOBALS[test_failed];
		$percent = Round(10000 * $GLOBALS[test_passed] / $total) / 100;

		echo "<br />\n";
		echo "Passed $GLOBALS[test_passed] of $total tests ($percent%)<br />\n";
	}

?>