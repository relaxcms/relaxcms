<?php

class CException extends Exception
{
	/**
     * ���ڱ�����󼶱�
     * @var integer
     */
    protected $severity;

    /**
     * �����쳣���캯��
     * @param integer $severity ���󼶱�
     * @param string  $message  ������ϸ��Ϣ
     * @param string  $file     �����ļ�·��
     * @param integer $line     �����к�
     * @param array   $context  ���������ģ���������󴥷��������������б���������
     */
	public function __construct($severity, $message, $file, $line, array $context = array())
    {
        $this->severity = $severity;
        $this->message  = $message;
        $this->file     = $file;
        $this->line     = $line;
        $this->code     = 0;

        empty($context) || $this->setData('Error Context', $context);
    }
	
	public function CException($severity, $message, $file, $line, $context)
	{
		$this->__construct($severity, $message, $file, $line, $context);
	}
	
	public function errorMessage()
	{
		//error message
		$errorMsg = '<li>Error on line '.$this->getLine().' in '.$this->getFile()
			.': '.$this->getMessage().': ErrorCode='.$this->getCode().'</li>';

		$errorMsg .= '<li>CallTrace'.$this->getTraceAsString().'</li>';
		
		//Exception
		$tracedb = $this->getTrace();
				
		// Show Function
		foreach ($tracedb as $key=>$v) { 
		
			if ($v['function']){
				$errorMsg .= "<li>";
				
				$errorMsg .= sprintf(
						'at %s%s%s(%s)', 
						isset($v['class']) ? parse_class($v['class']) : '',
						isset($v['type'])  ? $v['type'] : '', 
						$v['function'], 
						isset($v['args'])?parse_args($v['args']):''
						);
						
				$errorMsg .= "</li>";
			}
			
			// Show line
			if (isset($v['file']) && isset($v['line'])) {
				$errorMsg .= "<li>";
				$errorMsg .= sprintf(' in %s', parse_file($v['file'], $v['line']));
				$errorMsg .= "</li>";
			}
		}
				
		return $errorMsg;
		
		//throw $this;
	}
}
?>