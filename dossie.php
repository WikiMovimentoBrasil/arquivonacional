<?php

//Requer ID do dossie
if (!isset($_GET["id"])) die("Forneça número do dossiê");

//Requer script para login
require "login.php";

//Busca dossie
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, "https://sian.an.gov.br/sianex/consulta/Pesquisa_Livre_Controle.asp" );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query(["ID" => $_GET["id"]]));
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Referer: https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp') );
curl_setopt( $ch, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch, CURLOPT_COOKIEFILE, "cookie.txt" );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
$output = curl_exec( $ch );
curl_close( $ch );

//Verifica se a consulta deu certo. 
//Sem sucesso, aguarda 15 segundos e atualiza página. 
//Com sucesso, retorna conteúdo da página e força download utilizando o código de referência como título.
if ($output == FALSE) {

    $time = time();
    sleep(15);
    http_response_code(303);
    header("Location: {$local}/dossie.php?id={$_GET["id"]}&time={$time}");

} else {

    preg_match_all('/<label style=\'color:red;font-size:12px;\'><i>([^<]*)<\/i>/', $output, $filename);
    if (!isset($filename['1']['0'])) $filename['1']['0'] = $_GET["id"];

    header('Content-Disposition: attachment; filename="'.trim($filename['1']['0'], " -").'.html"');
    header('Content-Description: File Transfer');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");

    echo($output);
}

