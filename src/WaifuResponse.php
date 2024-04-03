<?php

namespace ErnestMarcinko\WaifuVault;

/**
 * @phpstan-import-type WaifuResponseOptionsArgs from WaifuResponseOptions
 */
class WaifuResponse {
	/**
	 * @param string $token
	 * @param string $url
	 * @param int|string $retentionPeriod
	 * @param WaifuResponseOptionsArgs|WaifuResponseOptions $options
	 */
	public function __construct(
		readonly public string     $token = '',
		readonly public string     $url = '',
		readonly public int|string $retentionPeriod = '',
		public array|WaifuResponseOptions $options = array(),
	) {
		if (is_array($this->options)) {
			$this->options = new WaifuResponseOptions(...$this->options);
		}
	}
}
