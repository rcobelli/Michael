<?php

use Rybel\backbone\LogStream;
use Rybel\backbone\page;
use Rybel\backbone\site;

include_once("../init.php");

$config['type'] = LogStream::console;

$contactHelper = new ContactHelper($config);
$actionHelper = new ActionHelper($config);
$companyHelper = new CompanyHelper($config);

if (empty($_GET['contact_id'])) {
    header("Location: contacts.php");
    die();
}

$contactData = $contactHelper->getContact($_SESSION['id'], $_GET['contact_id']);
$contactHelper->updateLinkedinCheck($_GET['contact_id']);

header("Location: " . $contactData['linkedin']);

?>
