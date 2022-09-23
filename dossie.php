<?php

/* 
 *
 * PARAMETROS PARA ATIVAÇÃO QUE DEVEM SER SUBSTITUIDOS
 * 
 * $login = E-mail cadastrado no SIAN
 * $senha = Senha cadastrada no SIAN
 * $local = Diretório onde este arquivo esteja disponível. Altere caso necessário.
 * 
 */

$login = "XXX";
$senha = "YYY";
$local = "http://localhost:8080/";
$id    = $_GET["id"];

/* 
 * 
 * SCRIPT
 *
 */


//Exibir erros durante a execução
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//Capturar cookie para login
$ch1 = curl_init( "https://sian.an.gov.br/sianex/consulta/login-novo-com-cadastro.asp" );
curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch1, CURLOPT_HTTPHEADER, ['Referer' => 'https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp']);
curl_setopt( $ch1, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch1, CURLOPT_COOKIEFILE, "cookie.txt" );
$output_cookie = curl_exec( $ch1 );
curl_close( $ch1 );

//Realiza login no servidor
$params2 = [
        "login"             => $login,
        "senha"             => $senha,
        "caminho"           => "",
        "destinatario"      => "",
        "fonecontato"       => "",
        "assinaturacontato" => ""
];
$ch2 = curl_init();
curl_setopt( $ch2, CURLOPT_URL, "https://sian.an.gov.br/sianex/consulta/Verifica_Login.asp" );
curl_setopt( $ch2, CURLOPT_POST, true );
curl_setopt( $ch2, CURLOPT_POSTFIELDS, http_build_query( $params2 ) );
curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch2, CURLOPT_HTTPHEADER, ['Referer' => 'https://sian.an.gov.br/sianex/consulta/login-novo-com-cadastro.asp']);
curl_setopt( $ch2, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch2, CURLOPT_COOKIEFILE, "cookie.txt" );
curl_setopt( $ch2, CURLOPT_FOLLOWLOCATION, true);
$output_login = curl_exec( $ch2 );
curl_close( $ch2 );


//Busca dossie
$params3 = [        
        "ID"    => $id
];
$ch3 = curl_init();
curl_setopt( $ch3, CURLOPT_URL, "https://sian.an.gov.br/sianex/consulta/Pesquisa_Livre_Controle.asp" );
curl_setopt( $ch3, CURLOPT_POST, true );
curl_setopt( $ch3, CURLOPT_POSTFIELDS, http_build_query( $params3 ) );
curl_setopt( $ch3, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch3, CURLOPT_HTTPHEADER, array('Referer: https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp') );
curl_setopt( $ch3, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch3, CURLOPT_COOKIEFILE, "cookie.txt" );
$output = curl_exec( $ch3 );
curl_close( $ch3 );

//Verifica se a consulta deu certo. 
//Sem sucesso, aguarda 15 segundos e atualiza página. 
//Com sucesso, retorna conteúdo da página e força download utilizando o código de referência como título.
if ($output == FALSE) {
    sleep(15);
    http_response_code(303);
    header("Location: ".$local."/dossie.php?id=".$id."&time=".time());
} else {
    preg_match_all('/<label style=\'color:red;font-size:12px;\'><i>([^<]*)<\/i>/', $output, $filename);
    if (!isset($filename['1']['0'])) $filename['1']['0'] = $id;

    header('Content-Disposition: attachment; filename="'.trim($filename['1']['0'], " -").'.html"');
    header('Content-Description: File Transfer');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");

    echo($output);
}
