<?php

class CException extends Exception
{
	/**
     * 用于保存错误级别
     * @var integer
     */
    protected $severity;

    /**
     * 错误异常构造函数
     * @param integer $severity 错误级别
     * @param string  $message  错误详细信息
     * @param string  $file     出错文件路径
     * @param integer $line     出错行号
     * @param array   $context  错误上下文，会包含错误触发处作用域内所有变量的数组
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