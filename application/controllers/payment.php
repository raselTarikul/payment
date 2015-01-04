<?php

class Payment extends CI_Controller{
   
    public function __construct() {
        parent::__construct();
        $this->load->library('paypal');
        $set = array(
            'returUrl'  => 'http://localhost/ci-paypal/index.php/payment/success',
            'cancellUrl' => 'http://localhost/ci-paypal/index.php/payment/cancel',
            'clientid' => 'ASy3OhByyPhJGC7GiUmGKMALYCbTNCnaYW2euEFxva0ShrR_XJaFv7Hip9Go',
            'secrectid' => 'EPXYwxA6whaTSkffJ1pdPq1i8feTfVtf80QDYnpox9_7hBd47cYbxt-OXaSz',
            'mod' => 'sandbox',
            'timeout' => 100,
            'log' => FALSE,
            'mathod' => 'paypal',
            'shippingcost' => 0.00,
            'currency' => 'USD',
            
            
        );
        $this->paypal->config($set);
    }
    
    public function index(){
        echo '<a href="'.base_url().'index.php/payment/pay">Pay Now</a>';
    }

    

    public function pay(){
        

        $this->paypal->pay(400, "Test Payment");
    }
    
    public function success(){
        $payerId = $this->input->get('PayerID');
        $paymentId = $this->input->get('paymentId');
        if($this->session->userdata('session_id') === $paymentId){
         $this->paypal->execute($payerId, $paymentId);
         echo 'Suddess';
        }else {
            $this->cancel();
        }
        
    }
    
    public function cancel(){
        echo 'Sorry we are unable to process your payment.';
    }
}