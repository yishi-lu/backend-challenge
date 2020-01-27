<?php
    namespace App\Api;
    use App\Service\TokenService;
    use App\Service\EtfsService;

    require_once  "../service/TokenService.php";
    require_once  "../service/EtfsService.php";

    header("Access-Control-Allow-Origin: http://69.203.92.72:9090");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $tokenService = new TokenService();

    $result = $tokenService->validateToken();

    //token validation fails
    if(!$result['result']) {
        http_response_code(401);
        echo json_encode($result);
        return;
    }

    $etfService = new EtfsService();

    $result = $etfService->getAllEtfs();

    http_response_code(200);

    echo json_encode(
        array(
            "success" => $result,
        )
    );

?>
