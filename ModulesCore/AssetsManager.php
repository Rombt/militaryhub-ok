<?php

namespace ModulesCore;

class AssetsManager {
	protected static $css = [ 
		'head' => [ 
			'frontend' => [],
			'backend' => [],
		],
		'footer' => [ 
			'frontend' => [],
			'backend' => [],
		],
	];

	protected static $js = [ 
		'head' => [ 
			'frontend' => [],
			'backend' => [],
		],
		'footer' => [ 
			'frontend' => [],
			'backend' => [],
		],
	];

	/**
	 * Определяет, является ли текущий контекст backend или frontend
	 * @return string 'backend' или 'frontend'
	 */
	protected static function getContext() {
		return ( strpos( $_SERVER['REQUEST_URI'], '/backend' ) !== false ) ? 'backend' : 'frontend';
	}

	/**
	 * Регистрирует CSS-файл в нужной области (head/footer) и для нужного контекста (frontend/backend)
	 */
	public static function registerCss( $path ) {
		$position = ( strpos( $path, 'footer' ) !== false ) ? 'footer' : 'head';
		$context = self::getContext();

		if ( ! in_array( $path, self::$css[ $position ][ $context ] ) ) {
			self::$css[ $position ][ $context ][] = $path;
		}
	}

	/**
	 * Регистрирует JS-файл в нужной области (head/footer) и для нужного контекста (frontend/backend)
	 */
	public static function registerJs( $path ) {
		$position = ( strpos( $path, 'head' ) !== false ) ? 'head' : 'footer';
		$context = self::getContext();

		if ( ! in_array( $path, self::$js[ $position ][ $context ] ) ) {
			self::$js[ $position ][ $context ][] = $path;
		}
	}

	/**
	 * Возвращает список CSS-файлов по позиции и контексту
	 */
	public static function getCss( $position = 'head' ) {
		$context = self::getContext();
		return self::$css[ $position ][ $context ] ?? [];
	}

	/**
	 * Возвращает список JS-файлов по позиции и контексту
	 */
	public static function getJs( $position = 'footer' ) {
		$context = self::getContext();
		return self::$js[ $position ][ $context ] ?? [];
	}

	public static function renderCss( $position = 'head' ) {
		$result = '';
		foreach ( self::getCss( $position ) as $css ) {
			$result .= '<link rel="stylesheet" href="' . htmlspecialchars( $css, ENT_QUOTES ) . '">' . PHP_EOL;
		}
		return $result;
	}

	public static function renderJs( $position = 'footer' ) {
		$result = '';
		foreach ( self::getJs( $position ) as $js ) {
			$result .= '<script src="' . htmlspecialchars( $js, ENT_QUOTES ) . '" defer></script>' . PHP_EOL;
		}
		return $result;
	}
}