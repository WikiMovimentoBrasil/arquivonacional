<?php

//Requer dados do arquivo
if (!isset($_GET["arquivo"])) die("Forneça hash do arquivo");
if (!isset($_GET["NomeArquivo"])) die("Forneça hash do nome do arquivo");
if (!isset($_GET["apresentacao"])) die("Forneça número da apresentação");

//Requer script para login
require "login.php";

//Busca arquivo
$ch = curl_init( "https://sian.an.gov.br/sianex/consulta/download_novo.asp?arquivo={$_GET["arquivo"]}&NomeArquivo={$_GET["NomeArquivo"]}&apresentacao={$_GET["apresentacao"]}" );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_NOBODY, true);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt( $ch, CURLOPT_AUTOREFERER, true);
curl_setopt( $ch, CURLOPT_HEADER, false);
curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Referer' => "https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp"]);
curl_setopt( $ch, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch, CURLOPT_COOKIEFILE, "cookie.txt" );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$target = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

http_response_code(303);
header("Location: ".$target);