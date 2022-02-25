<?php

use Rybel\backbone\site;

function init_site(site $site)
{
    $site->addHeader("../includes/header.php");
    $site->addFooter("../includes/footer.php");
}

function devEnv()
{
    return gethostname() == "Ryans-MBP";
}

function currentPage($name)
{
    echo $name;
    echo basename(__FILE__, '.php') == $name ? "active" : "";
}
