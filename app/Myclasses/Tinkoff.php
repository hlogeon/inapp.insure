<?php

namespace App\Myclasses;

//use PDO;

class Tinkoff
{
    const API_URL    = 'https://securepay.tinkoff.ru/v2/';
    const INIT_URL   = self::API_URL . 'Init/';
    const CHARGE_URL = self::API_URL . 'Charge/';
    const CANSEL_URL = self::API_URL . 'Cancel/';
    const ADD_CUSTOMER = self::API_URL . 'AddCustomer/';
    const GET_CUSTOMER = self::API_URL . 'GetCustomer/';
    const GET_CARDLIST = self::API_URL . 'GetCardList/';
    const REMOVE_CARD  = self::API_URL . 'RemoveCard/';
    const DEBUG      = 0;

    /* database config */
    /**
     * @var array
     */
    protected $_db_params = array(
        'db_name' => '',
        'db_host' => '',
        'db_user' => '',
        'db_pass' => '',
    );

    /**
     * @var array
     */
    protected $_params = array(
        'TerminalKey' => '',
        'Password'    => '',
    );
    /**
     * @var array
     */
    protected $_order = array();

    /**
     * @var string
     */
    protected $_last_result = '';

    protected $errors = [];

    /**
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->params = $params;
    }

    /**
     * @param  $param
     * @return mixed
     */
    private function __getParam($param = 'Password')
    {
        if (isset($this->params[$param]) && !empty($this->params[$param])) {
            return $this->params[$param];
        }

        return false;
    }

    /**
     * @param $text
     * @param $code
     */
    public function show_error($text = '', $code = 404)
    {
        $this->errors[] = [
            "code"  => $code,
            "text"  => $text
        ];
        //echo '<!-- ERROR --> <div style="width:90%;margin:20px;font-size:18px;color:#000;background:#FCF0F0;border:2px #D46363 solid;padding:20px;"><h3>Error ' . $code . '</h3><pre>' . $text . '</pre></div> <!-- ERROR -->';
        //http_response_code($code);
        //die();
    }

    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public function GetRedirectURL()
    {
        if (!empty($this->_last_result)) {
            return $this->_last_result->PaymentURL;
        }

        return false;
    }

    /**
     * @param array $params
     */
    public function genToken($params = array())
    {
        if (isset($params['DATA'])) {
            unset($params['DATA']);
        }

        if (isset($params['Receipt'])) {
            unset($params['Receipt']);
        }

        if (isset($params['Items'])) {
            unset($params['Items']);
        }

        if (!empty($this->__getParam('Password'))) {
            $params['Password']    = $this->__getParam('Password');
            $params['TerminalKey'] = $this->__getParam('TerminalKey');
            ksort($params);
            $x = implode('', $params);
            //$this->pre($x);
            return hash('sha256', $x);
        }

        return false;
    }

    /**
     * @param $data
     */
    public function pre($data = '')
    {
        echo '<pre>' . print_r($data, true) . '</pre>';
    }

    /**
     * @return mixed
     */
    public function getDesc()
    {
        if (!isset($this->order['Description'])) {
            return '';
        }

        if (!empty($this->order['Description'])) {
            return $this->order['Description'];
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        if (!isset($this->order['DATA']['Email'])) {
            return '';
        }

        if (!empty($this->order['DATA']['Email'])) {
            return $this->order['DATA']['Email'];
        }

        return '';
    }

    /**
     * @param $id
     */
    public function setOrderId($id = 0)
    {
        $this->order['OrderId'] = $id;
    }

    /**
     * @param  array    $params
     * @param  $dopdesc //        If it will be automatic
     * @return mixed
     */
    public function Init($params = array(), $dopdesc = '')
    {
        if (empty($params)) {
            $params = $this->order;
        }

        //$params = array('TerminalKey' => $this->__getParam('TerminalKey')) + $params;
        //$params = array('Token' => $this->genToken($params)) + $params;

        $params['TerminalKey'] = $this->__getParam('TerminalKey');
        $params['Token']       = $this->genToken($params);

        if (isset($params['Receipt']) && !is_object($params['Receipt'])) {
            $params['Receipt'] = (object)$params['Receipt'];
        }

        if (isset($params['DATA']) && !is_object($params['DATA'])) {
            $params['DATA'] = (object)$params['DATA'];
        }

        if (!isset($params['Recurrent'])) {
            $params['Recurrent'] = 'N';
        }

        $desc = $this->getDesc();
        if (empty($desc)) {
            if (!empty($params['Description'])) {
                $this->order['Description'] = $params['Description'];
                $desc                       = $params['Description'];
            }
        }

        $desc = $dopdesc . ' ' . $desc;

        $email = $this->getEmail();
        if (empty($email)) {
            if (!empty($params['DATA']->Email)) {
                $this->SetOrderEmail($params['DATA']->Email);
                $email = $params['DATA']->Email;
            }
        }

        if ($this->__db_available()) {
            //$OrderId = $this->__InsertPayment('WAIT_API', 0, 0, $email, $desc, '', $this->genToken($params), $params['Recurrent'], $dopdesc);
            $this->setOrderId($OrderId);
            $params['OrderId'] = $OrderId;
        }

        if (
            isset($params['TerminalKey']) &&
            isset($params['Amount']) &&
            isset($params['OrderId']) &&
            isset($params['TerminalKey']) &&
            isset($params['DATA']) &&
            isset($params['Receipt'])
        ) {

            if (self::DEBUG > 0) {
                $this->debug($params, 'Init #' . $params['OrderId']);
            }

            if(isset($this->charge_flag))
                unset($params['Recurrent'],$params['CustomerKey']);

            //file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/payment_rec_data.log', date('[H:i:s] ') . "\r\nINIT_RESPONSE:::\r\n" . print_r($params, true) . "\r\n=====\r\n", FILE_APPEND);


            $response = $this->sender($params, self::INIT_URL);
            $response = json_decode($response);


            return $response;
        } else {
            $this->show_error('Please fill data: TerminalKey, Amount, OrderId, TerminalKey, DATA, Receipt.<br>Current params:<br>' . print_r($params, true), 415);
        }
        
        return false;
    }

    public function SetRecurrent()
    {
        $this->order['Recurrent'] = 'Y';
    }

    private function generateToken($params)
    {
        $token                = array();
        $token['TerminalKey'] = $this->__getParam('TerminalKey');
        $token['Password']    = $this->__getParam('Password');

        foreach ($params as $key => $value) {
            $token[$key] = $value;
        }

        ksort($token);

        if (self::DEBUG > 0) {
            $this->debug($token, 'CHARGE_TOKEN');
        }

        $x     = implode('', $token);
        $token = hash('sha256', $x);

        if (self::DEBUG > 0) {
            $this->debug($token, 'RESULT_CHARGE_TOKEN');
        }

        return $token;
    }

    public function AddCustomer($CustomerKey)
    {
        if( $CustomerKey ) {
            $response = $this->GetCustomer($CustomerKey);
            if( ! $response->Success ) {
                $token = [];
                $token['CustomerKey'] = $CustomerKey;
                $params = array(
                    'TerminalKey'    => $this->__getParam('TerminalKey'),
                    'CustomerKey'    => $CustomerKey,
                );

                if( isset($this->order['DATA']['Phone']) && $this->order['DATA']['Phone'] ) {
                    $token["Phone"] = "+" . preg_replace("/[\D]/", "", $this->order['DATA']['Phone']);
                    $params["Phone"] = "+" . preg_replace("/[\D]/", "", $this->order['DATA']['Phone']);
                }

                if( isset($this->order['DATA']['Email']) && $this->order['DATA']['Email'] ) {
                    $token["Email"] = $this->order['DATA']['Email'];
                    $params["Email"] = $this->order['DATA']['Email'];
                }
                
                $token = $this->generateToken($token);
                $params["Token"] = $token;
                $response = $this->sender($params, self::ADD_CUSTOMER);
                $response = json_decode($response);
            }

            return $response;
        }
        $this->show_error('Нужно ввести идентификатор покупателя в системе продавца', 415);
        return false;
    }

    public function GetCustomer($CustomerKey)
    {
        if($CustomerKey) {

            $this->order["CustomerKey"] = $CustomerKey;
            $data = $this->order;
            $token = [];
            $token['CustomerKey'] = $CustomerKey;
            $token = $this->generateToken($token);

            $params = array(
                'TerminalKey'    => $this->__getParam('TerminalKey'),
                'CustomerKey'    => $CustomerKey,
                'Token'          => $token,
            );

            $response = $this->sender($params, self::GET_CUSTOMER);
            $response = json_decode($response);
            return $response;
        }
        $this->show_error('Нужно ввести идентификатор покупателя в системе продавца', 415);
        return false;
    }

    /**
     * @param array $params
     */
    public function AddMainInfo($params = array())
    {
        $this->order = $params;
    }

    public function AddMainInfoParams($params = array())
    {
        foreach ($params as $key => $value) {
            $this->order[$key] = $value;
        }
    }

    /**
     * @param array $params
     */
    public function AddDATA($params = array())
    {
        $this->order['DATA'] = $params;
    }

    /**
     * @param array $params
     */
    public function AddReceipt($params = array())
    {
        $this->order['Receipt'] = $params;
    }

    /**
     * @param array $params
     */
    public function AddItem($params = array())
    {
        if (!is_array($params)) {
            return;
        }

        if (strlen($params['Name']) > 128) {
            $params['Name'] = substr($params['Name'], 0, 127);
        }

        if (!isset($params['Name']) && !isset($params['Price']) ||
            !isset($params['Quantity']) || !isset($params['Tax'])) {
            return;
        }

        if (!isset($this->order['Receipt']['Items'])) {
            $this->order['Receipt']['Items'] = array();
        }

        $params['Amount']                  = $params['Price'] * $params['Quantity'];
        $this->order['Receipt']['Items'][] = (object)$params;
        $this->CalcAmount();
    }

    /**
     * @return null
     */
    public function CalcAmount()
    {
        if (!isset($this->order['Receipt']['Items'])) {
            $this->order['Amount'] = 0;
            return;
        }

        $amount = 0;
        if (is_array($this->order['Receipt']['Items'])) {
            foreach ($this->order['Receipt']['Items'] as $k => $item) {
                if (isset($item->Amount)) {
                    $amount += $item->Amount; // * $item->Quantity;
                }
            }
        }

        if (!isset($this->order['Amount'])) {
            $this->order = array('Amount' => $amount) + $this->order;
        } else {
            $this->order['Amount'] = $amount;
        }
    }

    /**
     * @param  $mobile
     * @return null
     */
    public function SetOrderMobile($mobile = '')
    {
        if (empty($mobile)) {
            return;
        }

        if (!isset($this->order['Receipt'])) {
            $this->order['Receipt'] = array();
        }

        if (!isset($this->order['DATA'])) {
            $this->order['DATA'] = array();
        }

        $this->order['DATA']['Phone']    = $mobile;
        $this->order['Receipt']['Phone'] = $mobile;
    }

    /**
     * @param $email
     */
    public function SetOrderEmail($email = '')
    {
        if (empty($email)) {
            return;
        }

        if (!isset($this->order['Receipt'])) {
            $this->order['Receipt'] = array();
        }

        if (!isset($this->order['DATA'])) {
            $this->order['DATA'] = array();
        }

        $this->order['DATA']['Email']    = $email;
        $this->order['Receipt']['Email'] = $email;
    }

    /**
     * @param $index
     */
    public function DeleteItem($index = 0)
    {
        if (!isset($this->order['Receipt']['Items'])) {
            return;
        }

        if (isset($this->order['Receipt']['Items'][$index])) {
            unset($this->order['Receipt']['Items'][$index]);
            array_multisort($this->order['Receipt']['Items'], SORT_DESC);
            $this->CalcAmount();
        }
    }

    /**
     * @return array
     */
    public static function AvailableTaxation()
    {
        $av = array(
            'osn',
            'usn_income',
            'usn_income_outcome',
            'envd',
            'esn',
            'patent',
        );
        return (array)$av;
    }

    /**
     * @return array
     */
    public static function AvaliableTax()
    {
        $av = array(
            'none',
            'vat0',
            'vat10',
            'vat18',
            'vat110',
            'vat118',
        );
        return (array)$av;
    }

    /**
     * @param  $tax
     * @return bool
     */
    public function isTaxation($tax = '')
    {
        if (empty($tax)) {
            return false;
        }

        if (in_array($tax, $this->AvailableTaxation())) {
            return true;
        }

        return false;
    }

    /**
     * @param  $tax
     * @return bool
     */
    public function isTax($tax = '')
    {
        if (empty($tax)) {
            return false;
        }

        if (in_array($tax, $this->AvaliableTax())) {
            return true;
        }

        return false;
    }

    /**
     * @param $tax
     */
    public function SetTax($tax = '')
    {
        if (!$this->isTax($tax)) {
            return;
        }

        if (!isset($this->order['Receipt']['Items'])) {
            return;
        }

        if (!empty($this->order['Receipt']['Items']) && is_array($this->order['Receipt']['Items'])) {
            foreach ($this->order['Receipt']['Items'] as $k => $item) {
                if (is_object($item)) {
                    $item = (array)$item;
                }

                $item['Tax']                         = $tax;
                $this->order['Receipt']['Items'][$k] = (object)$item;
            }
        }
    }

    public function showorder()
    {
        $this->pre($this->order);
    }

    /**
     * @param $taxa
     */
    public function SetTaxation($tax = '')
    {
        if (!isset($this->order['Receipt'])) {
            return;
        }

        if (!$this->isTaxation($tax)) {
            return;
        }

        $this->order['Receipt']['Taxation'] = $tax;
    }

    /**
     * @return mixed
     */
    private function __getConnection()
    {
        
    }

    /**
     * @param $status
     * @param $paymentId
     * @param $Amount
     * @param $Email
     * @param $desc
     * @param $redirect
     */
    private function __InsertPayment($status = '', $paymentId = 0, $Amount = 0, $Email = '', $desc = '', $redirect = '', $token = '', $Recurrent = 'N', $dopdesc = '')
    {
        
    }

    /**
     * @param $OrderId
     * @param $paymentId
     * @param $Amount
     * @param $redirect
     */
    private function __updatePayment($OrderId = 0, $status = 'NEW', $paymentId = 0, $Amount = 0, $redirect = '')
    {
        
    }

    private function __db_available()
    {
        // if (!empty($this->_db_params) && is_array($this->_db_params) && !empty($this->_db_params['db_name'])) {
        //     return true;
        // }

        return false;
    }

    /**
     * @param  $OrderId
     * @param  $status
     * @param  $CardId
     * @param  $Pan
     * @param  $ExpDate
     * @return null
     */
    private function __updateResultPayment($OrderId = 0, $status = 'NEW', $CardId = 0, $Pan = '', $ExpDate = 0, $RebillId = 0)
    {
        

    }

    public function doRedirect()
    {
        if (!empty($this->GetRedirectURL())) {
            header("X-Redirect: Powered by neatek");
            header("Location: " . $this->GetRedirectURL());
            die();
        }

        die('Empty ->GetRedirectURL();');
    }

    /**
     * @param array $params
     */
    public function checkResultResponse($params = array())
    {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $prev_token = $params['Token'];

        $params['Success'] = (int)$params['Success'];
        if ($params['Success'] > 0) {
            $params['Success'] = (string) 'true';
        } else {
            $params['Success'] = (string) 'false';
        }

        unset($params['Token']);

        $params['Password']    = $this->__getParam('Password');
        $params['TerminalKey'] = $this->__getParam('TerminalKey');

        ksort($params);
        $x = implode('', $params);

        if (self::DEBUG > 0) {
            $this->debug($params, 'GENERATED_SHA');
            $this->debug('NeededSHA: ' . $prev_token . PHP_EOL . 'ResultSHA:  [' . $x . ']  ' . hash('sha256', $x));
        }

        if (strcmp(strtolower($prev_token), strtolower(hash('sha256', $x))) == 0) {
            if (self::DEBUG > 0) {
                $this->debug('Fully valid sha256.', 'SUCCESS_CHECK_SHA256');
            }

            return true;
        }
    }

    /**
     * @return mixed
     */
    public function getResultResponse()
    {
        $response = file_get_contents('php://input');
        if (!empty($response)) {
            $response = json_decode($response);

            if (self::DEBUG > 0) {
                $this->debug($response);
            }

            if ($this->checkResultResponse($response)) {
                if ($this->__db_available()) {
                    if (!isset($response->RebillId)) {
                        $this->__updateResultPayment($response->OrderId, $response->Status, $response->CardId, $response->Pan, $response->ExpDate, 0);
                    } else {
                        $this->__updateResultPayment($response->OrderId, $response->Status, $response->CardId, $response->Pan, $response->ExpDate, $response->RebillId);
                    }
                }
            }
        }

        echo "OK";
        die();
    }

    /**
     * @param array   $data
     * @param $name
     */
    public function debug($data = array(), $name = '')
    {
        if (self::DEBUG > 0) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/debug' . date('dmY') . '.log', date('[H:i:s] ') . $name . "\r\nRESULT:::\r\n" . print_r($data, true) . "\r\n=====\r\n", FILE_APPEND);
        }
    }

    public function ClearOrder()
    {
        $this->order = array();
    }


    //TerminalKey
    //PaymentId
    //RebillId
    //Token

    public function Cansel($paymentId, $amount = 0)
    {
        $this->ClearOrder();
        if( ! $paymentId ) {
            $this->show_error("Не введен ID платежа", 500);
            return false;
        }

        //$this->pre($result);
        $token                = array();
        $token['TerminalKey'] = $this->__getParam('TerminalKey');
        $token['Password']    = $this->__getParam('Password');
        $token['PaymentId']   = $paymentId;

        if($amount > 0)
            $token["Amount"] = $amount * 100;

        ksort($token);

        if (self::DEBUG > 0) {
            $this->debug($token, 'CHARGE_TOKEN');
        }

        $x     = implode('', $token);
        $token = hash('sha256', $x);

        if (self::DEBUG > 0) {
            $this->debug($token, 'RESULT_CHARGE_TOKEN');
        }

        $params = array(
            'TerminalKey'   => $this->__getParam('TerminalKey'),
            'PaymentId'     => $paymentId,
            'Token'         => $token,
        );

        if($amount > 0)
            $params["Amount"] = $amount;

        $response = $this->sender($params, self::CANSEL_URL);
        $response = json_decode($response);

        //$this->pre($response);
        return $response;
    }

    private function sender($params, $url)
    {
        if ($curl = curl_init()) {
            $params = json_encode($params);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
            ));
            $response = curl_exec($curl);
            //$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            //{"Success":false,"ErrorCode":"10","Message":"Метод Charge заблокирован для данного терминала."}
            return $response;
        }
        return false;
    }

    /**
     * @param $result
     */
    public function Charge()
    {
        
        //$this->pre($result);
        $data = $this->order;
        $token                = array();
        $token['TerminalKey'] = $this->__getParam('TerminalKey');
        $token['Password']    = $this->__getParam('Password');
        $token['PaymentId']   = $data["PaymentId"];
        $token['RebillId']    = $data['RebillId'];
        ksort($token);

        if (self::DEBUG > 0) {
            $this->debug($token, 'CHARGE_TOKEN');
        }

        $x     = implode('', $token);
        $token = hash('sha256', $x);

        if (self::DEBUG > 0) {
            $this->debug($token, 'RESULT_CHARGE_TOKEN');
        }

        $params = array(
            'TerminalKey' => $this->__getParam('TerminalKey'),
            'PaymentId'   => $data["PaymentId"],
            'RebillId'    => $data['RebillId'],
            'Token'       => $token,
        );
        //file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/payment_rec_data.log', date('[H:i:s] ') . "\r\nCHARGE_PARAMS:::\r\n" . print_r($params, true) . "\r\n=====\r\n", FILE_APPEND);

        $response = $this->sender($params, self::CHARGE_URL);
        $response = json_decode($response);
        return $response;
    }

    public function GetCardList()
    {
        //$this->pre($result);
        $data = $this->order;
        if( ! isset($data["CustomerKey"]) ) $this->show_error("Не введен ключ", 500);
        $token                = array();
        $token['TerminalKey'] = $this->__getParam('TerminalKey');
        $token['Password']    = $this->__getParam('Password');
        $token['CustomerKey'] = $data["CustomerKey"];
        ksort($token);

        if (self::DEBUG > 0) {
            $this->debug($token, 'CHARGE_TOKEN');
        }

        $x     = implode('', $token);
        $token = hash('sha256', $x);

        if (self::DEBUG > 0) {
            $this->debug($token, 'RESULT_CHARGE_TOKEN');
        }

        $params = array(
            'TerminalKey' => $this->__getParam('TerminalKey'),
            'CustomerKey' => $data["CustomerKey"],
            'Token'       => $token,
        );

        $response = $this->sender($params, self::GET_CARDLIST);
        $response = json_decode($response);
        return $response;
    }

    public function RemoveCard()
    {
        //$this->pre($result);
        $data = $this->order;
        if( ! isset($data["CardId"]) ) $this->show_error("Не введен ID карты", 500);
        $token                = array();
        $token['TerminalKey'] = $this->__getParam('TerminalKey');
        $token['Password']    = $this->__getParam('Password');
        $token['CustomerKey'] = $data["CustomerKey"];
        ksort($token);

        if (self::DEBUG > 0) {
            $this->debug($token, 'CHARGE_TOKEN');
        }

        $x     = implode('', $token);
        $token = hash('sha256', $x);

        if (self::DEBUG > 0) {
            $this->debug($token, 'RESULT_CHARGE_TOKEN');
        }

        $params = array(
            'TerminalKey'       => $this->__getParam('TerminalKey'),
            'CustomerKey'       => $data["CustomerKey"],
            'CardId'            => $data["CardId"],
            'Token'             => $token,
        );

        $response = $this->sender($params, self::GET_CARDLIST);
        $response = json_decode($response);
        return $response;
    }
}
