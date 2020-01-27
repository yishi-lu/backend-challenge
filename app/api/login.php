<?php
    namespace App\Api;
    use App\Service\AuthService;

    require_once  "../service/AuthService.php";

    header("Access-Control-Allow-Origin: http://69.203.92.72:9090");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
    $service = new AuthService();

    $result = $service->login();

    if(array_key_exists('success', $result)) {

        $user = $result['success'];

        $jwt = $service->generateToken($user);

        setcookie('access_token', $jwt, time()+60*60*24*7, null, WEB_HOST, null, true);

        http_response_code(200);
        echo json_encode(
            array(
                "message" => "Successful login.",
            )
        );
    }
    else {
        http_response_code(401);
        echo json_encode($result);
    }
?>