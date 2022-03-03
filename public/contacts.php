<?php

use Rybel\backbone\LogStream;
use Rybel\backbone\page;
use Rybel\backbone\site;

include_once("../init.php");

$config['type'] = LogStream::console;

$helper = new ContactHelper($config);

$site = new site($config['pageTitles'], null, null);

// Site/page boilerplate
$site->addHeader("../includes/navbar.php");
init_site($site);

$page = new page(true);
$site->setPage($page);

if ($_GET['sync'] == 'true') {
    $helper->syncWithGoogle();
    header("Location: ?");
    die();
}

// Start rendering the content
ob_start();
?>
    <div class="container">
        <a href="?sync=true"><button type="button" class="btn btn-primary float-right">Sync with Google</button></a>
        <h1>Contacts</h1>
        <?php $helper->render_mainTable(); ?>
        <script>
            $(document).ready(function () {
                $('#dtBasicExample').DataTable({"pageLength": 25});
                $('.dataTables_length').addClass('bs-select');
            });
        </script>
        <br/>
    </div>
<?php
// End rendering the content
$content = ob_get_clean();
$page->setContent($content);

$site->render();
?>