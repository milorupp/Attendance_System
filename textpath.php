<?php

// PATH TO IMAGES AS VARIABLES (ACTUAL PATHS TO IMAGE FILES)
$pizzapath = "<img src='img/text/pizza.png' style='width:15px;height:15px;'>";
$uwajipath = "<img src='img/text/uwaji.png' style='width:20px;height:20px;'>";
$lennypath = "<img src='img/text/lenny.png' style='width:20px;height:20px;'>";
$bteapath = "<img src='img/text/btea.png' style='width:20px;height:20px;'>";
$brickspath = "<img src='img/text/12.png' style='width:20px;height:20px;'>";
$pinkpath = "<img src='img/text/pink.png' style='width:20px;height:20px;'>";
$yumyumpath = "<img src='img/text/yumyum.png' style='width:20px;height:20px;'>";
$sammypath = "<img src='img/text/sammy.png' style='width:20px;height:20px;'>";
$sbuxpath = "<img src='img/text/starbucks.png' style='width:20px;height:20px;'>";
$thumbsuppath = "<img src='img/text/thefinger.png' style='width:20px;height:20px;'>";
// THESE ARE THE WORDS YOU WANT TO DETECT
$keywords = array(
    "!smile",
    "!pizza",
    "!cp",
    "!uwaji",
    "!lenny",
    "!btea",
    "!12",
    "!pink",
    "!yumyum",
    "!sammy",
    "!*shakes head*",
    "!thefinger",
    ":smile:",
    ":frown:",
    ":happytears:",
    ":laughing:",
    ":haloface:",
    ":devilface:",
    ":wink:",
    ":licklips:",
    ":coolface:",
    ":smirking:",
    ":neutral:",
    ":unamused:",
    ":bus:",
    ":lightrail:",
    ":ship:",
    ":door:",
    ":nosmoking:",
    ":pizza:",
    ":angry:",
    ":scream:",
    ":sleepy:",
    ":dizzy:",
    ":barf:",
    ":kitty:",
    ":cookie:",
    ":soup:",
    ":thebest:",
    ":apple:",
    ":headphones:",
    ":heart:",
    ":snowboarding:",
    ":bowling:",
    ":football:",
    ":pumpkin:",
    ":icecream:",
    ":100:",
    ":camera:",
    ":banana:",
    ":leaf:",
    ":poop:",
    ":raised_hands:",
    ":ok_hand:");

// THESE ARE THE REPLACEMENT WORDS/IMAGE PATHS
$replacewords = array(
    ":) ",
    "$pizzapath",
    "&copy; ",
    "$uwajipath",
    "$lennypath",
    "$bteapath",
    "$brickspath",
    "$pinkpath",
    "$yumyumpath",
    "$sammypath",
    "$sbuxpath",
    "$thumbsuppath",
    "&#128512;",
    "&#128542;",
    "&#128514;",
    "&#128516;",
    "&#128519;",
    "&#128520;",
    "&#128521;",
    "&#128523;",
    "&#128526;",
    "&#128527;",
    "&#128528;",
    "&#128530;",
    "&#128652;",
    "&#128645;",
    "&#128674;",
    "&#128682;",
    "&#128685;",
    "&#127829;",
    "&#128544;",
    "&#128561;",
    "&#128564;",
    "&#128565;",
    "&#128567;",
    "&#128570;",
    "&#127850;",
    "&#127836;",
    "&#128055;",
    "&#127823;",
    "&#127911;",
    "&#128150;",
    "&#127938;",
    "&#127923;",
    "&#127944;",
    "&#127875;",
    "&#127846;",
    "&#128175;",
    "&#128247;",
    "&#127820;",
    "&#127809;",
    "&#128169;",
    "&#128588;",
    "&#128076;");
?>