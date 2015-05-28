<?php
// Change To Repo Directory
chdir("/home/stancke/Desenvolvimento/twitter-data-mining");
// Load Last 10 Git Logs
$git_history = [];
$git_logs = [];
$begin = "1.6";
$end = "1.7";

exec("git log " . $begin . ".." . $end, $git_logs);
// Parse Logs
$last_hash = null;
foreach ($git_logs as $line)
{
	// Clean Line
	$line = trim($line);
	// Proceed If There Are Any Lines
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
		// Author
		else if (strpos($line, 'Author') !== false) {
			$author = explode(':', $line);
			$author = trim(end($author));
			$git_history[$last_hash]['author'] = $author;
		}
		// Date
		else if (strpos($line, 'Date') !== false) {
			$date = explode(':', $line, 2);
			$date = trim(end($date));
			$git_history[$last_hash]['date'] = date('d/m/Y H:i:s A', strtotime($date));
		}
		// Message
		else {
			$git_history[$last_hash]['message'] .= $line ." ";
		}
	}
}

$cabecalho = "#Notas de Release - Versão" . $end . "#\n";
$bugsCorrigidos = "**Bugs corrigidos:**\n";
$novasFuncionalidades = "**Novas funcionalidades:**\n";

foreach ($git_history as $line)
{

	if (strpos($line['message'],'[FIX]') !== false) {

		$texto = str_replace("[FIX]", "", $line['message']);
		$bugsCorrigidos .= "-" . $texto . "\n";   
	}
	if (strpos($line['message'],'[FEATURE]') !== false) {

		$texto = str_replace("[FEATURE]", "", $line['message']);
		$novasFuncionalidades .= "-" . $texto . "\n";   
	}
}


$saida = $cabecalho . "\n";
$saida .= $novasFuncionalidades . "\n";
$saida .= $bugsCorrigidos;

chdir("/home/stancke/Desenvolvimento/gerador_de_release_notes");
file_put_contents ("CHANGELOG.md",$saida);


?>
