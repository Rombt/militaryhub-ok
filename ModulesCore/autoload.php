<?php

spl_autoload_register( function ($class) {
	if ( strpos( $class, 'ModulesCore\\' ) === 0 ) {
		$className = str_replace( 'ModulesCore\\', '', $class );
		$file = __DIR__ . '/' . $className . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
} );

use ModulesCore\ModuleLoader;

$loader = new ModuleLoader( __DIR__ . '/modules' );
$loader->loadModules();