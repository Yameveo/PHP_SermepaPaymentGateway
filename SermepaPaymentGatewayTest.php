<?
include ('SermepaPaymentGateway.php');
$amount='25';
$order=mktime();


$config=array(
    'url_tpvv'     => 'https://sis-t.sermepa.es:25443/sis/realizarPago',
    'secret'    	=> 'qwertyasdf0123456789',
    'code'     	=> '999008881',
    'name'            	=> 'TESTNAME',
    'terminal'        	=> '4',
    'currency'        	=> '978',
    'transactionType' 	=> '0',
    'consumerLanguage' => '3',
    'urlMerchant'     	=> 'http://www.example.com/PassarelaValidation.php'
    );


$spw = new SermepaPaymentGateway($config);
?>

<html>
    <head>
        <title>Comercio Simulador</title>
        <script language=JavaScript>
            function calc() { 
            vent=window.open('','tpv','width=725,height=600,scrollbars=no,resizable=yes,status=yes,menubar=no,location=no');
            document.forms[0].submit();
            }
        </script>

</head>
<body>
<?

echo $spw->getForm($amount,$order);

?>

<center>
    <a href='javascript:calc()'> Pay in new Window </a>
</center>
    							  
</body>
</html>
