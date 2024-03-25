<?php

namespace ErnestMarcinko\WaifuTests;

use phpmock\Mock;
use phpmock\MockBuilder;
use phpmock\MockEnabledException;

/**
 * Helper for mocking global PHP functions
 */
class GlobalMock {
	/**
	 * @var array<string, Mock>
	 */
	private static array $mocked = array();

	/**
	 * Creates a global function mock with a pre-determined response
	 *
	 * @param string $function_name
	 * @param mixed $return
	 * @throws MockEnabledException
	 */
	public static function mock(string $function_name, mixed $return): void {
		self::disable($function_name);
		$builder = new MockBuilder();
		$builder->setNamespace("ErnestMarcinko\\WaifuVault")
			->setName($function_name)
			->setFunction(is_callable($return) ? $return : fn()=>$return);
		$mock = $builder->build();
		$mock->enable();
		self::$mocked[$function_name] = $mock;
	}

	/**
	 * Disables all global function mocks or a single function mock if $function_name is set.
	 * @param string $function_name
	 * @return void
	 */
	public static function disable(string $function_name = ''): void {
		if ($function_name === '') {
			foreach (self::$mocked as $name => $return) {
				self::$mocked[$name]->disable();
				unset(self::$mocked[$name]);
			}
		} elseif (isset(self::$mocked[$function_name])) {
			self::$mocked[$function_name]->disable();
			unset(self::$mocked[$function_name]);
		}
	}
}
