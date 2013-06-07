<?php
function main()
{
	$stdin = fopen('listeEvt.txt', 'r');
	$stdout = fopen('php://stdout', 'w');
    
    $nbEvt = 0;
    $evtTab = array();
	    
    if ($stdin) {
        $line = 0;
        while (!feof($stdin)) {
            $line++;
            $buffer = fgets($stdin, 4096);
            if ($line == 0) {
                $nbEvt = $buffer;
            } else {
                $evt = explodeEvt($buffer);
                if ($evt != false) {
                    $evtTab[] = $evt;
                }
            }
        }
    }
    
    echo '<pre>';
    var_dump($evtTab);
    echo '</pre>';
	
    fwrite($stdout, "coucou");
    
	fclose($stdout);
	fclose($stdin);
}

function explodeEvt($evt) {
    $evtTab = array();
    $evt = explode(";", $evt);
    if (sizeof($evt) > 1) {
        $dateDebutTab = explode("-", $evt[0]);
        //$dateDebutEvt= str_replace("-", "", $evt[0]);
        $dateDebutEvt= $evt[0];
        $nbJour = $evt[1];
        $endDateTab = calcEndDate($dateDebutTab, $evt[1]);
        return array("startDate" => $dateDebutTab, "endDate" => $endDateTab, $nbJour);
    }
    return false;
}

function calcEndDate($dateTab, $nbJour){
    $endDateTab = $dateTab;
    $anneStart = $dateTab[0];
    $moisStart = $dateTab[1];
    $jourStart = $dateTab[2];
    echo $jourStart+"/"+$moisStart+"/"+$anneStart;
    if ($nbJour < 10) $nbJour = "0".$nbJour;
    return $endDateTab;
}
    
main();
?>