<pre><?php

//Requer número da coleção
if (!isset($_GET["colecao"])) die();
$colec = $_GET["colecao"];

//Define número da página
if (!isset($_GET["pag"])) {
    $pagin = 1;
} else {
    $pagin = $_GET["pag"];
}

//Requer script para login
require "login.php";

//Consulta à coleção
$params = [        
        "input_pesqfundocolecao"    => $colec,
        "data_inicio"               => "",
        "data_fim"                  => "",
        "input_pesqnotacao"         => "",
        "nivel"                     => "",
        "checked"                   => "",
        "txt_pesquisa"              => "",
        "v_pesquisa"                => "",
        "v_fundo_colecao"           => $colec,
        "v_ordem"                   => "Relevancia",
        "pesquisa"                  => ""
];
$ch = curl_init( "https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp?v_pesquisa=&v_fundo_colecao=".$colec."&Pages=".$pagin );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Referer' => 'https://sian.an.gov.br/sianex/consulta/resultado_pesquisa_new.asp']);
curl_setopt( $ch, CURLOPT_COOKIEJAR, "cookie.txt" );
curl_setopt( $ch, CURLOPT_COOKIEFILE, "cookie.txt" );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
$output = curl_exec( $ch );
curl_close( $ch );

//Verifica se a consulta deu certo. 
//Sem sucesso, aguarda 15 segundos e atualiza página. 
//Com sucesso, retorna lista de links para outras páginas e links de arquivos para download.
if ($output == FALSE) {

    
    sleep(15);
    http_response_code(303);
    header("Location: ".$local."/fundo.php?colecao=".$colec."&pag=".$pagin."&time=".time());

} else {

    //Abre html
    echo("<pre>");

    //Captura número de páginas no fundo e cria loop para exibição da lista
    preg_match_all('/var TotalPag = (\d*);/', $output, $pages);
    for ($p=1; $p <= $pages['1']['0']; $p++) { 
        echo("<a href='fundo.php?colecao=".$colec."&pag=".$p."'>Página ".$p."</a>\n");
    }

    //Linha para diferenciar seções
    echo("<hr>");
  
    //Captura arquivos para download na página atual e cria loop para exibição da lista
    preg_match_all('/<li><span[^>]*><a[^>]*>([^-]*) -[\s\S]*?Visualizar arquivo" class="help_pesquisa" onClick="javascript:fjs_Link_download\(\'([^\']*)\',\'([^\']*)\',\'([^\']*)\'\);">(?: ARQUIVO\.\: )?([^<]*)[\s\S]*?\n<input type = hidden id="in_(\d*)/', $output, $files);
    for ($i=0; $i < count($files['0']); $i++) {
        $files[5][$i] = explode(" ", $files[5][$i]);
        if (is_array($files[5][$i])) $files[5][$i] = end($files[5][$i]);
        echo("<a href='arquivo.php?arquivo=".$files[2][$i]."&NomeArquivo=".$files[3][$i]."&apresentacao=".$files[4][$i]."' download='".$files[5][$i]."'>".$files[5][$i]."</a>\n<a href='dossie.php?id=".$files[6][$i]."'>".$files[1][$i]."</a> (html)\n\n\n");
    }

    //Fecha html
    echo("</pre>");

}
