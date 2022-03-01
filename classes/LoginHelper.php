<?php

use Rybel\backbone\Helper;

class LoginHelper extends Helper {

    /**
     * @return Google_Client
     * @throws \Google\Exception
     */
    private function getClient(): Google_Client {
        $client = new Google_Client();
        $client->setAuthConfig('../client_secret.json');
        $client->setAccessType("offline");
        $client->addScope(["profile", "email", "https://www.googleapis.com/auth/contacts.readonly"]);
        $client->setIncludeGrantedScopes(true);
        $client->setRedirectUri($this->config['baseAuthURL'] . 'index.php');
        return $client;
    }

    /**
     * @return Google_Client
     * @throws \Google\Exception
     */
    public function getValidatedClient(): Google_Client {
        $client = $this->getClient();
        try {

            $client->setAccessToken((array) json_decode($_SESSION['access_token']));
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    throw new Exception("Force re-login...");
                }
            }
        } catch (Exception|Error $e) {
            header("Location: index.php");
            die();
        }

        return $client;
    }

    /**
     * @return void
     * @throws \Google\Exception
     */
    function logout() {
        $this->getClient()->revokeToken();
        session_destroy();
        setcookie("michael", null, 1, '/');
    }

    /**
     * @param string $code Auth code from Google OAuth
     * @return bool If setting the cookies/session succeeded
     * @throws \Google\Exception
     */
    function handleReturnCode(string $code): bool {
        if (isset($_GET['error'])) {
            exit($_GET);
        }

        $client = $this->getClient();
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $accessToken = $client->fetchAccessTokenWithAuthCode($code);
                $client->setAccessToken($accessToken);
            }
        }
        $accessToken = json_encode($client->getAccessToken());

        $plus = new Google_Service_Oauth2($client);
        $person = $plus->userinfo->get();

        $_SESSION['name'] = $person['name'];
        $_SESSION['email'] = $person['email'];
        $_SESSION['id'] = $person['id'];
        $_SESSION['access_token'] = $accessToken;

        $client->setAccessToken($_SESSION['access_token']);
        setcookie('michael', json_encode(array('name' => $person['name'], 'email' => $person['email'], 'id' => $person['id'], 'access_token' => $accessToken)), time() + (86400 * 30), "/"); // 86400 = 1 day
        return true;
    }

    /**
     * @return string Return URL to pass to Google OAuth
     * @throws \Google\Exception
     */
    function generateReturnURL() {
        $client = $this->getClient();
        if (isset($_GET['email'])) {
            $client->setLoginHint(urldecode($_GET['email']));
        }
        return $client->createAuthUrl();
    }

    /**
     * @param string $val Raw value of the cookie
     * @return void
     */
    function parseCookie(string $val) {
        $data = json_decode($val);
        $_SESSION['name'] = $data->name;
        $_SESSION['email'] = $data->email;
        $_SESSION['id'] = $data->id;
        $_SESSION['access_token'] = $data->access_token;
    }
}
