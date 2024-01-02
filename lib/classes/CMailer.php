<?php

require_once RPATH_SUPPORTS.DS."phpmailer".DS."class.phpmailer.php";
require_once RPATH_SUPPORTS.DS."phpmailer".DS."class.smtp.php";

class CMailer
{
		
	/** 
	 * Constructor
	 *
	 * @param void
	 */
	public function __construct()
	{
		
	}
	
	public function CMailer()
	{
		$this->__construct();
	}	
	
	
	public function send($params = array())
	{
		//rlog($params);
		
		$mail = new PHPMailer();
		
		$mail->IsSMTP();
		$mail->SmtpClose();
		$mail->Host = $params["smtp_server_host"];
		$mail->Port = $params["smtp_server_port"];
		if ($params["smtp_auth_type"] == 'plain'){
			$SSecure = '';
		}else{
			$SSecure = $params["smtp_auth_type"];
		}
		
		$mail->SMTPSecure = $SSecure;
		$mail->SMTPAuth = true;
		$mail->Username = $params["smtp_auth_account"];
		$mail->Password = $params["smtp_auth_passwd"];
		$mail->SMTPKeepAlive = true;
		$mail->CharSet = 'utf-8';
		//
		//$from_name = 'Master';
		//$from_address = 'master@relaxcms.com';
		$from_name = $params["smtp_auth_account"];//'master@qq.com';
		$from_address = $params["smtp_auth_account"];//'master@qq.com';
		
		//$mail->SetFrom($params["smtp_auth_account"], $params["smtp_auth_account"]);
		$mail->SetFrom($from_address, $from_name);
		$mail->IsHTML($params['is_html']);
		$mail->Subject = $params['subject'];
		$mail->ClearAddresses();
		$targetEmails = explode("\n",$params["smtp_target"]);
		foreach ($targetEmails as $key=>$v) {
			$m = trim($v);
			if (!$m)
				continue;
			$mail->AddAddress($m);
		}
				
		$html = $params['content']; 
		$mail->MsgHTML($html);
			
		//为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
		if (isset($params['attachments']) ) {
			$attachments = $params['attachments'];
			foreach ($attachments as $key => $v) {
				$mail->addAttachment($v['file'], $v['title']);
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "add attachment :".$v['file']);
			}			
		}
		$res = false;
		try {
			ob_start();
			$res = $mail->Send();
			$res2 = ob_get_contents();
			ob_end_clean();
			if (!$res) {				
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call mail->Send failed! res=$res, res2=$res2");
			}
			
		} catch (phpmailerException $e) {
			$res2 = $e->errorMessage(); //Pretty error messages from PHPMailer
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "phpmailerException! res2=$res2");
		} catch (Exception $e) {
			$res2 = $e->getMessage(); //Boring error messages from anything else!
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Exception! res2=$res2");
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);
		return $res;		
	}
}