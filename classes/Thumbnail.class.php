<?php
/********
 * Generate thumbnails
 * Creation: 05/21/2007
 * Modification: 02/04/2009
 *
 * * * * *
 * Sample usage:
 *
 * $thumb = new Thumbnail("/tmp/image.jpg");
 * $thumb->save("/tmp/thumb", 30, 40);						# Crop and Stretch JPEG
 * $thumb->save("/tmp/thumb", 30, 40, TM_NOCROP | TM_NOSTRETCH | TM_AUTOLOCK);	# Don't Crop and don't Stretch JPEG
 * $thumb->save("/tmp/thumb", 30, 40, TM_DEFAULT, "png");			# Crop and Stretch PNG
 */

// Mode Constants
define("TM_CROP", 0);
define("TM_NOCROP", 1);

define("TM_STRETCH", 0);
define("TM_NOSTRETCH", 2);

define("TM_AUTOLOCK", 0);
define("TM_LOCKWIDTH", 4);
define("TM_LOCKHEIGHT", 8);

define("TM_FIXEDTHUMB", 16);

define("TM_DEFAULT", TM_CROP | TM_STRETCH | TM_AUTOLOCK);


class Thumbnail
{
	/* Class attributes */
	private $mask; // Thumbnail's mask image (Formats: PNG/GIF)
	private $maskAlpha; // Alpha value for the mask image
	private $maskFormat; // Mask's format (PNG/GIF)
	private $maskInfo; // Information about the mask image
	private $source; // Source image for thumbnail's creation
	private $sourceInfo; // Information about the source image


	/**************
	 * Constructor - Initiate Thumbnail object
	 *
	 * @Parameters:
	 *  - $source (optional): Path to the source image  OR  GD Resource image
	 *
	 * @Returns:
	 *  - Pointer to a Thumbnail object
	 *
	 * @Throws:
	 *  - ThumbnailException if $source is not a valid source image
	 */

	public function __construct($source='')
	{
		if ($source)
		{
			if ($this->checkResource($source))
			{
				$this->setGDSource($source);
			}
			else
			{
				$this->setSource($source);
			}
		}
		else
		{
			$this->source = null;
			$this->sourceInfo = null;
		}

		$this->mask = null;
		$this->maskAlpha = 100;
		$this->maskFormat = '';
	}


	/**************
	 * setSource - Set source image for thumbnail's creation
	 *
	 * @Parameters:
	 *  - $file: Path to the source image
	 *
	 * @Returns:
	 *  - true if succeded
	 *
	 * @Throws:
	 *  - ThumbnailException if $file is not a valid source image
	 */

	public function setSource($file)
	{
		if (!file_exists($file))
		{
			throw new ThumbnailException(EX_INVALID_SOURCE);
		}

		$sourceInfo = getImageSize($file);
		switch ($sourceInfo['mime'])
		{
			case "image/jpeg": $sourceInfo['p'] = "ImageCreateFromJPEG"; break;
			case "image/png": $sourceInfo['p'] = "ImageCreateFromPNG"; break;
			case "image/gif": $sourceInfo['p'] = "ImageCreateFromGIF"; break;
			case "image/vnd.wap.wbmp": $sourceInfo['p'] = "ImageCreateFromWBMP"; break;
			default: throw new ThumbnailException(EX_INVALID_SOURCE); break;
		}

		$this->source = $file;
		$this->sourceInfo = $sourceInfo;

		return true;
	}


	/**************
	 * setGDSource - Set source image (From a GD Resource) for thumbnail's creation
	 *
	 * @Parameters:
	 *  - $resource: Source image's GD resource
	 *
	 * @Returns:
	 *  - true if succeded
	 *
	 * @Throws:
	 *  - ThumbnailException if $resource is not a valid GD resource
	 */

	public function setGDSource($resource)
	{
		if (!$this->checkResource($resource))
		{
			throw new ThumbnailException(EX_INVALID_SOURCE);
		}

		$sourceInfo = Array(imagesx($resource), imagesy($resource));

		$this->source = $resource;
		$this->sourceInfo = $sourceInfo;

		return true;
	}


	/**************
	 * setMask - Set mask image for thumbnail's creation
	 *
	 * @Parameters:
	 *  - $mask: Path to the mask image
	 *  - $alpha (optional): Alpha value for the mask image
	 *
	 * @Returns:
	 *  - true if succeded
	 *
	 * @Throws:
	 *  - ThumbnailException if $mask is not a valid mask image
	 *  - ThumbnailException if $alpha is not a valid alpha value
	 */

	public function setMask($mask, $alpha=100)
	{
		if (!file_exists($mask))
		{
			throw new ThumbnailException(EX_INVALID_MASK);
		}

		$data = getImageSize($mask);
		if (!in_array($data['mime'], Array("image/gif", "image/png")))
		{
			throw new ThumbnailException(EX_INVALID_MASK);
		}

		if ((int)$alpha < 0 || (int)$alpha > 100)
		{
			throw new ThumbnailException(EX_INVALID_ALPHA);
		}

		$this->mask = $mask;
		$this->maskAlpha = round($alpha);
		$this->maskFormat = str_replace("image/","",$data['mime']);
		$this->maskInfo = $data;

		return true;
	}

	/**************
	 * setMask24 - Set mask image for thumbnail's creation (PNG 24 Only)
	 *
	 * @Parameters:
	 *  - $mask: Path to the mask image (PNG 24)
	 *  - $alpha (optional): Alpha value for the mask image
	 *
	 * @Returns:
	 *  - true if succeded
	 *
	 * @Throws:
	 *  - ThumbnailException if $mask is not a valid mask image
	 *  - ThumbnailException if $alpha is not a valid alpha value
	 */

	public function setMask24($mask, $alpha=100)
	{
		$this->setMask($mask, $alpha);
		$this->maskInfo['png24'] = true;

		return true;
	}

	/**************
	 * save - Save thumbnail on disk
	 *
	 * @Parameters:
	 *  - $path: Path to where the thumbnail must be saved in (No extension needed, just path and filename)
	 *  - $width: Width of the thumbnail (If you are using a mask this will be ignored)
	 *  - $height: Height of the thumbnail (If you are using a mask this will be ignored)
	 *  - $mode: Thumbnail generation mode (Use constants defined on the top of this file. Multiple constants allowed. Default: TM_DEFAULT)
	 *  - $format: Format of the thumbnail (JPEG (Default), GIF or PNG only)
	 *
	 * @Returns:
	 *  - true if succeded
	 *
	 * @Throws:
	 *  - ThumbnailException if no source were setted
	 *  - ThumbnailException if $width or $height is lower than 5
	 */

	public function save($path, $width, $height, $mode=TM_DEFAULT, $format='jpeg')
	{
		if ($width < 5 || $height < 5)
		{
			throw new ThumbnailException(EX_INVALID_DIMENSION);
		}

		if (!$this->checkSource())
		{
			throw new ThumbnailException(EX_INVALID_SOURCE);
		}
		else
		{
			// Format
			$format = strtolower($format);

			// Create mask image
			if ($this->checkMask())
			{
				$p = "ImageCreateFrom{$this->maskFormat}";
		        	$border = $p($this->mask);

				$width = $this->maskInfo[0];
				$height = $this->maskInfo[1];
			}

			// Get dimensions
			list($newWidth, $newHeight) = $this->getDimensions($width, $height, $mode);

			// Create images
			$image = $this->checkResource($this->source) ? $this->source : $this->sourceInfo['p']($this->source);
			$thumb = ImageCreateTrueColor($mode & TM_NOCROP ? $newWidth : $width, $mode & TM_NOCROP ? $newHeight : $height);

			// Apply alpha (PNG only)
			if ($format == "png")
			{
				ImageFill($thumb, 0, 0, ImageColorResolveAlpha($thumb, 0, 0, 0, 255));
				ImageAlphaBlending($thumb, true);
				ImageSaveAlpha($thumb, true);
			}

			// Get crop position
			$x = $mode & TM_NOCROP ? 0 : $newWidth/2 - $width/2;
			$y = $mode & TM_NOCROP ? 0 : $newHeight/2 - $height/2;
			//$y = 0;

		        // Crop & Stretch
			ImageCopyResampled($thumb, $image, -$x, -$y, 0, 0, $newWidth, $newHeight, $this->sourceInfo[0], $this->sourceInfo[1]);

		        // Place border mask
			if ($this->checkMask())
			{
				if ($this->maskInfo['png24'])
				{
					$this->merge($thumb, $border);
				}
				else
				{
					ImageCopyMerge($thumb, $border, 0, 0, 0, 0, $width, $height, $this->maskAlpha);
				}

				ImageDestroy($border);
			}

			// Create directory
			$dir = substr($path,0,strrpos($path,"/"));
			@mkdir($dir, 0777, true);

			// Save image
			$p = "Image{$format}";
			$format = $format == "jpeg" ? "jpg" : $format;
			// editado: 100 paramatro da qualidade da imagem			
			$p($thumb, "{$path}.{$format}", 50);

			// Clear memory
			if (!$this->checkResource($this->source))
				ImageDestroy($image);
			ImageDestroy($thumb);

			return true;
		}
	}

	// Get thumbnail dimensions
	private function getDimensions($thumbWidth, $thumbHeight, $mode)
	{
		$imageHeight = $this->sourceInfo[1];
		$imageWidth = $this->sourceInfo[0];
		$newHeight = -1;
		$newWidth = -1;

		if ($mode & TM_NOSTRETCH)
		{
			if ($mode & TM_LOCKWIDTH && $thumbWidth < $imageWidth && $thumbHeight < $imageHeight)
			{
				return Array($thumbWidth, round((($thumbWidth * 100 / $imageWidth) / 100) * $imageHeight));
			}
			else if ($mode & TM_LOCKHEIGHT && $thumbWidth < $imageWidth && $thumbHeight < $imageHeight)
			{
				return Array(round((($thumbHeight * 100 / $imageHeight) / 100) * $imageWidth), $thumbHeight);
			}
			else
			{
				$newWidth = $imageWidth;
				$newHeight = $imageHeight;

				$thumbWidth = min($thumbWidth, $imageWidth);
				$thumbHeight = min($thumbHeight, $imageHeight);

				if ($newWidth > $thumbWidth)
				{
					$newWidth = $thumbWidth;
					$newHeight = (($newWidth * 100 / $imageWidth) / 100) * $imageHeight;
				}

				if ($newHeight > $thumbHeight)
				{
					$newHeight = $thumbHeight;
					$newWidth = (($newHeight * 100 / $imageHeight) / 100) * $imageWidth;
				}

				return Array($newWidth, $newHeight);
			}
		}
		else if ($mode & TM_LOCKWIDTH)
		{
			return Array($thumbWidth, round((($thumbWidth * 100 / $imageWidth) / 100) * $imageHeight));
		}
		else if ($mode & TM_LOCKHEIGHT)
		{
			return Array(round((($thumbHeight * 100 / $imageHeight) / 100) * $imageWidth), $thumbHeight);
		}
		else if ($mode & TM_FIXEDTHUMB)
		{
			return Array($thumbWidth, $thumbHeight);
		}


        	while ($newHeight < $thumbHeight || $newWidth < $thumbWidth)
	        {
                	if ($imageWidth < $imageHeight)
        	        {
	                        $newWidth = $thumbWidth;
                        	$newHeight = ceil(($thumbWidth * $imageHeight) / $imageWidth);
                	}
        	        else if ($imageHeight < $imageWidth)
	                {
                        	$newHeight = $thumbHeight;
                	        $newWidth = ceil(($thumbHeight * $imageWidth) / $imageHeight);
        	        }
	                else
                	{
        	                $newHeight = $newWidth = max($thumbWidth, $thumbHeight);
	                }

                	// Make sure it fits the dimension setted
        	        if ($newHeight < $thumbHeight)
	                        $thumbWidth++;
                	else if ($newWidth < $thumbWidth)
        	                $thumbHeight++;
	        }

		return Array($newWidth, $newHeight);
	}

	// Check if there's a defined source
	private function checkSource()
	{
		return $this->source ? true : false;
	}

	// Check if there's a defined mask
	private function checkMask()
	{
		return $this->mask ? true : false;
	}

	// Check if $resource is a valid GD resource
	private function checkResource($resource)
	{
		return strToLower(getType($resource)) == "resource" && strToLower(@get_resource_type($resource)) == "gd";
	}

	// Merge images
	function merge($destImg, $overlayImg)
	{
		// Get image dimensions
		$imgW = imageSX($destImg);
		$imgH = imageSY($destImg);

		// Loop over each pixel
		for ($y=0;$y<$imgH;$y++)
		{
			for ($x=0;$x<$imgW;$x++)
			{
				// Get pixel's RGBA
				$ovrARGB = imagecolorat($overlayImg, $x, $y);
				$ovrA = ($ovrARGB >> 24) << 1;
				$ovrR = $ovrARGB >> 16 & 0xFF;
				$ovrG = $ovrARGB >> 8 & 0xFF;
				$ovrB = $ovrARGB & 0xFF;

				// Merge pixels
				$change = false;
				if ($ovrA == 0)
				{
					$dstR = $ovrR;
					$dstG = $ovrG;
					$dstB = $ovrB;
					$change = true;
				}
				else if ($ovrA < 254)
			    {
					$dstARGB = imagecolorat($destImg, $x, $y);
					$dstR = $dstARGB >> 16 & 0xFF;
					$dstG = $dstARGB >> 8 & 0xFF;
					$dstB = $dstARGB & 0xFF;

					$dstR = (($ovrR * (0xFF-$ovrA)) >> 8) + (($dstR * $ovrA) >> 8);
					$dstG = (($ovrG * (0xFF-$ovrA)) >> 8) + (($dstG * $ovrA) >> 8);
					$dstB = (($ovrB * (0xFF-$ovrA)) >> 8) + (($dstB * $ovrA) >> 8);
					$change = true;
				}

				if ($change)
				{
					$dstRGB = imagecolorallocatealpha($destImg, $dstR, $dstG, $dstB, 0);
					imagesetpixel($destImg, $x, $y, $dstRGB);
				}
			}
		}

		return $destImg;
	}
}


// Exceptions
define("EX_INVALID_SOURCE", 1);
define("EX_INVALID_MASK", 2);
define("EX_INVALID_ALPHA", 3);
define("EX_INVALID_DIMENSION", 4);

class ThumbnailException extends Exception
{
	public function __construct($exno)
	{
		$exstr = "";

		switch ($exno)
		{
			case EX_INVALID_SOURCE:
				$exstr = "Invalid source image.";
			break;

			case EX_INVALID_MASK:
				$exstr = "Invalid mask image.";
			break;

			case EX_INVALID_ALPHA:
				$exstr = "Invalid alpha (Must be between 0~100).";
			break;

			case EX_INVALID_DIMENSION:
				$exstr = "Invalid dimensions (Width/height must be higher than 5).";
			break;
		}

		parent::__construct($exstr);
	}
}
?>