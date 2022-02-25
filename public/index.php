<?php

use Rybel\backbone\LogStream;
use Rybel\backbone\page;
use Rybel\backbone\site;

include_once("../init.php");

$config['type'] = LogStream::console;

$helper = new LoginHelper($config);

if ($_GET['logout'] == 'true') {
    session_destroy();
    setcookie("michael", null, 1, '/');
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
                <img src="../assets/icon.png" height="100px" style="float: left;" class="mr-5" alt="">
                <h1 style="text-align: left;">Michael</h1>
                <h3 style="text-align: left;">Your personal CRM</h3>
            </div>
        </header>
        <hr/>
        <main role="main" class="inner">
            <h1 class="cover-heading">Easily Organize All Your <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">Markdown</a> <a href="https://en.wikipedia.org/wiki/Runbook" target="_blank">Runbooks</a> 📕</h1>
            <p class="lead">This utility allows you to easily create, revise and download copies of your runbooks. Get started by logging in with Google now!</p>
            <p class="lead">
                <a href="<?php echo $helper->generateReturnURL(); ?>"><button type="button" class="btn btn-danger">Login With Google</button></a>
            </p>
        </main>
        <hr />
        <main role="main" class="inner mt-5">
            <h3 class="cover-heading">Example</h3>
            <p class="lead">You can download any revision of your runbook as a PDF. Below is an example</p>
            <img src="../assets/example.png" height="1000px;" class="mb-3 border border-secondary" alt="">
        </main>
    </div>
<?php

// End rendering the content
$content = ob_get_clean();
$page->setContent($content);

$site->render();
?>
