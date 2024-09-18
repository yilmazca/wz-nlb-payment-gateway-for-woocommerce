<?php
// include the autoloader
require_once('init.php');

use PaymentGatewayJson\Client\Client;
use PaymentGatewayJson\Client\Data\Customer;
use PaymentGatewayJson\Client\Transaction\Debit;
use PaymentGatewayJson\Client\Transaction\Result;
use PaymentGatewayJson\Client\Schedule\ScheduleData;
use PaymentGatewayJson\Client\Data\ThreeDSecureData;

class WC_WZ_NLBGatewayDirectPayment extends WC_WZ_NLBGateway
{
    protected $token = null;
    protected $merchantTransactionId;

    protected $client;

    protected $customer;

    public $cbURL;
    public $scURL;
    public $erURL;
    public $ccURL;

    // Use gateway schadule or merchant send a sub sequent transaction in period.
    //true-use gateway schadule
    //false-merchant create own schadule
    public $gatewaySchadule = 'off';

    //Initial transaction with stored card(prewious or now)
    public $initialStoreTrans = 'No';

    //Sub-sequent transaction with stored card
    public $subSeqentTrans =  'No';

    // Schadule in case of Recurring transaction
    public $scheduleUnit = 'DAY';

    public $schedulePeriod = '1';
    public $scheduleDelay =  '';
    public $refTranId =  '';

    public function __construct($apiUsername, $apiPassword, $apiKey, $sharedSecret)
    {
        $this->merchantTransactionId = 'D-' . date('Y-m-d') . '-' . uniqid();
        $this->client = new Client($apiUsername, $apiPassword, $apiKey, $sharedSecret, 'tr');
    }

    public function customer($firstName, $lastName, $email, $BillingAddress, $BillingCity = null, $BillingPostcode = null, $BillingCountry = null)
    {
        $BillingCountry = ($BillingCountry == null ? 'ME' : $BillingCountry);
        $BillingPostcode = ($BillingPostcode == null ? '1000' : $BillingPostcode);
        $BillingCity = ($BillingCity == null ? 'Podgorica' : $BillingCity);

        $this->customer = new Customer();
        $this->customer
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setIpAddress($this->getRealIpAddr())
            ->setBillingAddress1($BillingAddress)
            ->setBillingCity($BillingCity)
            ->setBillingPostcode($BillingPostcode)
            ->setBillingCountry($BillingCountry);
    }

    public function debit($amount, $currency, $description)
    {
        $amount = ($amount == null ? '0.5' : $amount);
        $currency = ($currency == null ? 'EUR' : $currency);
        $description = ($description == null ? '' : $description);

        $debit = new Debit();
        $debit->setMerchantTransactionId($this->merchantTransactionId)
            ->setAmount($amount)
            ->setCurrency($currency)
            ->setCallbackUrl($this->cbURL . '&transactionId=' . $this->merchantTransactionId)
            ->setSuccessUrl($this->scURL . '&transactionId=' . $this->merchantTransactionId)
            ->setErrorUrl($this->erURL . '&transactionId=' . $this->merchantTransactionId)
            ->setCancelUrl($this->ccURL)
            //->setCancelUrl($ini_array['Domain']['myDomainContent'] . '/examples/paymentCancel.php?MID=' . $merchantTransactionId)
            ->setDescription($description)
            ->setMerchantMetaData("Transaction:Debit;Description:test")
            ->setCustomer($this->customer);



        // Add Extra data 
        if (isset($_POST["numInstalment"])) {
            $debit->addExtraData('userField1', $_POST["numInstalment"]);  //  If you have an agreement with your acquiring banks to offer payments in installments, 
            //userField1 is used and becomes mandatory. In such cases send 00 or 01 when no installments are selected. 
            //In case of an invalid value, the payment will be declined.
        }

        //alias for flik payment
        if (isset($_POST["flikAlias"])) {
            $debit->addExtraData('alias', $_POST["flikAlias"]);
        }

        //Add 3-D Secure elements
        $threeDSdata = new ThreeDSecureData();

        //if token acquired via payment.js   
        if (isset($token)) {
            $debit->setTransactionToken($token);
        }
        switch ($this->initialStoreTrans) {
            case "No":
                switch ($this->subSeqentTrans) {
                    case "No":  //normal debit
                        $debit->setWithRegister(false)
                            ->setTransactionIndicator('SINGLE');
                        break;
                    case "subSeqentCoF": //subsequent CoF - normal transaction with stored card
                        $debit->setReferenceUuid($this->refTranId)
                            ->setTransactionIndicator('CARDONFILE');
                        break;
                    case "subSeqentRec":    //subsequent Recurring - Note: If jou send schedule on initialization
                        //you don’t need to do that.
                        $debit->setReferenceUuid($refTranId)
                            ->setTransactionIndicator('RECURRING');
                        break;

                    case "subSeqentMIT": //subsequent MIT
                        if (strlen($this->refTranId) == 0) {
                            echo ("For Sub-sequent MIT you need enter ReferenceTransactionID of Initial MIT transaction!");
                            return;
                        }
                        $debit->setReferenceUuid($this->refTranId)
                            ->setTransactionIndicator('CARDONFILE-MERCHANT-INITIATED');
                        break;
                }
                break;

            case "initialCoF": // debit & store card for future use
                $threeDSdata->setAuthenticationIndicator('04'); //04-add card
                $debit->setWithRegister(true)
                    ->setTransactionIndicator('SINGLE')
                    ->setThreeDSecureData($threeDSdata);
                break;

            case "initialRec":
                if (floatval($amount) == 0) {
                    echo ("Initial recurring is not possible with amount:" . $amount);
                    die;
                }
                if (strlen($this->refTranId) > 0) { //Recurring establish with already stored card
                    $debit->setReferenceUuid($this->refTranId);
                }
                $threeDSdata->setAuthenticationIndicator('02') //02-recurring+MIT
                    ->setRecurringFrequency(2); //!1->Recurring; no connections with $schedulePeriod
                $debit->setWithRegister(true)
                    ->setTransactionIndicator('INITIAL')
                    ->setThreeDSecureData($threeDSdata);
                //if gateway do a sub-sequent transactions
                if ($this->gatewaySchadule == 'on') {
                    //create schedular
                    $myScheduleData = new ScheduleData();
                    $myScheduleData->setPeriodUnit($this->scheduleUnit) // The units are 'DAY','WEEK','MONTH','YEAR'  
                        ->setPeriodLength($this->schedulePeriod)
                        ->setAmount($amount)
                        ->setCurrency($currency);

                    //Delay for first sub sequent transaction
                    if (strlen($this->scheduleDelay) > 0) { //if dellay for first sub-sequent transaction is set
                        $date = new DateTime("now", new DateTimeZone('UTC'));
                        $date->modify($_POST["scheduleDelay"]);
                        $myScheduleData->setStartDateTime($date);
                    }

                    //add Schedular to debit transaction
                    $debit->setSchedule($myScheduleData);
                }
                break;

            case "initialMIT": //debit with MIT establishe
                if (floatval($amount) == 0) {
                    echo ("Initial MIT is not possible with amount:" . $amount);
                    die;
                }
                if (strlen($this->refTranId) > 0) { //MIT establish with already stored card
                    $debit->setReferenceUuid($this->refTranId);
                }
                $threeDSdata->setAuthenticationIndicator('02') //02-recurring+MIT
                    ->setRecurringFrequency(1); //1->MIT
                $debit->setWithRegister(true)
                    ->setTransactionIndicator('INITIAL')
                    ->setThreeDSecureData($threeDSdata);
                break;
        }

        // send the transaction
        $result = $this->client->debit($debit);
        return $result->getRedirectUrl();
    }

    public function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}