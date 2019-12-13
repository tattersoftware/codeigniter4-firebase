<?php namespace Tatter\Firebase\Config;

use CodeIgniter\Config\BaseConfig;

class Firebase extends BaseConfig
{
	// Whether to continue instead of throwing exceptions
	public $silent = false;
	
	// Path to the Google Application Credentials key file
	public $keyfile = APPPATH . 'ThirdParty/Firestore/keyfile.json';
}
