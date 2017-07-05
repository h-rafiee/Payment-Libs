<?php
/**
*	Melat Payment Class
*/
class Melat {
	public $userName = "";
	public $userPassword = "";
	public $terminalId = "";
	public $wsdl = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';

	public function __construct($user_name,$user_password,$terminal_id) {
		$this->userName = $user_name;
		$this->userPassword = $user_password;
		$this->terminalId = $terminal_id;
	}

	public function bpPayRequest($amount,$orderId,$callBackUrl){
		try{

			$soap = new SoapClient($this->wsdl);
			$localDate = date("Ymd");
			$localTime = date("His");
			$additionalData = "";
			$parameters = array(
				'terminalId'     => $this->terminalId,
				'userName'       => $this->userName,
				'userPassword'   => $this->userPassword,
				'orderId'        => $orderId,
				'amount'         => $amount,
				'localDate'      => $localDate,
				'localTime'      => $localTime,
				'additionalData' => $additionalData,
				'callBackUrl'    => $callBackUrl,
				'payerId'        => 0
			);
			$response = $soap->bpPayRequest($parameters);
			list($ResCode,$RefId) = explode(",", $response);
			if($ResCode == 0){
				$data['status'] = 'done';
				$data['ResCode'] = $ResCode;
				$data['message'] = $this->getResCodeMessage($ResCode);
				$data['RefId'] = $RefId;
				return (object) $data;
			}else{
				throw new Exception($this->getResCodeMessage($ResCode));
			}

		}catch(Exeption $e){
			$data['status']='fail';
			$data['message']=$e->getMessage();
			return (object) $data;
		}
	}

	public function bpVerifyRequest($orderId,$saleOrderId,$saleRefrenceId){
		try{
			$soap = new SoapClient($this->wsdl);
			$parameters = array(
				'terminalId'     => $this->terminalId,
				'userName'       => $this->userName,
				'userPassword'   => $this->userPassword,
				'orderId'        => $orderId,
				'saleOrderId'    => $saleOrderId,
				'saleRefrenceId' => $saleRefrenceId
			);
			$response = $soap->bpVerifyRequest($parameters);
			list($ResCode,$RefId) = explode(",", $response);
			if($ResCode == 0){
				$data['status'] = 'done';
				$data['ResCode'] = $ResCode;
				$data['message'] = $this->getResCodeMessage($ResCode);
				return (object) $data;
			}else{
				throw new Exception($this->getResCodeMessage($ResCode));
			}
		}catch(Exeption $e){
			$data['status']='fail';
			$data['message']=$e->getMessage();
			return (object) $data;
		}
	}

	public function sendToBank($RefId){
			return '<!DOCTYPE html>
			<html>
			<head>
			
			</head>
			<body>
			<script language="javascript" type="text/javascript"> 
				function postRefId (refIdValue) {
				var form = document.createElement("form");
				form.setAttribute("method", "POST");
				form.setAttribute("action", "https://bpm.shaparak.ir/pgwchannel/startpay.mellat");         
				form.setAttribute("target", "_self");
				var hiddenField = document.createElement("input");              
				hiddenField.setAttribute("name", "RefId");
				hiddenField.setAttribute("value", refIdValue);
				form.appendChild(hiddenField);
	
				document.body.appendChild(form);         
				form.submit();
				document.body.removeChild(form);
			}
			postRefId("' . $RefId . '");
			</script>
			</body>
			</html>
			';
	}

	public function getResCodeMessage($ResCode){
		$messages = [
			0  => 'تراکنش با موفقیت انجام شد',
			11 => 'شماره كارت نامعتبر است',
			12 => 'موجودي كافي نيست',
			13 => 'رمز نادرست است',
			14 => 'تعداد دفعات وارد كردن رمز بيش از حد مجاز است',
			15 => 'كارت نامعتبر است',
			16 => 'دفعات برداشت وجه بيش از حد مجاز است',
			17 => 'كاربر از انجام تراكنش منصرف شده است',
			18 => 'تاريخ انقضاي كارت گذشته است', 
			19 => 'مبلغ برداشت وجه بيش از حد مجاز است',
			111 => 'صادر كننده كارت نامعتبر است',
			112 => 'خطاي سوييچ صادر كننده كارت',
			113 => 'پاسخي از صادر كننده كارت دريافت نشد',
			114 => 'دارنده كارت مجاز به انجام اين تراكنش نيست', 
			21 => 'پذيرنده نامعتبر است',
			23 => 'خطاي امنيتي رخ داده است',
			24 => 'اطلاعات كاربري پذيرنده نامعتبر است',
			25 => 'مبلغ نامعتبر است',
			31 => 'پاسخ نامعتبر است',
			32 => 'فرمت اطلاعات وارد شده صحيح نمي باشد',
			33 => 'حساب نامعتبر است',
			34 => 'خطاي سيستمي',
			35 => 'تاريخ نامعتبر است',
			41 => 'شماره درخواست تكراري است',
			42 => 'تراكنش  Saleيافت نشد',
			43 => 'قبلا درخواست  Verify داده شده است',
			44 => 'درخواست  Verfiy يافت نشد',
			45 => 'تراكنش  Settle شده است',
			46 => 'تراكنش  Settle نشده است',
			47 => 'تراكنش  Settle يافت نشد',
			48 => 'تراكنش  Reverse شده است',
			49 => 'تراكنش  Refund يافت نشد',
			412 => 'شناسه قبض نادرست است',
			413 => 'شناسه پرداخت نادرست است',
			414 => 'سازمان صادر كننده قبض نامعتبر است',
			415 => 'زمان جلسه كاري به پايان رسيده است',
			416 => 'خطا در ثبت اطلاعات',
			417 => 'شناسه پرداخت كننده نامعتبر است',
			418 => 'اشكال در تعريف اطلاعات مشتري',
			419 => 'تعداد دفعات ورود اطلاعات از حد مجاز گذشته است',
			421 => 'IP نامعتبر است',
			51 => 'تراكنش تكراري است',
			54 => 'تراكنش مرجع موجود نيست',
			55 => 'تراكنش نامعتبر است',
			61 => 'خطا در واريز',
		];

		return $messages[(int) $ResCode];
	}
}
