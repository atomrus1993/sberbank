<?php
/*
Регистрация заказа
https://3dsec.sberbank.ru/payment/rest/register.do

Регистрация заказа с предавторизацией
https://3dsec.sberbank.ru/payment/rest/registerPreAuth.do

Запрос завершения оплаты заказа
https://3dsec.sberbank.ru/payment/rest/deposit.do

Запрос отмены оплаты заказа
https://3dsec.sberbank.ru/payment/rest/reverse.do

Запрос возврата средств оплаты заказа
https://3dsec.sberbank.ru/payment/rest/refund.do

Получение статуса заказа
https://3dsec.sberbank.ru/payment/rest/getOrderStatus.do

Получение статуса заказа
https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do

Запрос проверки вовлеченности карты в 3DS
https://3dsec.sberbank.ru/payment/rest/verifyEnrollment.do

Запрос проведения оплаты по связкам
https://3dsec.sberbank.ru/payment/rest/paymentOrderBinding.do

Запрос деактивации связки
https://3dsec.sberbank.ru/payment/rest/unBindCard.do

Запрос активации связки
https://3dsec.sberbank.ru/payment/rest/bindCard.do

Запрос изменения срока действия связки
https://3dsec.sberbank.ru/payment/rest/extendBinding.do

Запрос списка всех связок клиента
https://3dsec.sberbank.ru/payment/rest/getBindings.do

Запрос списка связок определённой банковской карты
https://3dsec.sberbank.ru/payment/rest/getBindingsByCardOrId.do


Запрос оплаты через SberPay
https://3dsec.sberbank.ru/payment/paymentSberPay.do
*/

class SberbankController
{
    private $user;
    private $password;
    private $passwordTest = "testPassword";
    private $test = false;

    /**
     * Регистрация заказа
     * @link 	https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register
     * @param  	integer 	$orderId   	Номер заказа в магазине
     * @param  	integer 	$valuePay  	Сумма в копейках
     * @param  	string 	$returnUrl 	Ссылка при успешной оплате
     * @return 	JSON 	order_id/formUrl
     */
    public function register($orderId, $valuePay, $returnUrl)
    {
        $query 	= Array(
            'amount'      => $valuePay,
            'orderNumber' => $orderId,
            'returnUrl'   => $returnUrl
        );

        return $this->getRequest(
            $this->getSberbankUrl('register'), 
            $this->httpBuildQuery($query)
        );
    }   
    /**
     * Регистрация заказа с предавторизацией
     * @link 	https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:registerpreauth
     * @param  	integer 	$orderId   	Номер заказа в магазине
     * @param  	integer 	$valuePay  	Сумма в копейках
     * @param  	string 	    $returnUrl 	Ссылка при успешной оплате
     * @return 	JSON 	order_id/formUrl
     */
    public function registerPreAuth($orderId, $valuePay, $returnUrl)
    {
        $query 	= Array(
            'amount'      => $valuePay,
            'orderNumber' => $orderId,
            'returnUrl'   => $returnUrl
        );

        return $this->getRequest(
            $this->getSberbankUrl('registerPreAuth'), 
            $this->httpBuildQuery($query)
        );
    } 
    /**
     * Получение статуса заказа
     * @link 	https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:getorderstatus	
     * @param  string $orderId 	Номер заказа в сбербанке
     * @return JSON          	Получаем данные
     */
    public function getOrderStatus($orderId)
    {
        $query 	= Array(
            'orderId' => $orderId
        );

        return $this->getRequest(
            $this->getSberbankUrl('getOrderStatus'), 
            $this->httpBuildQuery($query)
        );
    }
    /**
     * Получение статуса заказа
     * @link 	https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:getorderstatusextended
     * @param  	string $orderId     id транзакции в сбербанк
     * @param  	string $orderNumber Номер заказа в магазине
     * @return 	JSON              	Получаем данные
     */
    public function getOrderStatusExtended($orderId, $orderNumber)
    {
        $query 	= Array(
            'orderId'        => $orderId,
            'orderNumber'    => $orderNumber
        );

        return $this->getRequest(
            $this->getSberbankUrl('getOrderStatusExtended'), 
            $this->httpBuildQuery($query)
        );
    }

    public function getSberbankUrl($query)
    {
        $url = $this->test ? "3dsec" : "securepayments";

        return "https://" . $url . ".sberbank.ru/payment/rest/" . $query . ".do";
    }
    /**
     * Запрос
     * @param  string $url   Урл обращения
     * @param  string $query Данные
     * @return string        Ответ
     */
    public function getRequest($url, $query)
    {
        return json_decode(file_get_contents($url, false, $query));
    }
    /**
     * Формирование данных для отправки
     * @param  Array $array Массив с данными
     * @return string        Данные
     */
    public function httpBuildQuery($array)
    {
        $array['password'] = $this->password;
        $array['userName'] = $this->user;

        $result =  Array(
            'http' => Array(
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded",
                'content' => http_build_query($array),
                'timeout' => 60
            )
        );

    	return stream_context_create($result);
    }
    /**
     * Задаем Логин пользователя
     * @param string $user Логин
     */
    public function setUser($user)
    {
    	$this->user = $user;
    }
    /**
     * Задаем пароль пользователя
     * @param string $password Пароль
     */
    public function setPassword($password)
    {
    	$this->password = $password;
    }
    /**
     * Задаем пароль пользователя тест
     * @param string $password Пароль
     */
    public function setPasswordTest($password)
    {
        $this->passwordTest = $password;
    }    
    /**
     * Тестовый режим для отладки
     * @param  boolean $value 	true для теста
     * @return NULL         	Меняем с боевого режима на тестовый
     */
    public function testMode($value = false)
    {
    	if ($value === true){
            $this->test = true;
            $this->password = $this->passwordTest;
    	}
    }
    /**
     * Запись логов транзакций
     * @param  string $string Данные
     * @param  string $path   Название файла
     * @return NULL           Создание файла
     */
    public function log($string, $path = 'otherSberbank')
    {
        $file    = fopen($_SERVER['DOCUMENT_ROOT'].'/logs/'. $path .'.log', 'a+');
        fwrite($file, date("d-m-Y H:i:s") . PHP_EOL);
        fwrite($file, json_encode($_REQUEST, JSON_UNESCAPED_UNICODE) . PHP_EOL . "===" . PHP_EOL);
        fwrite($file, json_encode($string, JSON_UNESCAPED_UNICODE) . PHP_EOL . "\t\t\t\t=========" . PHP_EOL);
        fclose($file);
    }
}
