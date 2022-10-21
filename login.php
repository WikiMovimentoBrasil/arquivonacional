<pre><?php

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
$local = "http://[::1]:8080/";

/* 
 * 
 * SCRIPT
 *
 */

//Exibir erros durante a execução
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(60);

//Capturar cookie para login
$ch1 = curl_init( "https://sian.an.gov.br/sianex/consulta/login-novo-com-cadastro.asp" );
curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch1, CURLOPT_HTTPHEADER, ['Referer' => 'https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp']);
curl_setopt( $ch1, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch1, CURLOPT_COOKIEFILE, "cookie.txt" );
curl_setopt( $ch1, CURLOPT_FOLLOWLOCATION, true);
curl_setopt( $ch1, CURLOPT_SSL_VERIFYPEER, false);
$output_cookie = curl_exec( $ch1 );
curl_close( $ch1 );

//Verifica se cookie já está autenticado
if (strpos($output_cookie, "<!--Sessao: s") === false) {
    
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
    curl_setopt( $ch2, CURLOPT_SSL_VERIFYPEER, false);
    $output_login = curl_exec( $ch2 );
    curl_close( $ch2 );
}