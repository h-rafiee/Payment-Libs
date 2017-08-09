<?php
/**
 *  Zarinpal Payment Class
 *  Author : Hossein Rafiee [ h.rafiee91@gmail.com ]
 *  github : https://github.com/h-rafiee/Payment-Libs
 */
class Zarinpal {
    public $MerchantID = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
    public $wsdl = 'https://www.zarinpal.com/pg/services/WebGate/wsdl';

    public function __construct($merchant_id) {
        $this->MerchantID = $merchant_id;
    }

    /**
     * @param int $amount
     * @param string $callBackUrl
     * @param string $description
     * @param string $email
     * @param string $mobile
     * @return object
     */
    public function PaymentRequest($amount,$callBackUrl,$description='',$email='',$mobile=''){
        try{

            $soap = new SoapClient($this->wsdl,['encoding' => 'UTF-8']);
            $localDate = date("Ymd");
            $localTime = date("His");
            $additionalData = "";
            $parameters = array(
                'MerchantID' => $this->MerchantID,
                'Amount' => $amount,
                'Description' => $description,
                'Email' => $email,
                'Mobile' => $mobile,
                'CallbackURL' => $callBackUrl,
            );
            $response = $soap->PaymentRequest($parameters);
            if($response->Status == 100){
                $data['status'] = 'done';
                $data['Authority'] = $response->Authority;
                $data['message'] = $this->getResCodeMessage($response->Status);
                return (object) $data;
            }else{
                throw new ZarinpalException($this->getResCodeMessage($response->Status));
            }

        }catch(ZarinpalException $e){
            $data['status']='fail';
            $data['message']=$e->getMessage();
            return (object) $data;
        }
    }

    /**
     * @param string $status
     * @param string $authority
     * @param integer $amount
     * @return object
     */
    public function PaymentVerification($status,$authority,$amount){
        try{
            if($status != "OK"){
                throw new ZarinpalException("تراکنش توسط کاربر کنسل شد.");
            }
            $soap = new SoapClient($this->wsdl,['encoding' => 'UTF-8']);
            $parameters = array(
                'MerchantID' => $this->MerchantID,
                'Authority' => $authority,
                'Amount' => $amount,
            );
            $response = $soap->PaymentVerification($parameters);
            if($response->Status == 100){
                $data['status'] = 'done';
                $data['RefID'] = $response->RefID;
                $data['message'] = $this->getResCodeMessage($response->Status);
                return (object) $data;
            }else{
                throw new ZarinpalException($this->getResCodeMessage($response->Status));
            }
        }catch(ZarinpalException $e){
            $data['status']='fail';
            $data['message']=$e->getMessage();
            return (object) $data;
        }
    }

    public function sendToBank($Authority){
        return Header('Location: https://www.zarinpal.com/pg/StartPay/'.$Authority);
    }

    public function getResCodeMessage($ResCode){
        $messages = [
            -1 =>'اطلاعات ارسال شده ناقص است.',
            -2 => 'IP و يا مرچنت كد پذيرنده صحيح نيست.',
            -3 => 'با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد.',
            -4=> 'سطح تاييد پذيرنده پايين تر از سطح نقره اي است.',
            -11=>'درخواست مورد نظر يافت نشد.',
            -12=>'امكان ويرايش درخواست ميسر نمي باشد.',
            -21=>'هيچ نوع عمليات مالي براي اين تراكنش يافت نشد.',
            -22=>'تراكنش نا موفق ميباشد.',
            -33=>'رقم تراكنش با رقم پرداخت شده مطابقت ندارد.',
            -34=>'سقف تقسيم تراكنش از لحاظ تعداد يا رقم عبور نموده است.',
            -40=>'اجازه دسترسي به متد مربوطه وجود ندارد.',
            -41=>'اطلاعات ارسال شده مربوط به  AdditionalDataغيرمعتبر ميباشد.',
            -42=>'مدت زمان معتبر طول عمر شناسه پرداخت بايد بين  30دقيه تا  45روز مي باشد.',
            -54=>'درخواست مورد نظر آرشيو شده است.',
            100=>'عمليات با موفقيت انجام گرديده است.',
            101=>'عمليات پرداخت موفق بوده و قبلا  PaymentVerificationتراكنش انجام شده است.'
        ];

        return $messages[(int) $ResCode];
    }
}
class ZarinpalException extends Exception {}