<?php

use Rybel\backbone\LogStream;
use Rybel\backbone\page;
use Rybel\backbone\site;

include_once("../init.php");

$config['type'] = LogStream::console;

if (empty($_GET['company_id'])) {
    header("Location: companies.php");
    die();
}

$helper = new CompanyHelper($config);
$company = $helper->getCompany($_GET['company_id']);

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
        <h1>Contacts at <?php echo $company['name']; ?></h1>
        <?php $helper->render_companyContacts($_GET['company_id']); ?>
    </div>
<?php
// End rendering the content
$content = ob_get_clean();
$page->setContent($content);

$site->render();
?>