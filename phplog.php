<?php

exec("php /home/www/rotinas_jenkins/release-notes-generator/trata_versao.php $argv[3] /home/www/rotinas_jenkins/release-notes-generator $argv[2]");

// Muda para repositório
chdir($argv[2]);

//chdir("/home/stancke/Desenvolvimento/twitter-data-mining");
// Carrega log
$git_history = [];
$git_logs = [];
$tag = exec("git describe --tags `git rev-list --tags --max-count=1`");

//$tagFrom = "1.7";
//$tagTo = "1.8";
//$tag = $tagTo;
$urlLink = "http://m.slcty.co:8080/redmine/issues/";

exec("git log " . $tag . "..", $git_logs);
// Parseia o log
$last_hash = null;
foreach ($git_logs as $line)
{
	// Limpa a linha
	$line = trim($line);
	if (!empty($line))
	{
		// Commit
		if (strpos($line, 'commit') !== false)
		{
		$hash = explode(' ', $line);
		$hash = trim(end($hash));
		$git_history[$hash] = [
		'message' => ''
		];
		$last_hash = $hash;
		}
		// Autor
		else if (strpos($line, 'Author') !== false) {
			$author = explode(':', $line);
			$author = trim(end($author));
			$git_history[$last_hash]['author'] = $author;
		}
		// Data
		else if (strpos($line, 'Date') !== false) {
			$date = explode(':', $line, 2);
			$date = trim(end($date));
			$git_history[$last_hash]['date'] = date('d/m/Y H:i:s A', strtotime($date));
		}
		// Mensagem
		else {
			$git_history[$last_hash]['message'] .= $line ." ";
		}
	}
}

$existBugs = false;
$existFunc = false;

$tagNova = file_get_contents ("/home/www/rotinas_jenkins/release-notes-generator/versao_corrente");

$cabecalho = "##Versão " . $tagNova ." (". date('d/m/Y') . ")".  "##\n";
$bugsCorrigidos = "**Defeitos corrigidos:**\n";
$novasFuncionalidades = "**Novas funcionalidades:**\n";
$rodape = "\* *Este log foi gerado automaticamente a partir do repositório de código-fonte.*";

foreach ($git_history as $line)
{

	//SUBSTITUI AS HASHTAGS POR LINKS
	//preg_match("/#(\\w+)/", $line['message'], $matches);
	//foreach($matches as $match)
	//{
		//print_r($match);
		//$number = str_replace('#','',$match);
		//$link = $urlLinks . "/issues/" . $number[0];
		//$line['message'] = str_replace($match, '<a href="'.$link.'">'.$number[0].'</a>', $line['message']);
	//}

	$line['message'] = hashtag_links($line['message'], $urlLink);

	if (strpos($line['message'],'[FIX]') !== false) {

		$texto = str_replace("[FIX]", "", $line['message']);
		$bugsCorrigidos .= "-" . $texto . "\n";
		$existBugs = true;
	}
	if (strpos($line['message'],'[ENH]') !== false) {

		$texto = str_replace("[ENH]", "", $line['message']);
		$novasFuncionalidades .= "-" . $texto . "\n";   
		$existFunc = true;
	}
}

$saida = $cabecalho . "\n";
if($existFunc)
	$saida .= $novasFuncionalidades . "\n";
if($existBugs)
	$saida .= $bugsCorrigidos . "\n";
#$saida .= $rodape;

chdir($argv[1]);
#file_put_contents ("CHANGELOG.md",$saida, FILE_APPEND);
$fileContents = file_get_contents("CHANGELOG.md");
file_put_contents("CHANGELOG.md", $saida . $fileContents);


function hashtag_links($string, $urlLink) 
{
	preg_match_all('/#(\w+)/',$string,$matches);
	foreach ($matches[1] as $match) 
	{
		$string = str_replace("#$match", "<a href='".$urlLink."$match' target='_blank'>#$match</a>", "$string");
  	}

	return $string;
}


?>
