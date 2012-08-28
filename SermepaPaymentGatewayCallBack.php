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
	Author:		Jordi Mart�n
	Date:		16/08/2012
	Version:	1.0
*/
class SermepaPaymentGatewayCallBack
{
	
	var $secret   	=  'bdad275550a260666df4';
	
	function isValidMessage($total,$order,$code,$currency,$response,$remote_signature ){
		$message     = $total . $order . $code . $currency . $response . $this->secret;
		
		$local_signature = sha1($message);
		return (strcasecmp($local_signature,$remote_signature)==0);
	}
	
	function isValidResponse($response){
		return ($response < 101);
	}
	 
}
?>
