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
if (isset($_GET['delete_action'])) {
    if ($actionHelper->deleteAction($_GET['delete_action'])) {
        $success = true;
        header("Location: ?contact_id=" . $_GET['contact_id']);
        die();
    } else {
        $error = $actionHelper->getErrorMessage();
    }
} else if (isset($_GET['convert_action'])) {
    if ($actionHelper->convertAction($_GET['convert_action'])) {
        $success = true;
        header("Location: ?contact_id=" . $_GET['contact_id']);
        die();
    } else {
        $error = $actionHelper->getErrorMessage();
    }
} else if ($_POST['action'] == 'updateDetails') {
    if ($contactHelper->updateContact($_POST)) {
        $success = true;
        $contactData = $contactHelper->getContact($_SESSION['id'], $_GET['contact_id']);
    } else {
        $error = $contactHelper->getErrorMessage();
    }
} else if ($_POST['action'] == 'newAction') {
    if ($actionHelper->createAction($_POST)) {
        $success = true;
    } else {
        $error = $actionHelper->getErrorMessage();
    }
} else if ($_POST['action'] == 'newJob') {
    if ($companyHelper->createJob($_POST)) {
        $success = true;
    } else {
        $error = $companyHelper->getErrorMessage();
    }
}

$contactHelper->viewContact($_GET['contact_id']);

$site = new site($contactData['name'], $error, $success);

// Site/page boilerplate
$site->addHeader("../includes/navbar.php");
init_site($site);

$page = new page(true);
$site->setPage($page);

// Start rendering the content
ob_start();
?>
    <div class="container">
        <div class="float-right" style="text-align: right;">
            <h5 title="The level of recency in the relationship" class="tooltip-enabled">Days Left in Status</h5>
            <div class="btn-group" role="group" aria-label="Basic example">
                <?php
                $tierData = $contactHelper->getTierDates($contactData['tier_id']);
                switch ($contactHelper->getColorCode($contactData['contact_gap'], $contactData['tier_id'])) {
                    case -1:
                        echo '<button type="button" class="btn btn-dark" disabled>N/A</button>';
                        break;
                    case 1:
                        echo '<button type="button" class="btn btn-success">' . ($tierData['greenDays'] - $contactData['contact_gap']) . '</button>';
                    case 2:
                        echo '<button type="button" class="btn btn-warning">' . ($tierData['yellowDays'] - $contactData['contact_gap']) . '</button>';
                    case 3:
                        echo '<button type="button" class="btn btn-danger">' . ($tierData['redDays'] - $contactData['contact_gap']) . '</button>';
                }
                ?>
            </div>
        </div>
        <h1><?php echo $contactData['name']?></h1>
        <h4><?php echo $contactData['relation']; ?> contact (<?php echo $contactData['relation_detail']; ?>) - <?php echo $contactData['tier_name'];?></h4>
        <?php
        if (!is_null($contactData['last_contact'])) {
            echo '<h5 title="' . $contactData['last_contact_details'] . '">Last contacted on ' . date('m/d/Y', strtotime($contactData['last_contact'])) . '</h5>';
        }
        ?>
        <hr/>
        <button id="newJobButton" class="btn btn-success float-right ml-2" onclick="document.getElementById('newJobForm').style.display = 'block'; document.getElementById('newJobButton').style.display = 'none'">Add Job</button>
        <?php
        if (!empty($contactData['linkedin'])) {
            echo '<a class="btn btn-primary float-right" href="linkedinRedirect.php?contact_id=' . $_GET['contact_id'] . '" target="_blank">Open LinkedIn Profile</a>';
        }
        ?>
        <h2>Jobs</h2>
        <div id="newJobForm" style="display: none">
            <?php include_once("../includes/newJobForm.php"); ?>
        </div>
        <?php $companyHelper->render_contactJobs($_GET['contact_id']); ?>
        <hr/>
        <h3>Details</h3>
        <?php include_once("../includes/updateDetailsForm.php"); ?>
        <hr/>
        <button id="newActionButton" class="btn btn-success float-right ml-2" onclick="document.getElementById('newActionForm').style.display = 'block'; document.getElementById('newActionButton').style.display = 'none'">Add Upcoming Action</button>
        <h2>Actions</h2>
        <div id="newActionForm" style="display: none">
            <?php include_once("../includes/newActionForm.php"); ?>
        </div>
        <?php $actionHelper->render_contactActions($_GET['contact_id']); ?>
    </div>
<?php
// End rendering the content
$content = ob_get_clean();
$page->setContent($content);

$site->render();
?>