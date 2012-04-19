<?php
/*
	Licensed to the Apache Software Foundation (ASF) under one
	or more contributor license agreements.  See the NOTICE file
	distributed with this work for additional information
	regarding copyright ownership.  The ASF licenses this file
	to you under the Apache License, Version 2.0 (the
	"License"); you may not use this file except in compliance
	with the License.  You may obtain a copy of the License at

		http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing,
	software distributed under the License is distributed on an
	"AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
	KIND, either express or implied.  See the License for the
	specific language governing permissions and limitations
	under the License.

	File:		SermepaPaymentGatewayCallBack.php (SERMEPA)
	Function:	Create a valid form and hash for SERMEPA Gateway
	Author:		Jordi Martín
	Date:		16/04/2012
	Version:	Alpha (not tested)
*/
include('SermepaPaymentGateway.php');
//only post is allowed
if (!empty($_POST)) {
    if (DEBUG) {
        $log = fopen("log.txt", "a");
        fwrite($log, "=====================================================\n");
    }
    // get from post
    $total            = $_POST["Ds_Amount"];
    $order            = $_POST["Ds_Order"];
    $code             = $_POST["Ds_MerchantCode"];
    $currency         = $_POST["Ds_Currency"];
    $response         = $_POST["Ds_Response"];
    $remote_signature = $_POST["Ds_Signature"];
    
    
    $pv      = new PassarelaVisa();
    $isValid = $pv->isValidMessage($total, $order, $code, $currency, $response, $remote_signature);
    
    // Calcul del SHA1
    if ($isValid) {
        $total    = number_format($total / 100, 2);
        $order    = (int) intval($order);
        $response = intval($response);
        
        if ($response < 101) {
            //ALL OK
            //put your code here
            
            if (DEBUG) {
                fwrite($log, "Order:" . $order . " Total:" . $total . " Responce: " . $response . " Status: OK\n");
            }
        } else {
            //something wrong
            if (DEBUG) {
                fwrite($log, "Order:" . $order . " Total:" . $total . " Responce: " . $response . " Status: Fail\n");
            }
        }
        
    } else {
        if (DEBUG) {
            fwrite($log, "Order:" . $order . " Total:" . $total . " Responce: " . $response . " Status: Signature mistmach\n");
            fwrite($log, "\tPOST VALUES:\n");
            foreach ($_POST as $key => $value) {
                fwrite($log, "\tKey:" . $key . " Value:" . $value . "\n");
            }
            fwrite($log, "\tLocal signature:" . $firma_local . "\n");
        }
    }
} else {
    if (DEBUG) {
        fwrite($log, "No request post\n");
    }
    header("Status: 404 Not Found");
}
if (DEBUG) {
    fclose($log);
}
?>
