<?php

use Rybel\backbone\Helper;

class LoginHelper extends Helper {

    static private function getClient(): Google_Client {
        $client = new Google_Client();
        $client->setAuthConfig('../client_secret.json');
        $client->setAccessType("offline");
        $client->addScope(["profile", "email", "https://www.googleapis.com/auth/contacts.readonly"]);
        $client->setIncludeGrantedScopes(true);
        return $client;
    }

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
        echo "Code: " . $code . "</br>";
        if (isset($_GET['error'])) {
            exit($_GET);
        }

        $client = $this->getClient();
        if ($client->isAccessTokenExpired()) {
            echo "Expired access token</br>";
            if ($client->getRefreshToken()) {
                echo "Good refresh token</br>";
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                echo "Bad refresh token</br>";
                $accessToken = $client->fetchAccessTokenWithAuthCode($code);
                echo "Access token:</br>";
                var_dump($accessToken);
                $client->setAccessToken($accessToken);
            }
        } else {
            echo "Not expired</br>";
        }
        $_SESSION['access_token'] = $client->getAccessToken();

        $plus = new Google_Service_Oauth2($client);
        $person = $plus->userinfo->get();

        die();

        $_SESSION['name'] = $person['name'];
        $_SESSION['email'] = $person['email'];
        $_SESSION['id'] = $person['id'];

        if (isset($_SESSION['access_token'])) {
            $client->setAccessToken($_SESSION['access_token']);
            setcookie('michael', json_encode(array('name' => $person['name'], 'email' => $person['email'], 'id' => $person['id'], 'access_token' => $access_token['access_token'])), time() + (86400 * 30), "/"); // 86400 = 1 day
            return true;
        }
        return false;
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
        $client->setRedirectUri($this->config['baseAuthURL'] . 'index.php');
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
