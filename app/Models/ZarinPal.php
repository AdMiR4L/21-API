<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use SoapClient;

class ZarinPal extends Model
{
    private $MerchantID;
    private $Amount;
    private $Description;
    private $CallbackURL;

    public function __construct($amount, $orderId = null){

        $this->MerchantID = 'be70dcee-d152-4327-9070-749163eb3547'; //Required
        $this->Amount = $amount; //Amount will be based on Toman - Required
        $this->Description = 'خرید بلیط شرکت در مسابقه'; // Required
        $this->CallbackURL = 'https://api.21sport.club/api/game/payment/verify/'.$orderId; // Required
        //$this->CallbackURL = 'http://localhost/public/api/game/payment/verify/'.$orderId; // Required



    }

    public function doPayment(){
        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        //$client = new SoapClient('https://sandbox.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

        $result = $client->PaymentRequest(
            [
                'MerchantID' => $this->MerchantID,
                'Amount' => $this->Amount,
                'Description' => $this->Description,
                'CallbackURL' => $this->CallbackURL
            ]
        );
        return $result;
    }

    public function verifyPayment($authority, $status)
    {
        if ($status == 'OK') {

            $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
            //$client = new SoapClient('https://sandbox.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

            $result = $client->PaymentVerification(
                [
                    'MerchantID' => $this->MerchantID,
                    'Authority' => $authority,
                    'Amount' => $this->Amount,
                ]
            );
            return $result;
        }else{
            return false;
        }
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}

