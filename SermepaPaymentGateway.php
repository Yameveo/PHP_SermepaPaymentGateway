<?
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

	File:		SermepaPaymentGateway.php (SERMEPA)
	Function:	Create a valid form and hash for SERMEPA Gateway
	Author:		Jordi Martín
	Date:		16/04/2012
	Version:	Alpha (not tested)
*/
define('DEBUG', 0);

class SermepaPaymentGatewayException extends Exception { };

class SermepaPaymentGateway
{
    private $url_tpvv;
    private $secret;
    private $code;
    private $name;
    private $terminal;
    private $currency;
    private $transactionType;
    private $urlMerchant;
    private $consumerLanguage;
    
    
    function __construct()
    {
        //init vars
        if (DEBUG) {
            error_reporting(1);
        }
        $this->url_tpvv 	= 'https://sis-i.sermepa.es:25443/sis/realizarPago';
        $this->secret    	= 'qwertyasdf0123456789';
        $this->code     	= '999008881'; //FUD
        $this->name            	= 'TESTNAME';
        $this->terminal        	= '1';
        $this->currency        	= '978';
        $this->transactionType 	= '0';
	$this->consumerLanguage = '3';
        $this->urlMerchant     	= 'http://www.example.com/PassarelaValidation.php';
    }
    
    
    private function numberNormalizer($price)
    {
        $integers = "";
        if ($price == "") {
	    throw new NumberException("Empty price");
        } else {
	    $price = preg_replace('/[^0-9\.,]/', '', $price); //only allow numbers and "." or "," characters
            $price = str_replace(",", ".", $price);
            $pa     = explode(".", $price); //split the decimal part
            if (sizeof($pa) == 1) {
                return $price . "00";
            } else if (sizeof($pa) == 2) {
                $integers   = $pa[0];
                $decimals = $pa[1];
                if (strlen($decimals) > 2) {
		    throw new NumberException("Too much decimals (2 max.)");
                } 
		else if (strlen($decimals) == 2){
                    return $integers . $decimals;
		}
                else if (strlen($decimals) == 1){
                    return $integers . $decimals . "0";
		}
                else if (strlen($decimals) == 0){
                    return $integers . "00";
		}
                
            } else {
		throw new NumberException("Malformed number");
            }
            
        }
    }
    
    
    
    private function showDebugInfo($amount, $order)
    {
        $info = "";
        $info .= "<pre>";
	$info .= "<table>";
	$info .= "	<tr>";
	$info .= "		<td><h2>Info debug.</h2></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>URL: <font color=blue>$this->url_tpvv</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>Comercio: <font color=blue>$this->name</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>FUC: <font color=blue>$this->code</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>Terminal: <font color=blue>$this->terminal</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>Pedido: <font color=blue>$order</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>Importe: <font color=blue>$amount</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>Tipo de Operacion: <font color=blue>$this->transactionType (Autorización)</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>URL del comercio: <font color=blue>$this->urlMerchant</font></td>";
	$info .= "	</tr>";
	$info .= "	<tr>";
	$info .= "		<td>Moneda: <font color=blue>$this->currency Euros</font></td>";
	$info .= "	</tr>";
	$info .= "</table>";
	$info .= "</pre>";
        return $info;
    }
    
    
    public function getForm($amount, $order,$form_name='tpv_sermepa')
        $form = '';
        if (DEBUG) {
            $form .= $this->showDebugInfo($amount, $order);
        }
        $order = str_pad($order, 7, "0", STR_PAD_LEFT);
        $amount = $this->numberNormalizer($amount);
        $form .= '<form name="'.$form_name.'" action="' . $this->url_tpvv . '" method="post" target="tpv">';
        $form .= '<input type="hidden" name="Ds_Merchant_Currency" value="' . $this->currency . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantCode" value="' . $this->code . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantName" value="' . $this->name . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_ConsumerLanguage" value="' . $this->consumerLanguage . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_Terminal" value="' . $this->terminal . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_TransactionType" value="' . $this->transactionType . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_Amount" value="' . $amount . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_Order"  value="' . $order . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantURL" value="' . $this->urlMerchant . '">';
        $message   = $amount . $order . $this->code . $this->currency . $this->transactionType . $this->urlMerchant . $this->secret;
        $signature = sha1($message);
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantSignature" value="' . $signature . '">';
        $form .= '</form>';
        return $form;
    }


   public function isValidMessage($total,$order,$code,$currency,$response,$remote_signature ){
    	$message     = $total . $order . $code . $currency . $response . $secret;
    	$local_signature = sha1($message);
	return (strcmp($local_signature,$remote_signature)==0);
    }
    
    
}
?>
