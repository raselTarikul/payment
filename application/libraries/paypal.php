<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;

class Paypal {


    private $_mood,
            $_timeout,
            $_logEnabled,
            $_paymentMathod,
            $_shippingCost,
            $_tax,
            $_total,
            $_currency,
            $_returUrl,
            $_cancellUrl,
            $_api;

    public function config($set= array()) {
      
        $this->_returUrl = $set['returUrl'];
        $this->_cancellUrl = $set['cancellUrl'];
        $authtoken = new OAuthTokenCredential(
                $set['clientid'], $set['secrectid']
        );
        $this->_mood = $set['mod'];
        $this->_timeout = $set['timeout'];
        $this->_logEnabled = $set['log'];
        $this->_paymentMathod = $set['mathod'];
        $this->_shippingCost = $set['shippingcost'];
        $this->_currency = $set['currency'];
        
        
        $this->_api = new ApiContext($authtoken);
        $this->_api->setConfig([
            'mode' => $this->_mood,
            'http.ConnectionTimeOut' => $this->_timeout,
            'log.LogEnabled' => $this->_logEnabled
        ]);
    }

    public function pay($cost, $desctiption) {
        $payer = new Payer();
        $payer->setPaymentMethod($this->_paymentMathod);
        $details = new Details();
        $tax = ($this->_tax / 100) * $cost;
        $this->_total = $tax + $cost;
        $details->setTax("$tax")
                ->setShipping("$this->_shippingCost")
                ->setSubtotal("$this->_total");
        $amount = new Amount();
        $amount->setCurrency($this->_currency)
                ->setTotal("$this->_total")
                ->setDetails($details);
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                ->setDescription($desctiption);
        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction]);
        $redirectUrl = new RedirectUrls();
        $redirectUrl->setReturnUrl($this->_returUrl)
                ->setCancelUrl($this->_cancellUrl);
        $payment->setRedirectUrls($redirectUrl);
        try {
            $CI = get_instance();
            $payment->create($this->_api);
            $CI->session->set_userdata('paypal_token', $payment->getId());
        } catch (Exception $ex) {
            echo $ex;
        }
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $approvalUrl = $link->getHref();
                break;
            }
        }
 
      header('Location: ' . $approvalUrl);
    // echo "<a href='$approvalUrl' >$approvalUrl</a>";
    }

    public function execute($payerId, $paymentId) {
        $payment = Payment::get($paymentId, $this->_api);
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);
        $payment->execute($execution, $this->_api);
    }

}
