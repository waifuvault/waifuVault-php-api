<?php

namespace ErnestMarcinko\WaifuVault\Exceptions;

use ErnestMarcinko\WaifuVault\WaifuError;
use Exception;

class WaifuException extends Exception {
	public function __construct(public WaifuError $waifuError) {
		parent::__construct($waifuError->message);
	}
}
