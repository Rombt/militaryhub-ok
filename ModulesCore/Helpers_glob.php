<?php


class HelpersGlob {
	use Helpers_glob;
}






trait Helpers_glob {


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

				$result = new \stdClass();
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

}