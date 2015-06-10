<?php
chdir($argv[3]);
$releaseType = $argv[1];
$ultimaTag = exec("git describe --tags `git rev-list --tags --max-count=1`");

$arrayTag = explode(".",$ultimaTag);

if($releaseType == "Minor")
{
	$arrayTag[1]++;
	$arrayTag[2] = 0;
}
else if($releaseType == "Fix")
{
	$arrayTag[2]++;
}

$novaVersao = $arrayTag[0] . "." . $arrayTag[1] . "." . $arrayTag[2];
chdir($argv[2]);
file_put_contents("versao_corrente", $novaVersao);

?>
