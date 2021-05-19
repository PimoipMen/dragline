<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use Qcloud_cos\Auth;

class Welcome extends CI_Controller {

    private $server;

    public function __construct()
    {
        parent::__construct();
        $this->server();
    }

    public function index()
    {
        $this->load->view('welcome_message');
    }

    /**
     * 构造server
     */
    private function server()
    {
        $oauth2_tables_config = $this->config->item('oauth2_tables');
        $connection = ['dsn' => $this->db->dsn, 'username' => $this->db->username, 'password' => $this->db->password];
        OAuth2\Autoloader::register();
        $storage = new OAuth2\Storage\Pdo($connection, $oauth2_tables_config);

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $this->server = new OAuth2\Server($storage);

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
        $this->server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));
        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

        //print_r($this->server);
    }

    /**
     * 获取token
     * db grant_type: client_credentials
     */
    public function token()
    {
        $_POST['client_id'] = $_POST['app_id'];
        $_POST['client_secret'] = $_POST['app_secret'];
        $this->server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    }

    /**
     * 获取资源
     */
    public function resource()
    {
        // Handle a request to a resource and authenticate the access token
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
            die;
        }
        $token = $this->server->getAccessTokenData(OAuth2\Request::createFromGlobals());
        print_r($token);
        echo json_encode(array('success' => true, 'message' => 'You accessed my APIs!'));
    }

    /**
     * http://my-huitianxia.com/welcome/authorize?response_type=code&client_id=testclient&state=xyz
     * db grant_type: authorization_code
     */
    public function authorize()
    {
        $request = OAuth2\Request::createFromGlobals();
        $response = new OAuth2\Response();
        //print_r($this->server);
        // validate the authorize request
        if (!$this->server->validateAuthorizeRequest($request, $response)) {
            $response->send();
            die;
        }
        // display an authorization form
        if (empty($_POST)) {
            exit('
            <form method="post">
              <label>Do You Authorize TestClient?</label><br />
              <input type="submit" name="authorized" value="yes">
              <input type="submit" name="authorized" value="no">
            </form>');
        }

        // print the authorization code if the user has authorized your client
        $is_authorized = ($_POST['authorized'] === 'yes');
        $user_id = isset($_POST['uid']) ? (string) $_POST['uid'] : '';
        $this->server->handleAuthorizeRequest($request, $response, $is_authorized, $user_id);
        if ($is_authorized) {
            // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
            $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
            exit("SUCCESS! Authorization Code: $code");
        }
        $response->send();
    }

}
