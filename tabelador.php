<pre><?php

/*

Para executar esse script, insira-o em uma pasta separada juntamente com os dossiês que deseja converter e o Html2Text.php

https://github.com/mtibben/html2text/blob/master/src/Html2Text.php

*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once('Html2Text.php');
use Html2Text\Html2Text;

foreach (glob("*.html") as $filename) {
    $html = file_get_contents($filename);
	$html2TextConverter = new Html2Text($html);
	$html_stiped = $html2TextConverter->getText();


	$html_sanitized = preg_replace('/ 1 /', 									' 1(um) ', 	$html_stiped);
	$html_sanitized = preg_replace('/;/',										",",	 	$html_sanitized);
	$html_sanitized = preg_replace('/[\n	]*[^º\d ](\d(?:\.\d)*)(?! - _) -?/',"<br>", 	$html_sanitized);
	$html_sanitized = preg_replace('/(\b\| ?[A-ZÁÉÍÓÚÀÂÊÔÃÕÇ ]{2,}\b)/', 		'$1 |', 	$html_sanitized);
	$html_sanitized = preg_replace('/[\n	 ]{3,}/', 							"\n", 		$html_sanitized);
	$html_sanitized = preg_replace('/[\n ]*<br>/', 								"\n<br>", 	$html_sanitized);
	$html_sanitized = preg_replace('/ *\n(?!<br>)/', 							". ", 		$html_sanitized);
	$html_sanitized = preg_replace('/<br> ?([A-ZÁÉÍÓÚÀÂÊÔÃÕÇ \/()]{2,})[. ]*/', "$1;", 		$html_sanitized);

	$lines = explode("\n", $html_sanitized);

	foreach ($lines as $line) {
		$items = explode(";", $line);

		$items[0] = @trim(str_replace("\xc2\xa0", " ", $items[0]), " .:;,|-_\n");
		$items[1] = @trim(str_replace("\xc2\xa0", " ", $items[1]), " .:;,|-_\n");

		if ($items[0] == "DIMENSÃO E SUPORTE") {
			$components = preg_split("/([^ .\/][A-ZÁÉÍÓÚÀÂÊÔÃÕÇ ]{2,}.?:)/", $items[1], -1, PREG_SPLIT_DELIM_CAPTURE);
			array_shift($components);

			for ($i=0; $i < (count($components)/2); $i+=2) { 
				$list[] = array(
					trim(str_replace("\xc2\xa0", " ", $components[$i]), " .:;,|-_"),
					trim(str_replace("\xc2\xa0", " ", $components[($i+1)]), " .:;,|-_")
				);
			}

			unset($items);
			continue;
		}

		$list[] = array($items[0], $items[1]);
		unset($items);
	}

	$wanted = array(
		"CÓDIGO DE REFERÊNCIA",
		"INDICAÇÃO DO TÍTULO",
		"DATA DE PRODUÇÃO",
		"LOCAL DE PRODUÇÃO",
		"ESPECIFICAÇÃO DE ANEXOS",
		"INDICAÇÃO DE RESPONSABILIDADE"
	);

	foreach ($list as $key) {
		if (in_array($key[0], $wanted)) {
			echo('"'.$key[1].'";');
		}
	}

	echo "<br>";

	unset($list);
}

?>
