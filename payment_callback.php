//only post is allowed
include("SermepaPaymentGatewayCallBack.php");

define('TPV_TPV_DEBUG', 1);
if (TPV_TPV_DEBUG) {
	$log = fopen("log.txt", "a");
        fwrite($log, "=====================================================\n");
}

if (!empty($_POST)) {

    // get from post
    $order_tpv= $_POST["Ds_Order"];
    $order = intval(substr($order_tpv,5,strlen($order_tpv)));
    
    //vars
    $total            = $_POST["Ds_Amount"];
    $code             = $_POST["Ds_MerchantCode"];
    $currency         = $_POST["Ds_Currency"];
    $response         = $_POST["Ds_Response"];
    $remote_signature = $_POST["Ds_Signature"];
    
    
    $spw = new SermepaPaymentGatewayCallBack();
    $isValid = $spw->isValidMessage($total, $order_tpv, $code, $currency, $response, $remote_signature);
    
    // Calcul del SHA1
    if ($isValid) {
        $total    = number_format($total / 100, 2);
        $order_tpv    = (int) intval($order_tpv);
        $order_id    = (int) intval($order);
        $response = intval($response);
        
        if ($spw->isValidResponse($response)) {
            //ALL OK
				fwrite($log, "Order:" . $order_id .  " Order TPV:" . $order_tpv . " Total:" . $total . " Responce: " . $response . " Status: Order hasn't correct stat (PP)\n");
        } else {
            //something wrong
            if (TPV_DEBUG) {
                 fwrite($log, "Order:" . $order_id .  " Order TPV:" . $order_tpv . " Total:" . $total . " Responce: " . $response . " Status: Fail\n");
            }
        }
        
    } else {
        if (TPV_DEBUG) {
            fwrite($log, "Order:" . $order_id . " Total:" . $total . " Responce: " . $response . " Status: Signature mistmach\n");
            fwrite($log, "\tPOST VALUES:\n");
            foreach ($_POST as $key => $value) {
                fwrite($log, "\tKey:" . $key . " Value:" . $value . "\n");
            }
        }
    }
} else {
    if (TPV_DEBUG) {
        fwrite($log, "No request post\n");
    }
    header("Status: 404 Not Found");
}
if (TPV_DEBUG) {
    fclose($log);
}
