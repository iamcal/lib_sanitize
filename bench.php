<?
	header("Content-type: text/html; charset=utf-8");

	include('lib_sanitize.php');

	$GLOBALS[test_loops] = 300;


	#
	# greeking from lipsum.com
	#

	$clean_ascii  = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam nec massa ipsum, eget posuere diam.";
	$clean_ascii .= " Nullam bibendum purus vel purus interdum dictum. Aliquam aliquam lacus at arcu eleifend id sagittis";
	$clean_ascii .= " purus pretium. Phasellus eget porta ipsum. Suspendisse nibh orci, tristique sed semper sed, gravida";
	$clean_ascii .= " et lacus. Nam eget neque scelerisque nunc pellentesque bibendum lobortis sed nisi. Duis iaculis bla";
	$clean_ascii .= "ndit pulvinar. Vivamus elit urna, tincidunt quis pellentesque sed, iaculis vitae dolor. Quisque matt";
	$clean_ascii .= "is dapibus diam eu adipiscing. Maecenas dolor dolor, pellentesque in fermentum at, malesuada nec era";
	$clean_ascii .= "t. Praesent posuere felis eu arcu eleifend scelerisque. Sed sollicitudin auctor nulla, nec blandit m";
	$clean_ascii .= "assa interdum in. Nulla ultricies lacinia orci sit amet consequat. Sed vitae lacus vitae lacus vehic";
	$clean_ascii .= "ula scelerisque vel nec neque. Sed ornare enim a mi sagittis placerat. Proin non dui mi. Proin aucto";
	$clean_ascii .= "r blandit eros non auctor. Nunc non nisi imperdiet massa ultricies pellentesque. In hac habitasse pl";
	$clean_ascii .= "atea dictumst. Vestibulu";


	#
	# some tamil poetry: http://www.columbia.edu/kermit/utf8.html
	#

	$clean_utf8  = "à®¯à®¾à®®à®±à®¿à®¨à¯à®¤ à®®à¯Šà®´à®¿à®•à®³à®¿à®²à¯‡ à®¤à®®à®¿à®´à¯à®®à¯Šà®´à®¿ à®ªà¯‹à®²à¯ à®‡à®©";
	$clean_utf8 .= "à®¿à®¤à®¾à®µà®¤à¯ à®Žà®™à¯à®•à¯à®®à¯ à®•à®¾à®£à¯‹à®®à¯, à®ªà®¾à®®à®°à®°à®¾à®¯à¯ à®µà®¿à®²à®™à¯";
	$clean_utf8 .= "à®•à¯à®•à®³à®¾à®¯à¯, à®‰à®²à®•à®©à¯ˆà®¤à¯à®¤à¯à®®à¯ à®‡à®•à®´à¯à®šà¯à®šà®¿à®šà¯Šà®²à®ªà¯ à®";
	$clean_utf8 .= "ªà®¾à®©à¯à®®à¯ˆ à®•à¯†à®Ÿà¯à®Ÿà¯, à®¨à®¾à®®à®®à®¤à¯ à®¤à®®à®¿à®´à®°à¯†à®©à®•à¯ à®•à¯Šà®£à¯à®Ÿà";
	$clean_utf8 .= "¯ à®‡à®™à¯à®•à¯ à®µà®¾à®´à¯à®¨à¯à®¤à®¿à®Ÿà¯à®¤à®²à¯ à®¨à®©à¯à®±à¯‹? à®šà¯Šà®²à¯à®²à¯€à®°à¯";
	$clean_utf8 .= "!à®¤à¯‡à®®à®¤à¯à®°à®¤à¯ à®¤à®®à®¿à®´à¯‹à®šà¯ˆ à®‰à®²à®•à®®à¯†à®²à®¾à®®à¯ à®ªà®°à®µà¯à®®à¯à®µà®•";
	$clean_utf8 .= "à¯ˆ à®šà¯†à®¯à¯à®¤à®²à¯ à®µà¯‡à®£à¯à®Ÿà¯à®®à¯.à®¯à®¾à®®à®±à®¿à®¨à¯à®¤ à®®à¯Šà®´à®¿à®•à®³à®¿à®²";
	$clean_utf8 .= "à¯‡ à®¤à®®à®¿à®´à¯à®®à¯Šà®´à®¿ à®ªà¯‹à®²à¯ à®‡à®©à®¿à®¤à®¾à®µà®¤à¯ à®Žà®™à¯à®•à¯à®®à¯ à®•à®¾à®";
	$clean_utf8 .= "£à¯‹à®®à¯, à®ªà®¾à®®à®°à®°à®¾à®¯à¯ à®µà®¿à®²à®™à¯à®•à¯à®•à®³à®¾à®¯à¯, à®‰à®²à®•à®©à¯ˆà®¤à¯à®¤à";
	$clean_utf8 .= "¯à®®à¯ à®‡à®•à®´à¯à®šà¯à®šà®¿à®šà¯Šà®²à®ªà¯ à®ªà®¾à®©à¯à®®à¯ˆ à®•à¯†à®Ÿà¯à®Ÿà¯, à®¨à®¾à®®à®®";
	$clean_utf8 .= "à®¤à¯ à®¤à®®à®¿à®´à®°à¯†";


	#
	# the same poetry, with randomly inserted 'X's
	#

	$dirty_utf8  = "à®¯à®¾à®®à®±à®¿à®¨à¯à®¤ à®®à¯Šà®´Xà®¿à®•à®³à®¿à®²à¯‡ à®¤à®®à®¿à®´à¯à®®à¯Šà®´à®¿ à®ªà¯‹à®²à¯ à®‡à®";
	$dirty_utf8 .= "à®¿à®¤à®¾à®µà®¤à¯ à®Žà®™à¯à®•à¯Xà®®à¯ à®•à®¾à®£à¯‹à®®à¯, à®ªà®¾à®®à®°à®°à®¾à®¯à¯ à®µà®¿à®²à®™à";
	$dirty_utf8 .= "à®•à¯à®•à®³à®¾à®¯à¯, à®‰à®²à®•àX®©à¯ˆà®¤à¯à®¤à¯à®®à¯ à®‡à®•à®´à¯à®šà¯à®šà®¿à®šà¯Šà®²à®ªà¯ à";
	$dirty_utf8 .= "ªà®¾à®©à¯à®®à¯ˆ à®•à¯†à®Ÿà¯à®Ÿà¯X, à®¨à®¾à®®à®®à®¤à¯ à®¤à®®à®¿à®´à®°à¯†à®©à®•à¯ à®•à¯Šà®£à¯à®Ÿ";
	$dirty_utf8 .= "¯ à®‡à®™à¯à®•à¯ à®µà®¾à®´à¯à®¨Xà¯à®¤à®¿à®Ÿà¯à®¤à®²à¯ à®¨à®©à¯à®±à¯‹? à®šà¯Šà®²à¯à®²à¯€à®°à¯";
	$dirty_utf8 .= "!à®¤à¯‡à®®à®¤à¯à®°à®¤à¯ à®¤à®®à®X¿à®´à¯‹à®šà¯ˆ à®‰à®²à®•à®®à¯†à®²à®¾à®®à¯ à®ªà®°à®µà¯à®®à¯à®µà®";
	$dirty_utf8 .= "à¯ˆ à®šà¯†à®¯à¯à®¤à®²à¯ à®µà¯‡à®X£à¯à®Ÿà¯à®®à¯.à®¯à®¾à®®à®±à®¿à®¨à¯à®¤ à®®à¯Šà®´à®¿à®•à®³à®¿à®";
	$dirty_utf8 .= "à¯‡ à®¤à®®à®¿à®´à¯à®®à¯Šà®´à®¿ à®Xªà¯‹à®²à¯ à®‡à®©à®¿à®¤à®¾à®µà®¤à¯ à®Žà®™à¯à®•à¯à®®à¯ à®•à®¾à";
	$dirty_utf8 .= "£à¯‹à®®à¯, à®ªà®¾à®®à®°à®°à®¾à®¯àX¯ à®µà®¿à®²à®™à¯à®•à¯à®•à®³à®¾à®¯à¯, à®‰à®²à®•à®©à¯ˆà®¤à¯à®¤";
	$dirty_utf8 .= "¯à®®à¯ à®‡à®•à®´à¯à®šà¯à®šà®¿àX®šà¯Šà®²à®ªà¯ à®ªà®¾à®©à¯à®®à¯ˆ à®•à¯†à®Ÿà¯à®Ÿà¯, à®¨à®¾à®®à®";
	$dirty_utf8 .= "à®¤à¯ à®¤à®®à®¿à®´à®°à¯†";


	#
	# the first chapter of moby dick, translated to japanese with babelfish and converted to SJIS
	#

	$clean_sjis  = "Ž„‚ðIshmael‚Æ“d˜b‚µ‚È‚³‚¢B Ž„‚Ìà•z‚Å‚Ù‚Æ‚ñ‚Ç‚¨‹à‚ðA‚¨‚æ‚Ñ“Á’è‰½‚àŠCŠÝ‚ÌŽ„‚É‹»–¡‚ð‹N‚±‚³‚¹‚éŽ‚Á‚Ä";
	$clean_sjis .= "‚¢‚éA‚ ‚é”N‘O‚É-Œˆ‚µ‚Ä‚Ç‚ÌˆÊ³Šm‚É‹C‚É‚µ‚Ä‚Í‚¢‚¯‚È‚¢-Ž„‚ÍŽ„‚ª‚É‚Â‚¢‚Ä­‚µqŠC‚µA¢ŠE‚Ì…‚ðŠÜ‚ñ‚¾•”";
	$clean_sjis .= "•ª‚ðŒ©‚é‚±‚Æ‚ðl‚¦‚½B ‚»‚ê‚ÍŽ„‚ªäB‘Ÿ‚ð‘–‚è‹Ž‚é‚±‚Æ‚ÌŽ‚Á‚Ä‚¢‚é•û–@A‚¨‚æ‚ÑzŠÂ‚ð’²®‚·‚é‚±‚Æ‚Å‚ ‚éB";
	$clean_sjis .= " Ž„‚ªŽ©•ªŽ©g‚ðŒû‚É‚Â‚¢‚ÄŒµŠi‚Éˆç‚Â‚±‚Æ‚ðŒ©‚Â‚¯‚éŽž‚Í‚¢‚Â‚Å‚à; ‚»‚ê‚ªŽ¼‹C‚Å‚ ‚éŽž‚Í‚¢‚Â‚Å‚àAŽ„‚Ì¸";
	$clean_sjis .= "_‚Ì–¶‰J‚à‚æ‚¤‚Ì11ŒŽ; Ž„‚ªŽ©•ªŽ©g‚ð•s–{ˆÓ‚ÉŠ»‚Ì‘qŒÉ‚Ì‘O‚É‹xŽ~‚µA‚ ‚ç‚ä‚é‘’Ž®‚ÌŒã•”‚ÉŽ„‚ðˆç‚Ä‚é‚±‚Æ‚";
	$clean_sjis .= "ðŒ©‚Â‚¯‚éŽž‚Í‚¢‚Â‚Å‚à‰ï‚¢‚È‚³‚¢; ‚»‚µ‚Ä“Á‚ÉŽ„‚ÌƒnƒCƒ|‚ªŽ„‚æ‚è‚»‚Ì‚æ‚¤‚È—D¨‚Å‚ ‚éŽž‚Í‚¢‚Â‚Å‚àA‚»‚ê‚";
	$clean_sjis .= "»‚ê‚Í‹­‚¢“¹‹`‚ªŽ„‚ª’Ê‚è‚ÉTd‚É•à‚ÝA‘gD“I‚Épeople&#039‚ð‚½‚½‚­‚±‚Æ‚ð–h‚®‚æ‚¤‚É—v‹‚·‚é; ˆÈŠO‚Ìs‚Ì–";
	$clean_sjis .= "XŽq‚»‚µ‚ÄAŽ„‚Í‚»‚ê’ªŽžŽ„‚ª‚Å‚«‚é‚Æ‚·‚®ŠC‚É’…‚­à–¾‚·‚éB ‚±‚ê‚ÍƒsƒXƒgƒ‹‚¨‚æ‚Ñ‹…‚ÌŽ„‚Ì‘ã—‚Å‚ ‚éB “";
	$clean_sjis .= "NŠw‚Ì‰Ø—í‚³‚É‚æ‚Á‚ÄCato‚Í”Þ‚ÌŒ•‚É”ÞŽ©g‚ð“Š‚°‚é; Ž„‚Í‘D‚ÉÃ‚©‚ÉŽæ‚éB ‚±‚ê‚ÅˆÓŠO‚È‰½‚à‚È‚¢B ”Þ‚ç‚ª‚";
	$clean_sjis .= "µ‚©‚µ‚»‚ê‚ð’m‚Á‚Ä‚¢‚½‚çA”Þ‚ç‚Ì’ö“xAŽžŠÔ‚Ü‚½‚Í‘¼‚Ì‚Ù‚Æ‚ñ‚Ç‚·‚×‚Ä‚Ìl‚ÍAŽ„‚ª•t‚¢‚Ä‚¢‚éŠC—m‚Ì•û‚Ì“¯‚";
	$clean_sjis .= "¶Š´‚¶‚ð‚à‚¤­‚µ‚Ì‚Æ‚±‚ë‚Å‘åŽ–‚É‚·‚éB";


	#
	# the first chapter of moby dick, translated to french with babelfish and converted to Latin-1
	#

	$clean_latin1  = "Appelez-moi Ishmael. Quelques années il y a - ne vous occupez jamais de combien de temps avec précis";
	$clean_latin1 .= "ion - ayant peu ou pas d'argent dans ma bourse, et rien particulière pour m'intéresser sur le rivage";
	$clean_latin1 .= ", j'ai pensé que je naviguerais au sujet de l'et verrais la partie aqueuse du monde. Il est une mani";
	$clean_latin1 .= "ère que j'ai de chasser la rate, et réglementation de la circulation. Toutes les fois que je me trou";
	$clean_latin1 .= "ve m'élever sinistre au sujet de la bouche ; toutes les fois que c'est une humidité, novembre bruine";
	$clean_latin1 .= "ux dans mon âme ; toutes les fois que je me trouve faire une pause involontairement avant des entrep";
	$clean_latin1 .= "ôts de cercueil, et m'amener vers le haut à l'arrière de chaque enterrement réunissez-vous ; et part";
	$clean_latin1 .= "iculièrement toutes les fois que mes hypos obtiennent un dessus si de moi, cela il exige d'un princi";
	$clean_latin1 .= "pe moral fort de m'empêcher de faire un pas délibérément dans la rue, et de frapper méthodiquement p";
	$clean_latin1 .= "eople' ; chapeaux de s au loin - puis, je rends compte il grand temps d'arriver à la mer dès que je ";
	$clean_latin1 .= "pourrai. C'est mon produit";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>lib_sanitize Benchmarks</title>
</head>
<body>

<h1>lib_sanitize Benchmarks</h1>

<ul>
	<li> Each test is run <b><?=$GLOBALS[test_loops]?> times</b> (you can modify a line at the top of this file to change that). </li>
	<li> iconv is generally fastest when not converting from another encoding. </li>
	<li> mbstring is generally fastest when converting from another encoding. </li>
	<li> PHP is significantly slower than both. </li>
	<li> PHP mode can't convert from anything other than Latin-1. </li>
	<li> The first number is time taken to perform the test set. </li>
	<li> The second number is how many loops can be performed per second. </li>
	<li> Refreshing will likely get you different results, with the same ratios. </li>
</ul>


<?
	do_test("", "Empty String");
	do_test($clean_ascii, "1K of clean ASCII");
	do_test($clean_utf8, "1K of clean UTF8");
	do_test($dirty_utf8, "1K of dirty UTF8");

	$GLOBALS['sanitize_input_encoding'] = 'SJIS';
	do_test($clean_sjis, "1K of clean Shift_JIS");

	$GLOBALS['sanitize_mode'] = SANITIZE_INVALID_CONVERT;
	$GLOBALS['sanitize_convert_from'] = 'ISO-8859-1'; # Latin-1
	$GLOBALS['sanitize_input_encoding'] = 'UTF-8';
	do_test($clean_latin1, "1K of fallback Latin-1");


	function do_test($input, $name){

		echo "<h2>".HtmlSpecialChars($name)."</h2>\n";


		$exts = array(
			SANITIZE_EXTENSION_MBSTRING	=> 'MBSTRING', 
			SANITIZE_EXTENSION_ICONV	=> 'ICONV',
			SANITIZE_EXTENSION_PHP		=> 'PHP',
		);

		$results = array();
		$max = 0;

		foreach ($exts as $ext => $lbl){

			$GLOBALS['sanitize_extension'] = $ext;
			$start = microtime_micro();
			$error = 0;
			for ($i=0; $i<$GLOBALS[test_loops]; $i++){
				try {
					$temp = sanitize_string($input, 1);
				} catch (Exception $e){
					$temp = 'ERR';
					$error = 1;
				}
			}
			$t = microtime_micro() - $start;
			$ps = round(1000000 * $GLOBALS[test_loops] / $t);
			$results[$ext] = array(
				't' => round($t / 1000),
				'ps' => $ps,
				'e' => $error,
				'last' => $temp,
			);

			if (!$error){
				$max = max($max, $ps);
			}
		}

		echo "<table border=\"1\" cellpadding=\"4\">\n";
		echo "<tr>\n";
		echo "<th>Extension</th>\n";
		echo "<th>Time</th>\n";
		echo "<th>Rate</th>\n";
		echo "<th>&nbsp;</th>\n";
		echo "</tr>\n";


		foreach ($exts as $ext => $lbl){

			echo "<tr>\n";
			echo "<td>$lbl</td>\n";

			if ($results[$ext][e]){
				echo "<td align=\"center\">n/a</td>";
				echo "<td align=\"center\">n/a</td>";
				echo "<td>&nbsp;</td>";
			}else{
				echo "<td align=\"right\">".number_format($results[$ext][t])."ms</td>";
				echo "<td align=\"right\">".number_format($results[$ext][ps])."/s</td>";

				$w = round(300 * ($results[$ext][ps] / $max));

				$col = ($results[$ext][ps] == $max) ? '#0c0' : '#9f9';

				echo "<td align=\"left\"><div style=\"height: 30px; width: {$w}px; background-color: $col\"></div></td>";

				#echo substr($results[$ext][last], 0, 20);
			}
			echo "</td>\n";

			echo "</tr>\n";

		}

		echo "</table>\n";
	}

	function microtime_micro(){ 
		list($usec, $sec) = explode(" ", microtime()); 
		return round(1000000 * ((float)$usec + (float)$sec));
	}
?>

</body>
</html>