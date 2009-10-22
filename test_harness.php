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

		echo '</table>';

		$total = $GLOBALS[test_passed] + $GLOBALS[test_failed];
		$percent = Round(10000 * $GLOBALS[test_passed] / $total) / 100;

		echo "<br />\n";
		echo "Passed $GLOBALS[test_passed] of $total tests ($percent%)<br />\n";
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





?>