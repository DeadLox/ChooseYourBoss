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
            $buffer = fgets($stdin, 4096);
            // La première ligne contient le nombre total d'évènements
            if ($line == 0) {
                $nbEvt = $buffer;

            } else {
                // On vérifie qu'on ne dépasse pas le nombre d'évènements définit au début
                if ($line <= $nbEvt) {
                    // On récupère la date de début et la durée de l'évènement
                    $evt = explode(";", $buffer);
                    if (sizeof($evt) > 1) {
                        // Récupère les valeurs Année Mois Jour pour la date de début
                        $dateDebutExplode = explode("-", $evt[0]);
                        $dateDebutTab[0] = intval($dateDebutExplode[0]);
                        $dateDebutTab[1] = intval($dateDebutExplode[1]);
                        $dateDebutTab[2] = intval($dateDebutExplode[2]);
                        $dateDebutEvt= $evt[0];
                        // Calcul la date de fin
                        $endDate = calcEndDate($dateDebutTab, intval($evt[1]));
                        // On rajoute des zéros devant pour faciliter le tri par date de début
                        $keyMonth = ($dateDebutTab[1] < 10) ? "0".$dateDebutTab[1] : $dateDebutTab[1];
                        $keyDay = ($dateDebutTab[2] < 10) ? "0".$dateDebutTab[2] : $dateDebutTab[2];
                        $evtTab[] = array("startDate" => $dateDebutTab[0]."".$keyMonth."".$keyDay, "endDate" => $endDate, "nbDay" => $evt[1]);
                    }
                    // Tri du tableau par date de début puis par durée
                    usort($evtTab, "evtComparator");
                }
            }
            $line++;
        }
        // Evt count
        $nbEvt = 0;
        $currentDate = "";
        if (sizeof($evtTab) > 0) {

            // Retire les évènements chevauchant sur plus d'un autre évènement
            $evtTab = evtFilter($evtTab);

            // Compte le nombre d'évènements non chevauchant 
            foreach ($evtTab as $key => $evt) {
                if ($currentDate == "") {
                    $currentDate = $evt["endDate"];
                    $nbEvt++;
                } else {
                    // Si la date de fin du premier ne chevauche pas la date de début du deuxième
                    if ($currentDate < $evt['startDate']) {
                        $currentDate = $evt['endDate'];
                        $nbEvt += 1;
                    }
                }
            }
        }
    }

    var_dump($nbEvt);
	
    fwrite($stdout, $nbEvt);
    
	fclose($stdout);
	fclose($stdin);
}

/**
 * Retire du tableau d'évènement les évènements dépassant sur plus de deux autres évènements
 */
function evtFilter($evtTab){
    $evtToDel = array();
    // On parcours les évènements
    foreach ($evtTab as $key => $evtToCheck) {
        // Nombre de fois que l'évènement chevauche un autre évènement
        $nbOverlap = 0;
        // On reparcours tous les évènements
        foreach ($evtTab as $key2 => $evt) {
            // Si c'est pas le même évènement et que la date de début est inférieur à celle du deuxième et que la date de fin est supérieur à la date de début du deuxième
            // Alors c'est un évènement chevauchant
            if ($key != $key2 && $evtToCheck['startDate'] <= $evt['startDate'] && $evtToCheck['endDate'] >= $evt['startDate']) {
                $nbOverlap++;
                // Si il chevauche plus d'un autre évènement, alors on l'ajoute au évènement à supprimer
                if ($nbOverlap > 1) {
                    $evtToDel[] = $key;
                }
            }
        }
        $evtTab[$key]['nbOverlap'] = $nbOverlap;
    }
    if (sizeof($evtToDel) > 0) {
        foreach ($evtToDel as $value) {
            // ON supprime tous les évènements chevauchant plus d'un autre évènement
            unset($evtTab[$value]);
        }
    }
    return $evtTab;
}

/**
 * Calcul la date de fin des évènements
 */
function calcEndDate($dateTab, $nbJour){
    $anneeStart = $dateTab[0];
    $moisStart = $dateTab[1];
    $jourStart = $dateTab[2];
    // Initialise la date de fin avec la date de début
    $jourEnd = $jourStart;
    $moisEnd = $moisStart;
    $anneeEnd = $anneeStart;

    // On enlève un jour au nombre de jour pour compter le jour de début dans la durée
    $nbJour = intval($nbJour)-1;

    // tant qu'il reste des jours
    while($nbJour > 0){
        $dayInMonth = cal_days_in_month(CAL_GREGORIAN, $moisEnd, $anneeEnd);
        $dayBeforeLastMonth = $dayInMonth-$jourEnd;

        // La durée ne dépasse pas le nombre de jours restant avant la fin du mois courant
        if ($dayBeforeLastMonth > 0 && $dayBeforeLastMonth-$nbJour >= 0) {
            $jourEnd += $nbJour;
            $nbJour = 0;
        // La durée dépasse le nombre de jours restant avant la fin du mois
        } else {
            $jourEnd = 1;
            $moisEnd++;
            // Si on est supérieur à douze, alors on change d'année
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

/**
 * Tri le tableau d'évènement par date de début puis par durée
 */
function evtComparator($a, $b){
    if ($a['startDate'] == $b['startDate']) {
        if ($a['nbDay'] < $b['nbDay']) {
            return -1;
        } else {
            return 1;
        }
    }
    return ($a['startDate'] < $b['startDate']) ? -1 : 1;
}

main();
?>