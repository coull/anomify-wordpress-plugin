<?php

class Anomify_Utils
{

	public static function stringToBool ($sString)
	{

		switch (strtolower($sString)) {

			case '1':
			case 'yes':
			case 'y':
			case 'true':
			case 'enabled':

				return true;

			case '0':
			case 'no':
			case 'n':
			case 'false':
			case 'disabled':

				return false;

			default:
				throw new Exception('Ambiguous non-boolean string cannot be converted: ' . $sString);

		}

	}

	public static function curlIsAvailable ()
	{

		if (true == function_exists('curl_init')) {
			return true;
		}

		return false;

	}

	public static function dataDirIsWritable ()
	{

		$sDataDir = Anomify::getDataDir();

		if (false == file_exists($sDataDir)) {
			return false;
		}

		if (false == is_dir($sDataDir)) {
			return false;
		}

		if (false == is_writable($sDataDir)) {
			return false;
		}

		return true;

	}

}
