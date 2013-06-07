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
        $dateDebutExplode = explode("-", $evt[0]);
        $dateDebutTab[0] = intval($dateDebutExplode[0]);
        $dateDebutTab[1] = intval($dateDebutExplode[1]);
        $dateDebutTab[2] = intval($dateDebutExplode[2]);
        $dateDebutEvt= $evt[0];
        $endDateTab = calcEndDate($dateDebutTab, intval($evt[1]));
        return array("startDate" => $dateDebutTab, "endDate" => $endDateTab, $evt[1]);
    }
    return false;
}

function calcEndDate($dateTab, $nbJour){
    $anneeStart = $dateTab[0];
    $moisStart = $dateTab[1];
    $jourStart = $dateTab[2];
    echo "Debut: ".$jourStart."/".$moisStart."/".$anneeStart."<br/>";
    // Init End Date
    $jourEnd = $jourStart;
    $moisEnd = $moisStart;
    $anneeEnd = $anneeStart;

    // remove one day
    //$nbJour = intval($nbJour)-1;

    while($nbJour > 0){
        $dayInMonth = cal_days_in_month(CAL_GREGORIAN, $moisEnd, $anneeEnd);
        $dayBeforeLastMonth = $dayInMonth-$jourEnd;

        // less than end of month
        if ($dayBeforeLastMonth > 0 && $dayBeforeLastMonth-$nbJour >= 0) {
            $jourEnd += $nbJour;
            $nbJour = 0;
        // More
        } else {
            $jourEnd = 1;
            $moisEnd++;
            if ($moisEnd > 12) {
                $moisEnd = 1;
                $anneeEnd++;
            }
            $nbJour -= $dayBeforeLastMonth;
        }
    }

    $endDateTab[0] = $anneeEnd;
    $endDateTab[1] = $moisEnd;
    $endDateTab[2] = $jourEnd;
    echo "Fin:   ".$jourEnd."/".$moisEnd."/".$anneeEnd."<br/>";
    return $endDateTab;
}

main();
?>