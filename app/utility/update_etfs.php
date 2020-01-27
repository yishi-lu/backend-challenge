<?php
    namespace App\Utility;
    use App\Service\TokenService;
    use App\Service\EtfsService;

    require_once  "../service/TokenService.php";
    require_once  "../service/EtfsService.php";

    header("Access-Control-Allow-Origin: http://69.203.92.72:9090");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    $etfService = new EtfsService();

    $etfService->updateEtfs(EFTURL);

?>