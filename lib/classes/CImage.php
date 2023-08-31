<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );



class CImage
{
	/** @var false|resource */
	protected $resource = false; // tmp resource.
	/** @var int */
	protected $imageType = IMAGETYPE_PNG; // Default to png if file type isn't evident.
	/** @var string */
	protected $mimeType = 'image/png'; // Default to png
	/** @var int */
	protected $bitDepth = 24;
	/** @var null|string */
	protected $filePath = null;
	/** @var finfo */
	private $fileInfo;
	/** @var \OCP\ILogger */
	private $logger;

	

	/**
	 * Constructor.
	 *
	 * @param resource|string $imageRef The path to a local file, a base64 encoded string or a resource created by
	 * an imagecreate* function.
	 * @param \OCP\ILogger $logger
	 */
	public function __construct($imageRef = null, $logger = null) 
	{
		if (!extension_loaded('gd') || !function_exists('gd_info')) {
			return false;
		}
		
	}

	/**
	 * Get mime type for an image file.
	 *
	 * @param string|null $filePath The path to a local image file.
	 * @return string The mime type if the it could be determined, otherwise an empty string.
	 */
	static public function getMimeTypeForFile($filePath) 
	{
		// exif_imagetype throws "read error!" if file is less than 12 byte
		if ($filePath !== null && filesize($filePath) > 11) {
			$imageType = exif_imagetype($filePath);
		} else {
			$imageType = false;
		}
		return $imageType ? image_type_to_mime_type($imageType) : '';
	}
	
	/**
	 * Determine whether the object contains an image resource.
	 *
	 * @return bool
	 */
	public function valid() 
	{ 
		return is_resource($this->resource);
	}

	/**
	 * Returns the MIME type of the image or an empty string if no image is loaded.
	 *
	 * @return string
	 */
	public function mimeType() 
	{
		return $this->valid() ? $this->mimeType : '';
	}

	/**
	 * Returns the width of the image or -1 if no image is loaded.
	 *
	 * @return int
	 */
	public function width() 
	{
		return $this->valid() ? imagesx($this->resource) : -1;
	}

	/**
	 * Returns the height of the image or -1 if no image is loaded.
	 *
	 * @return int
	 */
	public function height() 
	{
		return $this->valid() ? imagesy($this->resource) : -1;
	}

	/**
	 * Returns the width when the image orientation is top-left.
	 *
	 * @return int
	 */
	public function widthTopLeft() 
	{
		$o = $this->getOrientation();
		switch ($o) {
			case -1:
			case 1:
			case 2: // Not tested
			case 3:
			case 4: // Not tested
				return $this->width();
			case 5: // Not tested
			case 6:
			case 7: // Not tested
			case 8:
				return $this->height();
		}
		return $this->width();
	}

	/**
	 * Returns the height when the image orientation is top-left.
	 *
	 * @return int
	 */
	public function heightTopLeft() 
	{
		$o = $this->getOrientation();
		switch ($o) {
			case -1:
			case 1:
			case 2: // Not tested
			case 3:
			case 4: // Not tested
				return $this->height();
			case 5: // Not tested
			case 6:
			case 7: // Not tested
			case 8:
				return $this->width();
		}
		return $this->height();
	}

	/**
	 * Outputs the image.
	 *
	 * @param string $mimeType
	 * @return bool
	 */
	public function show($mimeType = null) 
	{
		if ($mimeType === null) {
			$mimeType = $this->mimeType();
		}
		header('Content-Type: ' . $mimeType);
		return $this->_output(null, $mimeType);
	}

	/**
	 * Saves the image.
	 *
	 * @param string $filePath
	 * @param string $mimeType
	 * @return bool
	 */
	public function save($filePath = null, $mimeType = null) 
	{
		if ($mimeType === null) {
			$mimeType = $this->mimeType();
		}
		if ($filePath === null && $this->filePath === null) {
			return false;
		} elseif ($filePath === null && $this->filePath !== null) {
			$filePath = $this->filePath;
		}
		return $this->_output($filePath, $mimeType);
	}

	/**
	 * Outputs/saves the image.
	 *
	 * @param string $filePath
	 * @param string $mimeType
	 * @return bool
	 * @throws Exception
	 */
	private function _output($filePath = null, $mimeType = null) 
	{
		if ($filePath) {
			if (!file_exists(dirname($filePath)))
				mkdir(dirname($filePath), 0777, true);
			if (!is_writable(dirname($filePath))) {
				return false;
			} elseif (is_writable(dirname($filePath)) && file_exists($filePath) && !is_writable($filePath)) {
				return false;
			}
		}
		if (!$this->valid()) {
			return false;
		}

		$imageType = $this->imageType;
		if ($mimeType !== null) {
			switch ($mimeType) {
				case 'image/gif':
					$imageType = IMAGETYPE_GIF;
					break;
				case 'image/jpeg':
					$imageType = IMAGETYPE_JPEG;
					break;
				case 'image/png':
					$imageType = IMAGETYPE_PNG;
					break;
				case 'image/x-xbitmap':
					$imageType = IMAGETYPE_XBM;
					break;
				case 'image/bmp':
				case 'image/x-ms-bmp':
					$imageType = IMAGETYPE_BMP;
					break;
				default:
					return false; 
			}
		}

		switch ($imageType) {
			case IMAGETYPE_GIF:
				$retVal = imagegif($this->resource, $filePath);
				break;
			case IMAGETYPE_JPEG:
				$retVal = imagejpeg($this->resource, $filePath);
				break;
			case IMAGETYPE_PNG:
				$retVal = imagepng($this->resource, $filePath);
				break;
			case IMAGETYPE_XBM:
				if (function_exists('imagexbm')) {
					$retVal = imagexbm($this->resource, $filePath);
				} else {
					return false;
				}

				break;
			case IMAGETYPE_WBMP:
				$retVal = imagewbmp($this->resource, $filePath);
				break;
			case IMAGETYPE_BMP:
				$retVal = imagebmp($this->resource, $filePath, $this->bitDepth);
				break;
			default:
				$retVal = imagepng($this->resource, $filePath);
		}
		return $retVal;
	}

	
	/**
	 * @return resource Returns the image resource in any.
	 */
	public function resource() 
	{
		return $this->resource;
	}

	/**
	 * @return null|string Returns the raw image data.
	 */
	public function data() 
	{
		if (!$this->valid()) {
			return null;
		}
		ob_start();
		switch ($this->mimeType) {
			case "image/png":
				$res = imagepng($this->resource);
				break;
			case "image/jpeg":
				$res = imagejpeg($this->resource);
				break;
			case "image/gif":
				$res = imagegif($this->resource);
				break;
			default:
				$res = imagepng($this->resource);
				rlog('Could not guess mime-type, defaulting to png');
				break;
		}
		if (!$res) {
			rlog('Error getting image data.');
		}
		return ob_get_clean();
	}

	/**
	 * @return string - base64 encoded, which is suitable for embedding in a VCard.
	 */
	function __toString() 
	{
		return base64_encode($this->data());
	}

	/**
	 * (I'm open for suggestions on better method name ;)
	 * Get the orientation based on EXIF data.
	 *
	 * @return int The orientation or -1 if no EXIF data is available.
	 */
	public function getOrientation() 
	{
		if ($this->imageType !== IMAGETYPE_JPEG) {
			rlog('OC_Image->fixOrientation() Image is not a JPEG.');
			return -1;
		}
		if (!is_callable('exif_read_data')) {
			rlog('Exif module not enabled.');
			return -1;
		}
		if (!$this->valid()) {
			rlog('No image loaded.');
			return -1;
		}
		if (is_null($this->filePath) || !is_readable($this->filePath)) {
			rlog('OC_Image->fixOrientation() No readable file path set.');
			return -1;
		}
		$exif = @exif_read_data($this->filePath, 'IFD0');
		if (!$exif) {
			return -1;
		}
		if (!isset($exif['Orientation'])) {
			return -1;
		}
		return $exif['Orientation'];
	}

	/**
	 * (I'm open for suggestions on better method name ;)
	 * Fixes orientation based on EXIF data.
	 *
	 * @return bool.
	 */
	public function fixOrientation() 
	{
		$o = $this->getOrientation();
		rlog('Orientation: ' . $o);
		$rotate = 0;
		$flip = false;
		switch ($o) {
			case -1:
				return false; //Nothing to fix
			case 1:
				$rotate = 0;
				break;
			case 2:
				$rotate = 0;
				$flip = true;
				break;
			case 3:
				$rotate = 180;
				break;
			case 4:
				$rotate = 180;
				$flip = true;
				break;
			case 5:
				$rotate = 90;
				$flip = true;
				break;
			case 6:
				$rotate = 270;
				break;
			case 7:
				$rotate = 270;
				$flip = true;
				break;
			case 8:
				$rotate = 90;
				break;
		}
		if($flip && function_exists('imageflip')) {
			imageflip($this->resource, IMG_FLIP_HORIZONTAL);
		}
		if ($rotate) {
			$res = imagerotate($this->resource, $rotate, 0);
			if ($res) {
				if (imagealphablending($res, true)) {
					if (imagesavealpha($res, true)) {
						imagedestroy($this->resource);
						$this->resource = $res;
						return true;
					} else {
						rlog('Error during alpha-saving');
						return false;
					}
				} else {
					rlog('Error during alpha-blending');
					return false;
				}
			} else {
				rlog('Error during orientation fixing');
				return false;
			}
		}
		return false;
	}

	/**
	 * Loads an image from a local file, a base64 encoded string or a resource created by an imagecreate* function.
	 *
	 * @param resource|string $imageRef The path to a local file, a base64 encoded string or a resource created by an imagecreate* function or a file resource (file handle    ).
	 * @return resource|false An image resource or false on error
	 */
	public function load($imageRef) 
	{
		if (is_resource($imageRef)) {
			if (get_resource_type($imageRef) == 'gd') {
				$this->resource = $imageRef;
				return $this->resource;
			} elseif (in_array(get_resource_type($imageRef), array('file', 'stream'))) {
				return $this->loadFromFileHandle($imageRef);
			}
		} elseif ($this->loadFromBase64($imageRef) !== false) {
			return $this->resource;
		} elseif ($this->loadFromFile($imageRef) !== false) {
			return $this->resource;
		} elseif ($this->loadFromData($imageRef) !== false) {
			return $this->resource;
		}
		rlog('could not load anything. Giving up!');
		return false;
	}

	/**
	 * Loads an image from an open file handle.
	 * It is the responsibility of the caller to position the pointer at the correct place and to close the handle again.
	 *
	 * @param resource $handle
	 * @return resource|false An image resource or false on error
	 */
	public function loadFromFileHandle($handle) 
	{
		$contents = stream_get_contents($handle);
		if ($this->loadFromData($contents)) {
			return $this->resource;
		}
		return false;
	}

	/**
	 * Loads an image from a local file.
	 *
	 * @param bool|string $imagePath The path to a local file.
	 * @return bool|resource An image resource or false on error
	 */
	public function loadFromFile($imagePath = false) 
	{
		// exif_imagetype throws "read error!" if file is less than 12 byte
		if (!@is_file($imagePath) || !file_exists($imagePath) || filesize($imagePath) < 12 || !is_readable($imagePath)) {
			return false;
		}
		$iType = exif_imagetype($imagePath);
		switch ($iType) {
			case IMAGETYPE_GIF:
				if (imagetypes() & IMG_GIF) {
					$this->resource = imagecreatefromgif($imagePath);
					// Preserve transparency
					imagealphablending($this->resource, true);
					imagesavealpha($this->resource, true);
				} else {
					rlog('GIF images not supported: ' . $imagePath);
				}
				break;
			case IMAGETYPE_JPEG:
				if (imagetypes() & IMG_JPG) {
					$this->resource = imagecreatefromjpeg($imagePath);
				} else {
					rlog('JPG images not supported: ' . $imagePath);
				}
				break;
			case IMAGETYPE_PNG:
				if (imagetypes() & IMG_PNG) {
					$this->resource = imagecreatefrompng($imagePath);
					// Preserve transparency
					imagealphablending($this->resource, true);
					imagesavealpha($this->resource, true);
				} else {
					rlog('PNG images not supported: ' . $imagePath);
				}
				break;
			case IMAGETYPE_XBM:
				if (imagetypes() & IMG_XPM) {
					$this->resource = imagecreatefromxbm($imagePath);
				} else {
					rlog('XBM/XPM images not supported: ' . $imagePath);
				}
				break;
			case IMAGETYPE_WBMP:
				if (imagetypes() & IMG_WBMP) {
					$this->resource = imagecreatefromwbmp($imagePath);
				} else {
					rlog('WBMP images not supported: ' . $imagePath);
				}
				break;
			case IMAGETYPE_BMP:
				$this->resource = $this->imagecreatefrombmp($imagePath);
				break;
			default:

				// this is mostly file created from encrypted file
				$this->resource = imagecreatefromstring(file_get_contents($imagePath));
				$iType = IMAGETYPE_PNG;
				//rlog('Default');
				break;
		}
		if ($this->valid()) {
			$this->imageType = $iType;
			$this->mimeType = image_type_to_mime_type($iType);
			$this->filePath = $imagePath;
		}
		return $this->resource;
	}

	/**
	 * Loads an image from a string of data.
	 *
	 * @param string $str A string of image data as read from a file.
	 * @return bool|resource An image resource or false on error
	 */
	public function loadFromData($str) 
	{
		if (is_resource($str)) {
			return false;
		}
		$this->resource = @imagecreatefromstring($str);
		if ($this->fileInfo) {
			$this->mimeType = $this->fileInfo->buffer($str);
		}
		if (is_resource($this->resource)) {
			imagealphablending($this->resource, false);
			imagesavealpha($this->resource, true);
		}

		if (!$this->resource) {
			rlog('OC_Image->loadFromFile, could not load');
			return false;
		}
		return $this->resource;
	}

	/**
	 * Loads an image from a base64 encoded string.
	 *
	 * @param string $str A string base64 encoded string of image data.
	 * @return bool|resource An image resource or false on error
	 */
	public function loadFromBase64($str) 
	{
		if (!is_string($str)) {
			return false;
		}
		$data = base64_decode($str);
		if ($data) { // try to load from string data
			$this->resource = @imagecreatefromstring($data);
			if ($this->fileInfo) {
				$this->mimeType = $this->fileInfo->buffer($data);
			}
			if (!$this->resource) {
				rlog('OC_Image->loadFromBase64, could not load');
				return false;
			}
			return $this->resource;
		} else {
			return false;
		}
	}

	/**
	 * Create a new image from file or URL
	 *
	 * @link http://www.programmierer-forum.de/function-imagecreatefrombmp-laeuft-mit-allen-bitraten-t143137.htm
	 * @version 1.00
	 * @param string $fileName <p>
	 * Path to the BMP image.
	 * </p>
	 * @return bool|resource an image resource identifier on success, <b>FALSE</b> on errors.
	 */
	private function imagecreatefrombmp($fileName) 
	{
		if (!($fh = fopen($fileName, 'rb'))) {
			rlog('imagecreatefrombmp: Can not open ' . $fileName);
			return false;
		}
		// read file header
		$meta = unpack('vtype/Vfilesize/Vreserved/Voffset', fread($fh, 14));
		// check for bitmap
		if ($meta['type'] != 19778) {
			fclose($fh);
			rlog('imagecreatefrombmp: Can not open ' . $fileName . ' is not a bitmap!');
			return false;
		}
		// read image header
		$meta += unpack('Vheadersize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vcolors/Vimportant', fread($fh, 40));
		// read additional 16bit header
		if ($meta['bits'] == 16) {
			$meta += unpack('VrMask/VgMask/VbMask', fread($fh, 12));
		}
		// set bytes and padding
		$meta['bytes'] = $meta['bits'] / 8;
		$this->bitDepth = $meta['bits']; //remember the bit depth for the imagebmp call
		$meta['decal'] = 4 - (4 * (($meta['width'] * $meta['bytes'] / 4) - floor($meta['width'] * $meta['bytes'] / 4)));
		if ($meta['decal'] == 4) {
			$meta['decal'] = 0;
		}
		// obtain imagesize
		if ($meta['imagesize'] < 1) {
			$meta['imagesize'] = $meta['filesize'] - $meta['offset'];
			// in rare cases filesize is equal to offset so we need to read physical size
			if ($meta['imagesize'] < 1) {
				$meta['imagesize'] = @filesize($fileName) - $meta['offset'];
				if ($meta['imagesize'] < 1) {
					fclose($fh);
					rlog('imagecreatefrombmp: Can not obtain file size of ' . $fileName . ' is not a bitmap!');
					return false;
				}
			}
		}
		// calculate colors
		$meta['colors'] = !$meta['colors'] ? pow(2, $meta['bits']) : $meta['colors'];
		// read color palette
		$palette = array();
		if ($meta['bits'] < 16) {
			$palette = unpack('l' . $meta['colors'], fread($fh, $meta['colors'] * 4));
			// in rare cases the color value is signed
			if ($palette[1] < 0) {
				foreach ($palette as $i => $color) {
					$palette[$i] = $color + 16777216;
				}
			}
		}
		// create gd image
		$im = imagecreatetruecolor($meta['width'], $meta['height']);
		if ($im == false) {
			fclose($fh);
			rlog('imagecreatefrombmp: imagecreatetruecolor failed for file "' . $fileName . '" with dimensions ' . $meta['width'] . 'x' . $meta['height']	);
			return false;
		}

		$data = fread($fh, $meta['imagesize']);
		$p = 0;
		$vide = chr(0);
		$y = $meta['height'] - 1;
		$error = 'imagecreatefrombmp: ' . $fileName . ' has not enough data!';
		// loop through the image data beginning with the lower left corner
		while ($y >= 0) {
			$x = 0;
			while ($x < $meta['width']) {
				switch ($meta['bits']) {
					case 32:
					case 24:
						if (!($part = substr($data, $p, 3))) {
							rlog($error);
							return $im;
						}
						$color = @unpack('V', $part . $vide);
						break;
					case 16:
						if (!($part = substr($data, $p, 2))) {
							fclose($fh);
							rlog($error);
							return $im;
						}
						$color = @unpack('v', $part);
						$color[1] = (($color[1] & 0xf800) >> 8) * 65536 + (($color[1] & 0x07e0) >> 3) * 256 + (($color[1] & 0x001f) << 3);
						break;
					case 8:
						$color = @unpack('n', $vide . substr($data, $p, 1));
						$color[1] = (isset($palette[$color[1] + 1])) ? $palette[$color[1] + 1] : $palette[1];
						break;
					case 4:
						$color = @unpack('n', $vide . substr($data, floor($p), 1));
						$color[1] = ($p * 2) % 2 == 0 ? $color[1] >> 4 : $color[1] & 0x0F;
						$color[1] = (isset($palette[$color[1] + 1])) ? $palette[$color[1] + 1] : $palette[1];
						break;
					case 1:
						$color = @unpack('n', $vide . substr($data, floor($p), 1));
						switch (($p * 8) % 8) {
							case 0:
								$color[1] = $color[1] >> 7;
								break;
							case 1:
								$color[1] = ($color[1] & 0x40) >> 6;
								break;
							case 2:
								$color[1] = ($color[1] & 0x20) >> 5;
								break;
							case 3:
								$color[1] = ($color[1] & 0x10) >> 4;
								break;
							case 4:
								$color[1] = ($color[1] & 0x8) >> 3;
								break;
							case 5:
								$color[1] = ($color[1] & 0x4) >> 2;
								break;
							case 6:
								$color[1] = ($color[1] & 0x2) >> 1;
								break;
							case 7:
								$color[1] = ($color[1] & 0x1);
								break;
						}
						$color[1] = (isset($palette[$color[1] + 1])) ? $palette[$color[1] + 1] : $palette[1];
						break;
					default:
						fclose($fh);
						rlog('imagecreatefrombmp: ' . $fileName . ' has ' . $meta['bits'] . ' bits and this is not supported!');
						return false;
				}
				imagesetpixel($im, $x, $y, $color[1]);
				$x++;
				$p += $meta['bytes'];
			}
			$y--;
			$p += $meta['decal'];
		}
		fclose($fh);
		return $im;
	}

	/**
	 * Resizes the image preserving ratio.
	 *
	 * @param integer $maxSize The maximum size of either the width or height.
	 * @return bool
	 */
	public function resize($maxSize) {
		if (!$this->valid()) {
			rlog('No image loaded');
			return false;
		}
		$widthOrig = imageSX($this->resource);
		$heightOrig = imageSY($this->resource);
		$ratioOrig = $widthOrig / $heightOrig;

		if ($ratioOrig > 1) {
			$newHeight = round($maxSize / $ratioOrig);
			$newWidth = $maxSize;
		} else {
			$newWidth = round($maxSize * $ratioOrig);
			$newHeight = $maxSize;
		}

		$this->preciseResize(round($newWidth), round($newHeight));
		return true;
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return bool
	 */
	public function preciseResize($width, $height) 
	{
		if (!$this->valid()) {
			rlog('No image loaded', array('app' => 'core'));
			return false;
		}
		$widthOrig = imageSX($this->resource);
		$heightOrig = imageSY($this->resource);
		$process = imagecreatetruecolor($width, $height);

		if ($process == false) {
			rlog('Error creating true color image');
			imagedestroy($process);
			return false;
		}

		// preserve transparency
		if ($this->imageType == IMAGETYPE_GIF or $this->imageType == IMAGETYPE_PNG) {
			imagecolortransparent($process, imagecolorallocatealpha($process, 0, 0, 0, 127));
			imagealphablending($process, false);
			imagesavealpha($process, true);
		}

		imagecopyresampled($process, $this->resource, 0, 0, 0, 0, $width, $height, $widthOrig, $heightOrig);
		if ($process == false) {
			rlog('Error re-sampling process image');
			imagedestroy($process);
			return false;
		}
		imagedestroy($this->resource);
		$this->resource = $process;
		return true;
	}

	/**
	 * Crops the image to the middle square. If the image is already square it just returns.
	 *
	 * @param int $size maximum size for the result (optional)
	 * @return bool for success or failure
	 */
	public function centerCrop($size = 0) {
		if (!$this->valid()) {
			rlog('No image loaded');
			return false;
		}
		$widthOrig = imageSX($this->resource);
		$heightOrig = imageSY($this->resource);
		if ($widthOrig === $heightOrig and $size == 0) {
			return true;
		}
		$ratioOrig = $widthOrig / $heightOrig;
		$width = $height = min($widthOrig, $heightOrig);

		if ($ratioOrig > 1) {
			$x = ($widthOrig / 2) - ($width / 2);
			$y = 0;
		} else {
			$y = ($heightOrig / 2) - ($height / 2);
			$x = 0;
		}
		if ($size > 0) {
			$targetWidth = $size;
			$targetHeight = $size;
		} else {
			$targetWidth = $width;
			$targetHeight = $height;
		}
		$process = imagecreatetruecolor($targetWidth, $targetHeight);
		if ($process == false) {
			rlog('Error creating true color image');
			imagedestroy($process);
			return false;
		}

		// preserve transparency
		if ($this->imageType == IMAGETYPE_GIF or $this->imageType == IMAGETYPE_PNG) {
			imagecolortransparent($process, imagecolorallocatealpha($process, 0, 0, 0, 127));
			imagealphablending($process, false);
			imagesavealpha($process, true);
		}

		imagecopyresampled($process, $this->resource, 0, 0, $x, $y, $targetWidth, $targetHeight, $width, $height);
		if ($process == false) {
			rlog('Error re-sampling process image ' . $width . 'x' . $height);
			imagedestroy($process);
			return false;
		}
		imagedestroy($this->resource);
		$this->resource = $process;
		return true;
	}

	/**
	 * Crops the image from point $x$y with dimension $wx$h.
	 *
	 * @param int $x Horizontal position
	 * @param int $y Vertical position
	 * @param int $w Width
	 * @param int $h Height
	 * @return bool for success or failure
	 */
	public function crop($x, $y, $w, $h) {
		if (!$this->valid()) {
			rlog('No image loaded');
			return false;
		}
		$process = imagecreatetruecolor($w, $h);
		if ($process == false) {
			rlog('Error creating true color image');
			imagedestroy($process);
			return false;
		}

		// preserve transparency
		if ($this->imageType == IMAGETYPE_GIF or $this->imageType == IMAGETYPE_PNG) {
			imagecolortransparent($process, imagecolorallocatealpha($process, 0, 0, 0, 127));
			imagealphablending($process, false);
			imagesavealpha($process, true);
		}

		imagecopyresampled($process, $this->resource, 0, 0, $x, $y, $w, $h, $w, $h);
		if ($process == false) {
			rlog('Error re-sampling process image ' . $w . 'x' . $h);
			imagedestroy($process);
			return false;
		}
		imagedestroy($this->resource);
		$this->resource = $process;
		return true;
	}

	/**
	 * Resizes the image to fit within a boundary while preserving ratio.
	 *
	 * Warning: Images smaller than $maxWidth x $maxHeight will end up being scaled up
	 *
	 * @param integer $maxWidth
	 * @param integer $maxHeight
	 * @return bool
	 */
	public function fitIn($maxWidth, $maxHeight) {
		if (!$this->valid()) {
			rlog('No image loaded');
			return false;
		}
		$widthOrig = imageSX($this->resource);
		$heightOrig = imageSY($this->resource);
		$ratio = $widthOrig / $heightOrig;

		$newWidth = min($maxWidth, $ratio * $maxHeight);
		$newHeight = min($maxHeight, $maxWidth / $ratio);

		$this->preciseResize(round($newWidth), round($newHeight));
		return true;
	}

	/**
	 * Shrinks larger images to fit within specified boundaries while preserving ratio.
	 *
	 * @param integer $maxWidth
	 * @param integer $maxHeight
	 * @return bool
	 */
	public function scaleDownToFit($maxWidth, $maxHeight) 
	{
		if (!$this->valid()) {
			rlog('No image loaded');
			return false;
		}
		$widthOrig = imageSX($this->resource);
		$heightOrig = imageSY($this->resource);

		if ($widthOrig > $maxWidth || $heightOrig > $maxHeight) {
			return $this->fitIn($maxWidth, $maxHeight);
		}

		return false;
	}
	
	/**
	 * mkpng
	 * 
	 * a testing example
	 *
	 * @param mixed $text This is a description
	 * @param mixed $width This is a description
	 * @param mixed $height This is a description
	 * @param mixed $previewimgfile This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function mkpng($text, $x, $y, $width=400, $height=300, $bgfile=null, $savefile=null)
	{
		if (!$savefile) {
			$fname = md5('mkpng_'.rand().time());
			$savefile = RPATH_CACHE.DS.$fname.'.png';
		}
		//$im = imagecreatetruecolor($width, $height);
		if (!$bgfile)
			$bgfile = RPATH_THEME.DS."sm".DS."img".DS."qabg.png";
		
		$im = @imagecreatefrompng($bgfile);
		if (!$im){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecreatetruecolor failed!w=".$width.',h='.$height);
			return false;
		}
		imagealphablending($im, true); // setting alpha blending on
		imagesavealpha($im, true); // save alphablending setting (important)
		
		
		/* Set a White & Transparent Background Color */
		//qabg
		/*$bgcolor = imagecolorallocatealpha($im, 255, 255, 255, 100); // (PHP 4 >= 4.3.2, PHP 5)
		if (!$bgcolor){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecolorallocatealpha failed!");
			return false;
		}*/
		
		//填充
		//imagefill($im, 0, 0, $bgcolor);
		
		$textcolor = imagecolorallocate($im, 0, 0, 0);
		
		// imagestring ( resource $image , int $font , int $x , int $y , string $s , int $col ) : bool
		
		//$font = RPATH_SUPPORTS.DS."fonts".DS."song.ttf";
		$font = RPATH_SUPPORTS.DS."fonts".DS."msyhbd.ttf";
		
		
		// imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text ) : array
		$res = imagettftext($im, 10, 0, $x, $y, $textcolor, $font, $text);
		
		//$res = imagestring($im, 5, $x, $y,  $text, $textcolor);		
		//if (!$res){
		//	rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagettftext failed!");
		//	return false;
		//}		
		
		$res = imagepng($im, $savefile);
		if (!$res){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call ImageColorAllocateAlpha failed!");
			return false;
		}
		imagedestroy($im);
		
		return true;
	}
	
	
	public function mknopic($text, $width=128, $height=128)
	{
		!$width && $width=128;
		!$height && $height=128;
		
		$im = imagecreatetruecolor($width, $height);
		if (!$im){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecreatetruecolor failed!w=".$width.',h='.$height);
			return false;
		}
		
		$bgcolor = imagecolorallocatealpha($im, 240, 240, 240, 100); // (PHP 4 >= 4.3.2, PHP 5)
		if (!$bgcolor){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecolorallocatealpha failed!");
			return false;
		}
		
		//填充
		imagefill($im, 0, 0, $bgcolor);
		
		$nr = strlen($text);
		$size = 14;
		$angle = 0;
		$x = 0;
		$y = 0;
		
		
		
		$textcolor = imagecolorallocate($im, 102, 102, 102);
		
		// imagestring ( resource $image , int $font , int $x , int $y , string $s , int $col ) : bool
		
		$font = RPATH_SUPPORTS.DS."fonts".DS."song.ttf";
		
		$szdb = array(14, 12, 10, 8, 7);
		foreach ($szdb as $key=>$v) {
			$size = $v;
			
			$box   = imagettfbbox($size, $angle, $font, $text);
			if( !$box )
				return false;
			$min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
			$max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
			$min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
			$max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
			
			$t_width  = ( $max_x - $min_x );
			$t_height = ( $max_y - $min_y );
			
			//rlog('size='.$size.',$t_width='.$t_width);
			
			if ($t_width > $width)
				continue;
			
			$x = ceil(($width - $t_width)/2);
			$y = ceil(($height)/2);
			
			break;
		}
		
		
		$res = imagettftext($im, $size, $angle, $x, $y, $textcolor, $font, $text);
		if (!$res){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagettftext failed!");
			return false;
		}		
		
		$res = imagepng($im, $previewimgfile);
		if (!$res){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call ImageColorAllocateAlpha failed!");
			return false;
		}
		imagedestroy($im);
		
		return $previewimgfile;
	}
	
	
	public function cropImage($srcfile, $x, $y, $w, $h, $savefile,  $target_w=0, $target_h=0)
	{
		$szs = @getimagesize($srcfile);
		if (!$szs) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getimagesize filed! szs=$szs, src='$srcfile'");
			return false;
		}
		
		list($orig_width, $orig_height, $bigType) = $szs;
		$mimetype = $szs['mime'];
		
		//w,h为0时，使用原尺寸
		!$w && $w = $orig_width;
		!$h && $h = $orig_height;
		
		switch ($bigType) {
			case 1: 
				$im = @imagecreatefromgif($srcfile);
				break;	 
			case 2: 
				$im = @imagecreatefromjpeg($srcfile); 
				break;	 
			case 3: 
				$im = @imagecreatefrompng($srcfile); 
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Unkown cropping image type '$bigType'!");
				return false;
		}
		
		if (!$im) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "unknown images '$srcfile'");
			return false;
		}
		
		!$target_w && $target_w = $w;
		!$target_h && $target_h = $h;
		
		$dst_r = ImageCreateTrueColor( $target_w, $target_h );
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "target_h=$target_h");
		/*
		 imagecopyresampled(
		  resource $dst_image,
		  resource $src_image,
		  int $dst_x,
		  int $dst_y,
		  int $src_x,
		  int $src_y,
		  int $dst_w,
		  int $dst_h,
		  int $src_w,
		  int $src_h
		): bool*/
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $x, $y, $w, $h, $target_w, $target_h);
		
		$res = imagecopyresampled($dst_r, $im, 0, 0, $x, $y,
				$target_w, $target_h, $w, $h);
		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecopyresampled failed!");
			return false;
		}
		
		$res = imagepng($dst_r, $savefile);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagepng failed!");
			return false;
		}
		
		return $res;
	}

	/**
	 * Destroys the current image and resets the object
	 */
	public function destroy() {
		if ($this->valid()) {
			imagedestroy($this->resource);
		}
		$this->resource = null;
	}

	public function __destruct() {
		$this->destroy();
	}
}

if (!function_exists('imagebmp')) {
	/**
	 * Output a BMP image to either the browser or a file
	 *
	 * @link http://www.ugia.cn/wp-data/imagebmp.php
	 * @author legend <legendsky@hotmail.com>
	 * @link http://www.programmierer-forum.de/imagebmp-gute-funktion-gefunden-t143716.htm
	 * @author mgutt <marc@gutt.it>
	 * @version 1.00
	 * @param string $fileName [optional] <p>The path to save the file to.</p>
	 * @param int $bit [optional] <p>Bit depth, (default is 24).</p>
	 * @param int $compression [optional]
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	function imagebmp($im, $fileName = '', $bit = 24, $compression = 0) 
	{
		if (!in_array($bit, array(1, 4, 8, 16, 24, 32))) {
			$bit = 24;
		} else if ($bit == 32) {
			$bit = 24;
		}
		$bits = pow(2, $bit);
		imagetruecolortopalette($im, true, $bits);
		$width = imagesx($im);
		$height = imagesy($im);
		$colorsNum = imagecolorstotal($im);
		$rgbQuad = '';
		if ($bit <= 8) {
			for ($i = 0; $i < $colorsNum; $i++) {
				$colors = imagecolorsforindex($im, $i);
				$rgbQuad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";
			}
			$bmpData = '';
			if ($compression == 0 || $bit < 8) {
				$compression = 0;
				$extra = '';
				$padding = 4 - ceil($width / (8 / $bit)) % 4;
				if ($padding % 4 != 0) {
					$extra = str_repeat("\0", $padding);
				}
				for ($j = $height - 1; $j >= 0; $j--) {
					$i = 0;
					while ($i < $width) {
						$bin = 0;
						$limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;
						for ($k = 8 - $bit; $k >= $limit; $k -= $bit) {
							$index = imagecolorat($im, $i, $j);
							$bin |= $index << $k;
							$i++;
						}
						$bmpData .= chr($bin);
					}
					$bmpData .= $extra;
				}
			} // RLE8
			else if ($compression == 1 && $bit == 8) {
				for ($j = $height - 1; $j >= 0; $j--) {
					$lastIndex = "\0";
					$sameNum = 0;
					for ($i = 0; $i <= $width; $i++) {
						$index = imagecolorat($im, $i, $j);
						if ($index !== $lastIndex || $sameNum > 255) {
							if ($sameNum != 0) {
								$bmpData .= chr($sameNum) . chr($lastIndex);
							}
							$lastIndex = $index;
							$sameNum = 1;
						} else {
							$sameNum++;
						}
					}
					$bmpData .= "\0\0";
				}
				$bmpData .= "\0\1";
			}
			$sizeQuad = strlen($rgbQuad);
			$sizeData = strlen($bmpData);
		} else {
			$extra = '';
			$padding = 4 - ($width * ($bit / 8)) % 4;
			if ($padding % 4 != 0) {
				$extra = str_repeat("\0", $padding);
			}
			$bmpData = '';
			for ($j = $height - 1; $j >= 0; $j--) {
				for ($i = 0; $i < $width; $i++) {
					$index = imagecolorat($im, $i, $j);
					$colors = imagecolorsforindex($im, $index);
					if ($bit == 16) {
						$bin = 0 << $bit;
						$bin |= ($colors['red'] >> 3) << 10;
						$bin |= ($colors['green'] >> 3) << 5;
						$bin |= $colors['blue'] >> 3;
						$bmpData .= pack("v", $bin);
					} else {
						$bmpData .= pack("c*", $colors['blue'], $colors['green'], $colors['red']);
					}
				}
				$bmpData .= $extra;
			}
			$sizeQuad = 0;
			$sizeData = strlen($bmpData);
			$colorsNum = 0;
		}
		$fileHeader = 'BM' . pack('V3', 54 + $sizeQuad + $sizeData, 0, 54 + $sizeQuad);
		$infoHeader = pack('V3v2V*', 0x28, $width, $height, 1, $bit, $compression, $sizeData, 0, 0, $colorsNum, 0);
		if ($fileName != '') {
			$fp = fopen($fileName, 'wb');
			fwrite($fp, $fileHeader . $infoHeader . $rgbQuad . $bmpData);
			fclose($fp);
			return true;
		}
		echo $fileHeader . $infoHeader . $rgbQuad . $bmpData;
		return true;
	}
}

if (!function_exists('exif_imagetype')) {
	/**
	 * Workaround if exif_imagetype does not exist
	 *
	 * @link http://www.php.net/manual/en/function.exif-imagetype.php#80383
	 * @param string $fileName
	 * @return string|boolean
	 */
	function exif_imagetype($fileName) {
		if (($info = getimagesize($fileName)) !== false) {
			return $info[2];
		}
		return false;
	}
}