<?php

namespace ErnestMarcinko\WaifuVault;

/**
 * @phpstan-type WaifuResponseOptionsArgs array{
 *     hideFilename?: bool,
 *     oneTimeDownload?: bool,
 *     protected?: bool
 * }
 */
readonly class WaifuResponseOptions {
	/**
	 * @param bool $hideFilename
	 * @param bool $oneTimeDownload
	 * @param bool $protected
	 */
	public function __construct(
		public bool $hideFilename = false,
		public bool $oneTimeDownload = false,
		public bool $protected = false
	) {}
}
