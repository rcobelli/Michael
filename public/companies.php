<?php

use Rybel\backbone\LogStream;
use Rybel\backbone\page;
use Rybel\backbone\site;

include_once("../init.php");

$config['type'] = LogStream::console;

$helper = new CompanyHelper($config);

$site = new site($config['pageTitles'], null, null);

// Site/page boilerplate
$site->addHeader("../includes/navbar.php");
init_site($site);

$page = new page(true);
$site->setPage($page);

// Start rendering the content
ob_start();
?>
    <div class="container">
        <h1>Companies</h1>
        <?php $helper->render_companies(); ?>
    </div>
<?php
// End rendering the content
$content = ob_get_clean();
$page->setContent($content);

$site->render();
?>