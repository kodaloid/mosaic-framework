<?php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;


/**
 * Provides a number of utilities for the Mosaic CMS.
 */
class MosTools {

	/**
	 * Returns a QrCode SVG image, encoded into a DataURI.
	 */
	function GenerateQrImage(string $code, int $size = 300) {
		$renderer = new ImageRenderer(
			new RendererStyle($size, 1),
			new SvgImageBackEnd()
	  );
	  $writer = new Writer($renderer);
	  $image = $writer->writeString($code);

	  return 'data:image/svg+xml;base64, ' . base64_encode($image);
	}


	/**
	 * Generate a list of time zones.
	 */
	function GenerateTimezoneList() {
		static $time_zones = null;
    
		if ($time_zones === null) {
			$time_zones = [];
			$offsets = [];
			$now = new DateTime('now', new DateTimeZone('UTC'));
			
			foreach (DateTimeZone::listIdentifiers() as $timezone) {
					$now->setTimezone(new DateTimeZone($timezone));
					$offsets[] = $offset = $now->getOffset();
					$name = $this->FormatTimezoneName($timezone);
					$time_zones[$timezone] = '(' . $this->FormatGmtOffset($offset) . ') ' . $name;
			}
			
			array_multisort($offsets, $time_zones);
		}
		
		return $time_zones;
	}

	
	/**
	 * Format a timezone offset.
	 */
	private function FormatGmtOffset($offset) {
		$hours = intval($offset / 3600);
		$minutes = abs(intval($offset % 3600 / 60));
		return 'GMT' . ($offset!==false ? sprintf('%+03d:%02d', $hours, $minutes) : '');
  	}
  
	
	/**
	 * Format a timezone name.
	 */
	private function FormatTimezoneName($name) {
		$name = str_replace('/', ', ', $name);
		$name = str_replace('_', ' ', $name);
		$name = str_replace('St ', 'St. ', $name);
		return $name;
	}
}