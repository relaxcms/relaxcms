<?php

class CJson
{
	static protected $send_content_type_header = false;
	/**
	 * set Content-Type header to jsonrequest
	 * @deprecated Use a AppFramework JSONResponse instead
	 */
	public static function setContentTypeHeader($type='application/json') { //'text/plain'
		if (!self::$send_content_type_header) {
			// We send json data
			header( 'Content-Type: '.$type . '; charset=utf-8');
			self::$send_content_type_header = true;
		}
	}
	
	/**
	 * Send json error msg
	 * @deprecated Use a AppFramework JSONResponse instead
	 */
	public static function error($data = array()) {
		$data['status'] = 'error';
		self::encodedPrint($data);
	}

	/**
	 * Send json success msg
	 * @deprecated Use a AppFramework JSONResponse instead
	 */
	public static function success($data = array(), $setContentType=true) {
		$data['status'] = 'success';
		self::encodedPrint($data, $setContentType);
	}

	/**
	 * Convert OC_L10N_String to string, for use in json encodings
	 */
	protected static function to_string(&$value) {
		//$value = safeEncoding($value, PHP_CHARSET);
	}

	/**
	 * Encode and print $data in json format
	 * @deprecated Use a AppFramework JSONResponse instead
	 */
	public static function encodedPrint($data, $setContentType=true) 
	{
		if ($setContentType) {
			self::setContentTypeHeader();
		}
		echo self::encode($data);
		exit;
	}

	/**
	 * Encode JSON
	 * @deprecated Use a AppFramework JSONResponse instead
	 */
	public static function encode($data) {
		if (is_array($data)) {
			array_walk_recursive($data, array('CJson', 'to_string'));
		}
		//JSON_PRETTY_PRINT
		if (version_compare(PHP_VERSION,'5.4.0','ge')){ //5.4.0
			return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
		} else {
			return json_encode($data);//, JSON_HEX_TAG);
		}
	}
	
	public static function decode($data)
	{
		return json_decode($data, true);
	}
}
