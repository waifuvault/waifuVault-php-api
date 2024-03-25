<?php

namespace ErnestMarcinko\WaifuVault;

class WaifuResponse {
	/**
	 * @param string $token
	 * @param string $url
	 * @param bool $protected
	 * @param int|string $retentionPeriod
	 */
	public function __construct(
		public string     $token = '',
		public string     $url = '',
		public bool       $protected = false,
		public int|string $retentionPeriod = ''
	) {}
}
