<?php

    $ranges = [];
    foreach($targets as $target) {
        $found = false;
        for($i = 0; $i < sizeof($ranges); $i++) {
            if($target['RangeID'] == $ranges[$i][0]) $found = true;
        }

        //0: RangeID, 1: Range Start, 2: Range End, 3: Range Width, 4: Display-width prozentual, 5: Left-position prozentual,
        //6: Array der Ranges in denen diese Range liegt, 7: Überstand-Width, 8: Original Range Width (da 3 überschrieben wird), 9: Range name
        if(!($found)) array_push($ranges, [$target['RangeID'], $target['rngStart'], $target['rngEnd'], $target['rngWidth'], 0, 0, [], 0, $target['rngWidth'], $target['RngName']]);
    }

    $widthsum = 0;
    $distinctranges = 0;
    for($i = 0; $i < sizeof($ranges); $i++) {
        for($j = 0; $j < $i; $j++) {
            //liegt die range in einer anderen?
            if($ranges[$i][1] < $ranges[$j][2]) {
                 array_push($ranges[$i][6], $j);
                //ragt die range über das ende der darunter liegenden heraus?
                if($ranges[$i][2] > $ranges[$j][2]) {
                    if($ranges[$i][7] == 0) {
                        $ranges[$i][7] = $ranges[$i][2] - $ranges[$j][2];
                    } else {
                        $ranges[$i][7] = min($ranges[$i][7], $ranges[$i][2] - $ranges[$j][2]);
                    }
                }
            }
        }
        if(sizeof($ranges[$i][6]) > 0) {
            $widthsum += $ranges[$i][7];
        } else {
            $widthsum += $ranges[$i][3];
            $distinctranges += 1;
        }
    }

    //nach minimal-width prüfen
    $difference = 0;
    $minimumwidth = 0.015;
    $separatorweight = 0.02;
    for($i = 0; $i < sizeof($ranges); $i++) {
        if( ($ranges[$i][3] / $widthsum) < $minimumwidth ) {
            $tempdifference = ($widthsum * $minimumwidth) - $ranges[$i][3];
            $ranges[$i][3] = $widthsum * $minimumwidth;
            //alle darunter liegenden ranges verlängern
            for($j = 0; $j < sizeof($ranges[$i][6]); $j++) {
                $ranges[$ranges[$i][6][$j]][3] += $tempdifference;
            }
            $difference += $tempdifference;
        }
    }
    $widthsum += $difference;

    //Ab hier wird Folgendes berechnet:
    //$range[4] = width prozentual
    //$range[5] = left-position prozentual
    $sidespacing = 0.02;
    $separatorwidth = $separatorweight * 100 * (1 - 2 * $sidespacing) / (1 + $separatorweight * ($distinctranges - 1));
    $templeft = $sidespacing * 100;
    if($pdf == true) {
        $tempsvg = '<svg width="930px" height="80px" xmlns="http://www.w3.org/2000/svg">
                <line x1="0%" y1="25" x2="100%" y2="25" style="stroke:black;stroke-width:2" />';
    } else {
        $tempsvg = '<svg width="100%" height="80px" xmlns="http://www.w3.org/2000/svg">
                <line x1="0%" y1="25" x2="100%" y2="25" style="stroke:black;stroke-width:2" />';
    }
    $indent = false;
    for($i = 0; $i < sizeof($ranges); $i++) {
        $ranges[$i][4] = ($ranges[$i][3] / $widthsum) * 100 * (1 - 2 * $sidespacing) / (1 + $separatorweight * ($distinctranges - 1));

        if($i == 0) {
            $ranges[$i][5] = $templeft;
            $templeft += $ranges[$i][4];
        } else {
            if(sizeof($ranges[$i][6]) == 0) {
                $ranges[$i][5] = $templeft + $separatorwidth;
                $templeft += $separatorwidth + $ranges[$i][4];
            } else {

                $ranges[$i][5] = $ranges[$ranges[$i][6][0]][5] + $ranges[$ranges[$i][6][0]][4] * (($ranges[$i][1] - $ranges[$ranges[$i][6][0]][1]) / $ranges[$ranges[$i][6][0]][3]);
                //templeft nur vergrößern, wenn range herausragt
                $templeft += $ranges[$i][7];
            }
        }

        if(sizeof($ranges[$i][6]) == 0) {
            $tempsvg .= '<rect x="' . $ranges[$i][5] . '%" y="10" height="30" rx="10" ry="10" width="' . $ranges[$i][4] . '%" style="fill: #ffffff"/>';
            $tempsvg .= '<defs><clipPath id="' . $ranges[$i][0] . '">
            <rect x="' . $ranges[$i][5] . '%" y="10" height="30" rx="10" ry="10" width="' . $ranges[$i][4] . '%" style="fill: #ffffff"/>
            </clipPath></defs>';
        } else {
            $tempsvg .= '<rect x="' . $ranges[$i][5] . '%" y="10" height="30" width="' . $ranges[$i][4] . '%" style="fill: #ffffff"/>';
        }
        //echo '<div style="background-color:white; height:50px; border:1px solid black; position:absolute; width:' . $ranges[$i][4] . '%; left:' . $ranges[$i][5] . '%;">';


        $targetcolors = ['#22670B', '#7EB838', '#B8CF3E', '#E3BF3D', '#DB7432', '#B04925', '#902B1D', '#90191C'];
        foreach($targets as $target) {
            if($target['RangeID'] == $ranges[$i][0]) {
                if(isset($target['tgtStart'])) {
                    $targetleft = $ranges[$i][5] + ($target['tgtStart'] - $ranges[$i][1]) * $ranges[$i][4] / $ranges[$i][8];
                    $targetwidth = $target['tgtWidth'] * $ranges[$i][4] / $ranges[$i][8];
                    $targetcoverage = ceil($target['NrBadCovered'] * 100/ $target['tgtWidth']);
                    if($targetcoverage > 7) $targetcoverage = 7;

                    if(sizeof($ranges[$i][6]) == 0) {
                        $tempsvg .= '<rect x="' . $targetleft . '%" y="10" height="30" width="' . $targetwidth . '%" style="fill: ' . $targetcolors[$targetcoverage] . '" clip-path="url(#' . $ranges[$i][0] . ')" />';
                    } else {
                        $tempsvg .= '<rect x="' . $targetleft . '%" y="10" height="30" width="' . $targetwidth . '%" style="fill: ' . $targetcolors[$targetcoverage] . '"/>';
                    }

                    //$targetleft = ($target['tgtStart'] - $ranges[$i][1]) * 100 / $ranges[$i][8];
                    //$targetwidth = $target['tgtWidth'] * 100 / $ranges[$i][8];
                    //$targetcoverage = floor($target['NrBadCovered'] * 255/ $target['tgtWidth']);
                    //echo '<div style="background-color:rgb(' . (255 - $targetcoverage) . ',' . $targetcoverage . ',0); height:50px; position:absolute; width:' . $targetwidth . '%; left:' . $targetleft . '%;"></div>';
                }
            }
        }

        if(sizeof($ranges[$i][6]) == 0) {
            $tempsvg .= '<rect x="' . $ranges[$i][5] . '%" y="10" height="30" rx="10" ry="10" width="' . $ranges[$i][4] . '%" style="stroke:#000000; fill: none; stroke-width: 1;"/>';
        } else {
            $tempsvg .= '<rect x="' . $ranges[$i][5] . '%" y="10" height="30" width="' . $ranges[$i][4] . '%" style="stroke:#000000; fill: none; stroke-width: 1;"/>';
        }


        if(!$indent && $i != 0 && ($ranges[$i][5] - $ranges[$i - 1][5]) < 8) {
            $tempsvg .= '<text x="' . $ranges[$i][5] . '%" y="75" width="' . $ranges[$i][4] . '%" font-size="10">' . $ranges[$i][9] . '</text>';
            $tempsvg .= '<line x1="' . ($ranges[$i][5] + 0.6) . '%" y1="62" x2="' . ($ranges[$i][5] + 0.6) . '%" y2="43" style="stroke:grey;stroke-width:1" />';
            $indent = true;
        } else {
            $tempsvg .= '<text x="' . $ranges[$i][5] . '%" y="65" width="' . $ranges[$i][4] . '%" font-size="10">' . $ranges[$i][9] . '</text>';
            $tempsvg .= '<line x1="' . ($ranges[$i][5] + 0.6)  . '%" y1="52" x2="' . ($ranges[$i][5] + 0.6)  . '%" y2="43" style="stroke:grey;stroke-width:1" />';
            $indent = false;
        }

        //echo '<p style="position:absolute; font-size:10px; top:40px;">' . $ranges[$i][9] . '</p>';
        //echo '</div>';
    }

    if(sizeof($rowsRelevant) > 0) {
        $variantcolors = ['#1DFFF5', '#D33EFF', '#F5FF17', '#2CFF3F', '#277AFF'];
        $coloriterator = 0;
        foreach($rowsRelevant as $rowRelevant) {
            for($i = 0; $i < sizeof($ranges); $i++) {
                if($rowRelevant['pos'] > $ranges[$i][1] && $rowRelevant['pos'] < $ranges[$i][2]) {
                    $variantposition = $ranges[$i][5] + $ranges[$i][4] * (($rowRelevant['pos'] - $ranges[$i][1]) / $ranges[$i][8]);
                    $tempsvg .= '<line x1="' . $variantposition  . '%" y1="39" x2="' . $variantposition  . '%" y2="11" style="stroke:' . $variantcolors[$coloriterator] . ';stroke-width:2" />';
                    $tempsvg .= '<line x1="' . ($variantposition - 0.3)  . '%" y1="3" x2="' . $variantposition  . '%" y2="9" style="stroke:' . $variantcolors[$coloriterator] . ';stroke-width:2" />';
                    $tempsvg .= '<line x1="' . ($variantposition + 0.3)  . '%" y1="3" x2="' . $variantposition  . '%" y2="9" style="stroke:' . $variantcolors[$coloriterator] . ';stroke-width:2" />';

                    $coloriterator += 1;
                    $coloriterator = $coloriterator % 5;
                    $i = sizeof($ranges) + 1;
                }
            }
        }
    }


    $tempsvg .= '</svg>';

    if($pdf == true && 0 == 1) {
        if (!file_exists('/var/amlvaran/samples/' . $pid . '/' . $sid . '/images') && !is_dir('/var/amlvaran/samples/' . $pid . '/' . $sid . '/images')) {
            mkdir('/var/amlvaran/samples/' . $pid . '/' . $sid . '/images');
        }
        if (!file_exists('/var/amlvaran/samples/' . $pid . '/' . $sid . '/images/version' . $version) && !is_dir('/var/amlvaran/samples/' . $pid . '/' . $sid . '/images/version' . $version)) {
            mkdir('/var/amlvaran/samples/' . $pid . '/' . $sid . '/images/version' . $version);
        }
        file_put_contents('/var/amlvaran/samples/' . $pid . '/' . $sid . '/images/version' . $version . '/' . $row['MutationID'] . '.svg', $tempsvg);
        shell_exec('java -jar pdftest/batik-1.8/batik-rasterizer-1.8.jar -w 1000.0 /var/amlvaran/samples/' . $pid . '/' . $sid . '/images/version' . $version . '/' . $row['MutationID'] . '.svg');
        echo '<img src="/var/amlvaran/samples/' . $pid . '/' . $sid . '/images/version' . $version . '/' . $row['MutationID'] . '.png"></img>';
    } else {
        echo $tempsvg;
    }

    //echo '<div style="background-color:white; width:40%; height:50px; border:1px solid black; position:absolute; left:5%;"></div>';
    //echo '<div style="background-color:red; width:15%; height:50px; border:1px solid black; position:absolute; left:50%;"></div>';
?>
