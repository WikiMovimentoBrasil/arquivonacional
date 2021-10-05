<?php

/* 

PARAMETROS PARA ATIVAÇÃO QUE DEVEM SER SUBSTITUIDOS

LOGIN = E-mail cadastrado no SIAN
SENHA = Senha cadastrada no SIAN
ENDEREÇO = Diretório onde este arquivo esteja disponível

*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Cookie de login
$ch1 = curl_init( "https://sian.an.gov.br/sianex/consulta/login.asp" );
curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch1, CURLOPT_HTTPHEADER, ['Referer' => 'https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp']);
curl_setopt( $ch1, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch1, CURLOPT_COOKIEFILE, "cookie.txt" );
$output_cookie = curl_exec( $ch1 );
curl_close( $ch1 );


//Login
$params2 = [
		"login" 		=> "LOGIN",
		"senha" 		=> "SENHA",
		"caminho" 			=> "",
		"destinatario" 		=> "",
		"fonecontato" 		=> "",
		"assinaturacontato" => ""
	];
$ch2 = curl_init();
curl_setopt( $ch2, CURLOPT_URL, "https://sian.an.gov.br/sianex/consulta/Verifica_Login.asp" );
curl_setopt( $ch2, CURLOPT_POST, true );
curl_setopt( $ch2, CURLOPT_POSTFIELDS, http_build_query( $params2 ) );
curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch2, CURLOPT_HTTPHEADER, ['Referer' => 'https://sian.an.gov.br/sianex/consulta/login.asp']);
curl_setopt( $ch2, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch2, CURLOPT_COOKIEFILE, "cookie.txt" );
curl_setopt( $ch2, CURLOPT_FOLLOWLOCATION, true);
$output_login = curl_exec( $ch2 );
curl_close( $ch2 );


//Busca coleção
if (!isset($_GET["pag"])) $_GET["pag"] = 1;
$params3 = [		
		"input_pesqfundocolecao" 	=> $_GET["colecao"],
		"data_inicio" 				=> "",
		"data_fim" 					=> "",
		"input_pesqnotacao" 		=> "",
		"nivel" 					=> "",
		"checked" 					=> "",
		"txt_pesquisa" 				=> "",
		"v_pesquisa" 				=> "",
		"v_fundo_colecao" 			=> $_GET["colecao"],
		"v_ordem" 					=> "Relevancia",
		"pesquisa" 					=> ""
	];
$ch3 = curl_init( "https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp?v_pesquisa=&v_fundo_colecao=".$_GET["colecao"]."&Pages=".$_GET["pag"] );
curl_setopt( $ch3, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch3, CURLOPT_POST, true );
curl_setopt( $ch3, CURLOPT_POSTFIELDS, http_build_query( $params3 ) );
curl_setopt( $ch3, CURLOPT_HTTPHEADER, ['Referer' => 'https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp']);
curl_setopt( $ch3, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch3, CURLOPT_COOKIEFILE, "cookie.txt" );
$output = curl_exec( $ch3 );
curl_close( $ch3 );

//Verifica se a consulta deu certo. 
//Sem sucesso, aguarda 15 segundos e atualiza página. 
//Com sucesso, retorna lista de links para outras páginas e links de arquivos para download.
if ($output == FALSE) {

	
	sleep(15);
	http_response_code(303);
	header("Location: https://ENDEREÇO/fundo.php?colecao=".$_GET["colecao"]."&pag=".$_GET["pag"]."&time=".time());

} else {

	echo("<pre>");
	preg_match_all('/var TotalPag = (\d*);/', $output, $pages);
	for ($p=1; $p <= $pages['1']['0']; $p++) { 
		echo("<a href='fundo.php?colecao=".$_GET["colecao"]."&pag=".$p."'>Página ".$p."</a>\n");
	}
	echo("<hr>");
  
	  preg_match_all('/download do arquivo" class="help_pesquisa" onClick="javascript:fjs_Link_download\(\'([^\']*)\',\'([^\']*)\',\'([^\']*)\'[\s\S]*?\n<input type = hidden id="in_(\d*)/', $output, $files);

	for ($i=0; $i < count($files['0']); $i++) { 
		echo("<a href='https://sian.an.gov.br/sianex/consulta/download.asp?arquivo=".$files[1][$i]."&NomeArquivo=".$files[2][$i]."&apresentacao=".$files[3][$i]."' download='".$files[2][$i]."'>".$files[2][$i]."</a>\n<a href='dossie.php?id=".$files[4][$i]."'>".$files[4][$i]."</a>\n\n\n");
	}

}
