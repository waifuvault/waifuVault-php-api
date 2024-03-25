<?php

namespace ErnestMarcinko\WaifuVault;

class WaifuError {
	/**
	 * @param string $name
	 * @param string $message
	 * @param int $status
	 */
	public function __construct(
		public string $name = '',
		public string $message = '',
		public int    $status = 0,
	) {}
}
