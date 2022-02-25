<?php

use Rybel\backbone\LogStream;
use Rybel\backbone\page;
use Rybel\backbone\site;

include_once("../init.php");

$config['type'] = LogStream::console;

$site = new site($config['pageTitles'], null, null);

// Site/page boilerplate
$site->addHeader("../includes/navbar.php");
init_site($site);

$page = new page(true);
$site->setPage($page);

$items = array();

$contactHelper = new ContactHelper($config);
$newContacts = $contactHelper->getNewContacts($_SESSION['id']);
foreach ($newContacts as $newContact) {
    $name = explode(" ", $newContact['name']);
    $items[] = array(
        "text" => "Review your new contact, " . $newContact['name'],
        "href" => "contact.php?contact_id=" . $newContact['contact_id'],
        "sortKey" => end($name)
    );
}

$newContacts = $contactHelper->getContactsStatusInfo($_SESSION['id']);
foreach ($newContacts as $newContact) {
    if (abs($newContact['contact_gap'] - $newContact['greenDays']) < 7 ||
        abs($newContact['contact_gap'] - $newContact['yellowDays']) < 7 ||
        abs($newContact['contact_gap'] - $newContact['redDays']) < 7) {
        $name = explode(" ", $newContact['name']);
        $items[] = array(
            "text" => "Reach out to " . $newContact['name'] . " before they change status",
            "href" => "contact.php?contact_id=" . $newContact['contact_id'],
            "sortKey" => end($name)
        );
    }
}

$newContacts = $contactHelper->getStaleLinkedinContacts($_SESSION['id']);
foreach ($newContacts as $newContact) {
    $name = explode(" ", $newContact['name']);
    $items[] = array(
        "text" => "Review " . $newContact['name'] . "'s LinkedIn for job upates",
        "href" => "contact.php?contact_id=" . $newContact['contact_id'],
        "sortKey" => end($name)
    );
}

$actionHelper = new ActionHelper($config);
$actions = $actionHelper->getUserActions($_SESSION['id']);
foreach ($actions as $action) {
    $name = explode(" ", $action['name']);
    $items[] = array(
        "text" => "[" . date("m/d/Y", strtotime($action['date'])) . "] " . $action['title'] . " with " . $action['name'],
        "href" => "contact.php?contact_id=" . $action['contact_id'],
        "sortKey" => end($name)
    );
}

usort($items, function ($item1, $item2) {
    return $item1['sortKey'] <=> $item2['sortKey'];
});

// Start rendering the content
ob_start();
?>
    <div class="container">
        <h1>Upcoming Actions</h1>
        <ul>
            <li>TODO: Review any contacts that are within 7 days of changing status (either side)</li>
            <?php
            foreach ($items as $item) {
                echo "<li><a href='" . $item['href'] . "'>" . $item['text'] . "</a></li>";
            }
            ?>
        </ul>
    </div>
<?php
// End rendering the content
$content = ob_get_clean();
$page->setContent($content);

$site->render();
?>