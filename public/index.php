<?php

use Rybel\backbone\LogStream;
use Rybel\backbone\page;
use Rybel\backbone\site;

include_once("../init.php");

$config['type'] = LogStream::console;

$helper = new LoginHelper($config);

if ($_GET['logout'] == 'true') {
    $helper->logout();
    header("Location: ?");
    die();
} else if (isset($_GET['code'])) {
    if ($helper->handleReturnCode($_GET['code'])) {
        header("Location: dashboard.php");
        die();
    } else {
        $error = "Invalid OAuth code";
    }
} else if (isset($_COOKIE['michael'])) {
    $helper->parseCookie($_COOKIE['michael']);
    header("Location: dashboard.php");
    die();
}

// Site/page boilerplate
$site = new site($config['pageTitles'], $error ?? null);
init_site($site);

$page = new page(false);
$site->setPage($page);

// Start rendering the content
ob_start();

?>
    <div class="container d-flex h-100 p-3 mx-auto flex-column">
        <header class="mb-3">
            <div class="inner">
                <img src="assets/icon.png" height="100" style="float: left;" class="mr-5" alt="Icon">
                <h1 style="text-align: left;">Michael</h1>
                <h3 style="text-align: left;">Your personal CRM</h3>
            </div>
        </header>
        <hr/>
        <main role="main" class="inner">
            <h1 class="cover-heading">Easily Manage Your Networks with a Personal CRM</h1>
            <p class="lead">CRMs aren't just for massive companies to manage customers, you need to manage your network too. Use Michael to remind you to check in on people's LinkedIn pages for career updates, keep track of when you last interacted and proactively remind you when you should reach back out to keep the relationship going.</p>
            <p class="lead">
                <a href="<?php echo $helper->generateReturnURL(); ?>"><button type="button" class="btn btn-danger">Login With Google</button></a>
            </p>
        </main>
        <hr />
        <main role="main" class="inner mt-5">
            <h3 class="cover-heading">Screenshot</h3>
            <img src="assets/screenshot.png" height="500px;" class="mb-3 border border-secondary" alt="">
        </main>
    </div>
<?php

// End rendering the content
$content = ob_get_clean();
$page->setContent($content);

$site->render();
?>
