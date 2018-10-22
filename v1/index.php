<?php
    require "config.php";
    $html = "<!DOCTYPE html>
    <head>
        <meta charset=\"utf-8\">
        <title>Endpoints — ".$API_NAME."</title>
    </head>
    <body>
        <h2>Endpoints</h2>
        <hr>
        <ul>
            <li>
                <a href=\"cards/\">/cards</a> — All MTG cards
            </li>
            <li>
                <a href=\"sets/\">/sets</a> — All MTG sets
            </li>
        </ul>
        <hr>
        <span style=\"font-size: smaller\">
            <i>
                ".$API_NAME."/".$API_VERSION."
            </i>
        </span>
    </body>
</html>";
    die($html);
?>