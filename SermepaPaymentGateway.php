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
	Date:		16/08/2012
	Version:	1.0
*/
define('DEBUG_TPV', 0);
if (DEBUG_TPV) {
    error_reporting(E_ALL);
    ini_set('display_errors','On');
}

class SermepaPaymentGatewayException extends Exception {};

class SermepaPaymentGateway
{
    private $url_tpvv;
    private $secret;
    private $code;
    private $name;
    private $terminal;
    private $currency;
    private $urlMerchant;
    private $consumerLanguage;
    private $merchantUrlOK;
    private $merchantUrlKO;
    
    
    function __construct($config)
    {
        if (empty($config))
        { 
            throw new SermepaPaymentGatewayException('Provide a valid config array');
        }
        $this->config($config);
        $this->init();
    }
    
    
    private function config($config){
    
        //obligatory
        $this->url_tpvv     = $config['url_tpvv'];
        $this->secret    	=      $config['secret'];
        $this->code     	=        $config['code'];
        $this->name            	= $config['name'];
        $this->terminal        	= $config['terminal'];
        $this->currency        	= $config['currency'];
        $this->consumerLanguage =       $config['consumerLanguage'];
        $this->urlMerchant     	= $config['urlMerchant'];
        
        
        //optional
        $this->merchantUrlOK         = $config['merchantUrlOK'];
        $this->merchantUrlKO         = $config['merchantUrlKO'];

    }
    
    private function init() {
        
        if (!isset($this->url_tpvv))
        { 
            throw new SermepaPaymentGatewayException('TPV url is a mandatory param');
        }
        if (!isset($this->secret))
        { 
            throw new SermepaPaymentGatewayException('Secret is a mandatory param');
        } 
        if (!isset($this->code))
        { 
            throw new SermepaPaymentGatewayException('Code is a mandatory param');
        }
        if (!isset($this->name))
        { 
            throw new SermepaPaymentGatewayException('Name is a mandatory param');
        } 
        if (!isset($this->terminal))
        { 
            throw new SermepaPaymentGatewayException('Terminal is a mandatory param');
        } 
        if (!isset($this->currency))
        { 
            throw new SermepaPaymentGatewayException('Currency is a mandatory param');
        } 
        //if (!isset($this->transactionType))
        //{ 
        //    throw new SermepaPaymentGatewayException('TransactionType is a mandatory param');
        //}         
        if (!isset($this->consumerLanguage))
        { 
            throw new SermepaPaymentGatewayException('ConsumerLanguage is a mandatory param');
        } 
        
	//if (!isset($this->urlMerchant))
        //{ 
        //    throw new SermepaPaymentGatewayException('UrlMerchant is a mandatory param');
        //} 
        
    }
    
    private function numberNormalizer($price)
    {
        $integers = "";
        if ($price == "") {
	    throw new SermepaPaymentGatewayException("Empty price");
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
		    throw new SermepaPaymentGatewayException("Too much decimals (2 max.)");
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
		throw new SermepaPaymentGatewayException("Malformed number");
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
    
    
    public function getForm($amount, $order,$show_button=true,$form_name='tpv_sermepa'){
	$transactionType="0";
        $form = '';
        if (DEBUG_TPV) {
            $form .= $this->showDebugInfo($amount, $order);
        }
        //$order = str_pad($order, 7, "0", STR_PAD_LEFT);
        $amount = $this->numberNormalizer($amount);
        $form .= '<form name="'.$form_name.'" id="'.$form_name.'" action="' . $this->url_tpvv . '" method="post">';
        $form .= '<input type="hidden" name="Ds_Merchant_Currency" value="' . $this->currency . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantCode" value="' . $this->code . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantName" value="' . $this->name . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_ConsumerLanguage" value="' . $this->consumerLanguage . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_Terminal" value="' . $this->terminal . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_TransactionType" value="' . $transactionType . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_Amount" value="' . $amount . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_Order"  value="' . $order . '">';
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantUrl" value="' . $this->urlMerchant . '">';
        
        if (!empty($this->merchantUrlOK)){
            $form .= '<input type="hidden" name="Ds_Merchant_UrlOK" value="' . $this->merchantUrlOK . '">';
        }
        
        if (!empty($this->merchantUrlKO)){
            $form .= '<input type="hidden" name="Ds_Merchant_UrlKO" value="' . $this->merchantUrlKO . '">';
        }
                   
        
        $message   = $amount . $order . $this->code . $this->currency . $transactionType . $this->urlMerchant . $this->secret;
        $signature = sha1($message);
        $form .= '<input type="hidden" name="Ds_Merchant_MerchantSignature" value="' . $signature . '">';
        if ($show_button) {
            $form .= '<input type="submit" value="Comprar">';
        }
        $form .= '</form>';
        return $form;
    }


 public function refund($paymentId,$amount ){

  	$amount = $this->numberNormalizer($amount);
	$transactionType="3";
        $message   = $amount . $paymentId . $this->code . $this->currency . $transactionType . $this->secret;

        $signature = sha1($message);

	$fields = array(
		'Ds_Merchant_Currency' => urlencode($this->currency),
		'Ds_Merchant_MerchantCode' => urlencode($this->code),
		'Ds_Merchant_Terminal' => urlencode($this->terminal),
		'Ds_Merchant_TransactionType' => $transactionType,
		'Ds_Merchant_Amount' => urlencode($amount),
		'Ds_Merchant_Order' => urlencode($paymentId),
		'Ds_Merchant_MerchantSignature' => $signature,
		'Ds_Merchant_MerchantName' => $this->name,
		'Ds_Merchant_ConsumerLanguage' => urlencode($this->consumerLanguage)
	);



	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	
	echo "\n";
	echo $this->url_tpvv;
	echo "\n";
	echo $fields_string;
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $this->url_tpvv);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	//no, not is a joke :(
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

	//execute post
	$result = curl_exec($ch);

	//close connection
	curl_close($ch);

      
       
        return $result;
    }
    
    
}
?>
