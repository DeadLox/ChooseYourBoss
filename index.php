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
                if ($buffer != false) {
                    $evt = explode(";", $buffer);
                    if (sizeof($evt) > 1) {
                        $dateDebutExplode = explode("-", $evt[0]);
                        $dateDebutTab[0] = intval($dateDebutExplode[0]);
                        $dateDebutTab[1] = intval($dateDebutExplode[1]);
                        $dateDebutTab[2] = intval($dateDebutExplode[2]);
                        $dateDebutEvt= $evt[0];
                        $endDate = calcEndDate($dateDebutTab, intval($evt[1]));
                        $keyMonth = ($dateDebutTab[1] < 10) ? "0".$dateDebutTab[1] : $dateDebutTab[1];
                        $keyDay = ($dateDebutTab[2] < 10) ? "0".$dateDebutTab[2] : $dateDebutTab[2];
                        $evtTab[] = array("startDate" => $dateDebutTab[0]."".$keyMonth."".$keyDay, "endDate" => $endDate, "duree" => $evt[1]);
                    }
                    asort($evtTab);
                }
            }
        }
        // Evt count
        $nbEvt = 0;
        $currentDate = "";
        if (sizeof($evtTab) > 0) {
            foreach ($evtTab as $key => $evt) {
                if ($currentDate == "") {
                    $currentDate = $evt["endDate"];
                } else {
                    if ($currentDate < $evt['startDate']) {
                        $currentDate = $evt['startDate'];
                        $nbEvt++;
                    }
                }
            }
        }
    }

    echo '<pre>';
    print_r($evtTab);
    echo '</pre>';

    var_dump($nbEvt);
	
    fwrite($stdout, $nbEvt);
    
	fclose($stdout);
	fclose($stdin);
}

function calcEndDate($dateTab, $nbJour){
    $anneeStart = $dateTab[0];
    $moisStart = $dateTab[1];
    $jourStart = $dateTab[2];
    // Init End Date
    $jourEnd = $jourStart;
    $moisEnd = $moisStart;
    $anneeEnd = $anneeStart;

    // remove one day
    $nbJour = intval($nbJour)-1;

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

    $anneeEnd = $anneeEnd;
    $moisEnd = ($moisEnd < 10) ? "0".$moisEnd : $moisEnd;
    $jourEnd = ($jourEnd < 10) ? "0".$jourEnd : $jourEnd;
    return $anneeEnd."".$moisEnd."".$jourEnd;
}

main();
?>