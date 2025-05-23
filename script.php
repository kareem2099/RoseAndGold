<?php
// PHP logic file

function getDynamicMessage() {
    $hour = date("H");
    $greeting = "";
    if ($hour < "12") {
        $greeting = "Good Morning! Welcome to our Rose and Gold site.";
    } elseif ($hour < "18") {
        $greeting = "Good Afternoon! Enjoy the Rose and Gold theme.";
    } else {
        $greeting = "Good Evening! Relax with our Rose and Gold palette.";
    }
    return $greeting;
}

function getProjectInfo() {
    return "This project demonstrates HTML, CSS, and PHP working together with a Rose and Gold theme.";
}

?>
