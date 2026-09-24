<?php




trait FeedManagerUtils {




	public static function get_obj( $obj, $maxDepth = 3 ) {
		$seen = [];

		$process_item = function ($item, $depth) use (&$process_item, &$seen, $maxDepth) {
			if ( $depth > $maxDepth ) {
				return '*MAX DEPTH REACHED*';
			}

			if ( is_object( $item ) ) {
				$id = spl_object_id( $item );
				if ( isset( $seen[ $id ][ $depth ] ) ) {
					return '*RECURSION DETECTED*';
				}
				if ( ! isset( $seen[ $id ] ) ) {
					$seen[ $id ] = [];
				}
				$seen[ $id ][ $depth ] = true;

				$result = new stdClass();
				foreach ( get_object_vars( $item ) as $key => $value ) {
					$result->$key = $process_item( $value, $depth + 1 );
				}
				return $result;
			} elseif ( is_array( $item ) ) {
				$result = [];
				foreach ( $item as $key => $value ) {
					$result[ $key ] = $process_item( $value, $depth + 1 );
				}
				return $result;
			} else {
				return $item; // Примитивы
			}
		};

		return $process_item( $obj, 1 ); // Начинаем с глубины 1
	}






	//===================================	Черновик	======================================
	//!! всё что ниже Требует доработки и тестирования!
	// public static function merge_google_table( string $tableName, string $jsonConfig ): array {


	// 	/**
	// 	 * Метод: importGoogleSheetsToDb
	// 	 * 
	// 	 * Назначение:
	// 	 * - Создаёт в БД таблицу, структура которой повторяет заголовки колонок Google Таблиц
	// 	 * - Один столбец задаётся как primary_key (задан вручную)
	// 	 * - Загружает данные из нескольких Google Таблиц (CSV)
	// 	 * - Гарантирует уникальность строк по значению primary_key
	// 	 * - Обновляет значения в случае дублирования primary_key из разных таблиц
	// 	 *
	// 	 * Входной параметр:
	// 	 * - string $tableName — имя таблицы БД, которую нужно создать
	// 	 * - string $primaryKey — имя столбца, который будет использоваться как PRIMARY KEY в таблице БД
	// 	 * - string $jsonConfig — JSON-объект, описывающий источники данных
	// 	 *
	// 	 * Структура JSON-конфига:
	// 	 * {
	// 	 *   "primary_key": "sku",
	// 	 *   "update_on_duplicate": true,
	// 	 *   "sources": [
	// 	 *     {
	// 	 *       "filePath": "/tmp/feed1.csv",
	// 	 *       "primary_column": "sku",
	// 	 *       "columns": ["sku", "name", "price"]
	// 	 *     },
	// 	 *     {
	// 	 *       "filePath": "/tmp/feed2.csv",
	// 	 *       "primary_column": "id",
	// 	 *       "columns": ["id", "name", "price"]
	// 	 *     }
	// 	 *   ]
	// 	 * }
	// 	 *
	// 	 * Поведение:
	// 	 * 1. Вычисляется объединённый список всех колонок из всех источников
	// 	 * 2. Типы данных определяются автоматически по первым 100 строкам (TEXT, INT, DECIMAL, BOOLEAN, DATE и пр.)
	// 	 * 3. Создаётся таблица в БД с этими колонками, primary_key — VARCHAR PRIMARY KEY
	// 	 * 4. Обработка источников:
	// 	 *    - строки читаются, значения приводятся к общему списку колонок
	// 	 *    - отсутствующие значения интерпретируются как NULL
	// 	 *    - данные вставляются пакетами (batch insert), используется транзакция
	// 	 *    - при дублировании primaryKey — строка обновляется
	// 	 * 5. Возвращается результат работы:
	// 	 *    {
	// 	 *      inserted: int,
	// 	 *      updated: int,
	// 	 *      skipped: int,
	// 	 *      errors: [
	// 	 *        {
	// 	 *          source: string,
	// 	 *          row: int,
	// 	 *          reason: string
	// 	 *        }, ...
	// 	 *      ]
	// 	 *    }
	// 	 *
	// 	 * Возможные расширения:
	// 	 * - Поддержка Google Sheets API
	// 	 * - Логгирование в файл или БД
	// 	 * - Настраиваемые преобразования данных
	// 	 */




	// 	if ( ! self::isDbInstanceValid() ) {
	// 		error_log( "FeedManagerUtils: БД подключение недоступно" );
	// 		return [];
	// 	}

	// 	$config = json_decode( $jsonConfig, true );
	// 	if ( ! $config || ! isset( $config['sources'] ) || ! is_array( $config['sources'] ) ) {
	// 		error_log( "FeedManagerUtils: Невалидный JSON-конфиг" );
	// 		return [];
	// 	}

	// 	$primaryKey = $config['primary_key'] ?? null;
	// 	if ( ! $primaryKey ) {
	// 		error_log( "FeedManagerUtils: Не задан primary_key" );
	// 		return [];
	// 	}

	// 	$updateOnDuplicate = $config['update_on_duplicate'] ?? false;
	// 	$allColumns = [];

	// 	// Собираем все уникальные колонки с типами по умолчанию
	// 	foreach ( $config['sources'] as $source ) {
	// 		foreach ( $source['columns'] as $col ) {
	// 			$allColumns[ $col ] = 'TEXT';
	// 		}
	// 	}

	// 	// Пытаемся определить типы колонок на основе первых 100 строк
	// 	foreach ( $config['sources'] as $source ) {
	// 		$handle = fopen( $source['filePath'], 'r' );
	// 		if ( ! $handle )
	// 			continue;
	// 		$header = fgetcsv( $handle );
	// 		$sampleCount = 0;
	// 		$columnTypes = [];
	// 		while ( ( $row = fgetcsv( $handle ) ) && $sampleCount++ < 100 ) {
	// 			foreach ( $header as $i => $col ) {
	// 				if ( ! isset( $row[ $i ] ) )
	// 					continue;
	// 				$columnTypes[ $col ][] = $row[ $i ];
	// 			}
	// 		}
	// 		fclose( $handle );

	// 		foreach ( $columnTypes as $col => $values ) {
	// 			if ( isset( $allColumns[ $col ] ) ) {
	// 				$allColumns[ $col ] = self::detectColumnType( $values );
	// 			}
	// 		}
	// 	}

	// 	// Проверка существования и создание таблицы, если нужно
	// 	if ( ! self::tableExists( $tableName ) ) {
	// 		try {
	// 			$sql = self::generateCreateTableSQL( $tableName, $allColumns, $primaryKey );
	// 			self::$db->query( $sql );
	// 		} catch (\Exception $e) {
	// 			error_log( "FeedManagerUtils: Ошибка создания таблицы '$tableName': " . $e->getMessage() );
	// 			return [];
	// 		}
	// 	}

	// 	// Импорт данных
	// 	$inserted = $updated = $skipped = 0;
	// 	$errors = [];

	// 	foreach ( $config['sources'] as $source ) {
	// 		$handle = fopen( $source['filePath'], 'r' );
	// 		if ( ! $handle )
	// 			continue;
	// 		$header = fgetcsv( $handle );
	// 		$headerMap = array_flip( $header );
	// 		$rowIndex = 1;
	// 		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
	// 			$rowIndex++;
	// 			$entry = [];
	// 			foreach ( $allColumns as $col => $_ ) {
	// 				$entry[ $col ] = isset( $headerMap[ $col ] ) ? ( $row[ $headerMap[ $col ] ] ?? null ) : null;
	// 			}
	// 			if ( ! isset( $entry[ $primaryKey ] ) || $entry[ $primaryKey ] === '' ) {
	// 				$errors[] = [ 'row' => $rowIndex, 'reason' => 'Missing primary key' ];
	// 				continue;
	// 			}

	// 			$query = self::$db->placehold( "SELECT COUNT(*) FROM `$tableName` WHERE `$primaryKey`=? LIMIT 1", $entry[ $primaryKey ] );
	// 			self::$db->query( $query );
	// 			$exists = self::$db->result( 'COUNT(*)' );

	// 			if ( $exists && $updateOnDuplicate ) {
	// 				$updateCols = [];
	// 				$updateValues = [];
	// 				foreach ( $entry as $col => $val ) {
	// 					if ( $col !== $primaryKey ) {
	// 						$updateCols[] = "`$col`=?";
	// 						$updateValues[] = $val;
	// 					}
	// 				}
	// 				$updateValues[] = $entry[ $primaryKey ];
	// 				$sql = self::$db->placehold( "UPDATE `$tableName` SET " . implode( ", ", $updateCols ) . " WHERE `$primaryKey`=?", ...$updateValues );
	// 				self::$db->query( $sql );
	// 				$updated++;
	// 			} elseif ( ! $exists ) {
	// 				$sql = self::$db->placehold(
	// 					"INSERT INTO `$tableName` (`" . implode( '`,`', array_keys( $entry ) ) . "`) VALUES (" . rtrim( str_repeat( "?,", count( $entry ) ), "," ) . ")",
	// 					...array_values( $entry )
	// 				);
	// 				self::$db->query( $sql );
	// 				$inserted++;
	// 			} else {
	// 				$skipped++;
	// 			}
	// 		}
	// 		fclose( $handle );
	// 	}

	// 	return [ 
	// 		'inserted' => $inserted,
	// 		'updated' => $updated,
	// 		'skipped' => $skipped,
	// 		'errors' => $errors,
	// 	];
	// }

	// private static function detectColumnType( array $values ): string {
	// 	foreach ( $values as $v ) {
	// 		if ( is_numeric( $v ) ) {
	// 			if ( preg_match( '/^\d+$/', $v ) )
	// 				return 'INT';
	// 			else
	// 				return 'DECIMAL(10,2)';
	// 		}
	// 		if ( preg_match( '/^\d{4}-\d{2}-\d{2}/', $v ) )
	// 			return 'DATE';
	// 	}
	// 	return 'TEXT';
	// }

	// private static function generateCreateTableSQL( string $tableName, array $columns, string $primaryKey ): string {
	// 	$colsSql = [];
	// 	foreach ( $columns as $col => $type ) {
	// 		$colsSql[] = "`$col` $type" . ( $col === $primaryKey ? " PRIMARY KEY" : "" );
	// 	}
	// 	return "CREATE TABLE `$tableName` (" . implode( ", ", $colsSql ) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	// }

	// private static function tableExists( string $tableName ): bool {
	// 	try {
	// 		$query = self::$db->placehold( "SHOW TABLES LIKE ?", self::$db->escape( $tableName ) );
	// 		self::$db->query( $query );
	// 		return self::$db->result() ? true : false;
	// 	} catch (\Exception $e) {
	// 		error_log( "FeedManagerUtils: Table check exception for '$tableName': " . $e->getMessage() );
	// 		return false;
	// 	}
	// }

	// private static function isDbInstanceValid(): bool {
	// 	return isset( self::$db ) && is_object( self::$db );
	// }

	// private static $db; // должен быть установлен при инициализации класса извне
}




//===============================================================

//!!
// echo '<pre>orders = <br>';
// print_r( $orders );
// print_r(self::get_obj( $orders,5 ));
// echo "</pre>";
// exit;

// error_log( "Настройки акции '" . $promotion_config['name'] . "' не корректны" );

// $str_order = print_r( $order, true );
// error_log( '$str_order =  ' . $str_order );

// Логирование вызова метода
// error_log( "Called update_is_applied(\$is_applied = {$is_applied}, \$order_id = {$order_id})" );

// // Логирование трассировки
// $trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
// foreach ( $trace as $index => $frame ) {
// 	$function = isset( $frame['function'] ) ? $frame['function'] : 'unknown';
// 	$class = isset( $frame['class'] ) ? $frame['class'] . $frame['type'] : '';
// 	$file = isset( $frame['file'] ) ? $frame['file'] : 'n/a';
// 	$line = isset( $frame['line'] ) ? $frame['line'] : 'n/a';
// 	error_log( "#$index {$class}{$function}() called at [$file:$line]" );
// }