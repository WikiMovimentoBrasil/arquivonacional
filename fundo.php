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
        "checked"                   => "checked",
        "arquivo_digital"           => "arquivo_digital",
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

    //Início da tabela
    echo("<table border='1'>");
  
    //Isola cada linha e inicia loop
    preg_match_all('/<li><span class="titulo_conteudo">[\s\S]*?<\/li>/', $output, $files);
    foreach ($files['0'] as $key => $file) {

        //Verifica se há link para download
        preg_match('/javascript:fjs_Link_download\(\'([^\']*)\',\'([^\']*)\'/', $file, $filecode);

        //Continua para próxima linha caso linha atual não possua código para download
        if(!isset($filecode['1'])) continue;

        //Recupera nome do arquivo
        preg_match('/BR_[\w]{2}AN[\w]{3}_[^ \.\x{005C}]*\.[\w]*/', $file, $filename);

        //Recupera alias do arquivo para associar com seu dossiê
        preg_match('/BR [\w]{2}AN[\w]{3} [^-]*/', $file, $filealias);

        //Fallback caso alias não exista
        if(!isset($filealias['0'])) {
            $filealias['0'] = $filename['0'];
        }

        //Elimina espaços desnecessários
        $filealias['0'] = trim($filealias['0']);

        //Recupera número do dossiê
        preg_match('/javascript:mudapagina_link\(\'in_([\d]*)/', $file, $filenumber);

        echo("
            <tr>
                <td>".$pagin."</td>
                <td>
                    <a 
                    href='arquivo.php?arquivo={$filecode['1']}&NomeArquivo={$filecode['2']}&apresentacao=1' 
                    download='{$filename['0']}'
                    >{$filename['0']}</a>
                </td>
                <td>
                    <a href='dossie.php?id={$filenumber['1']}'>{$filealias['0']}.html</a>
                </td>
            </tr>
        ");

    }
    
    //Fecha tabela
    echo("</table>");

    //Fecha html
    echo("</pre>");

}
