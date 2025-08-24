<?php
/**
 * CB Callable Utility
 *
 * Provides safe callable execution for the CB Gallery plugin.
 *
 * @package CB_Gallery
 */

if ( ! class_exists( 'CB_Callable' ) ) {

	class CB_Callable {

		/**
		 * Safely execute a callable with arguments.
		 *
		 * @param callable|string|array $callable A valid PHP callable.
		 * @param array                 $args Arguments to pass.
		 *
		 * @return mixed|null Returns the result of the callable, or null if not callable.
		 */
		public static function exec( $callable, array $args = [] ) {
			if ( is_callable( $callable ) ) {
				try {
					return call_user_func_array( $callable, $args );
				} catch ( \Throwable $e ) {
					error_log( '[CB_Gallery] Callable execution failed: ' . $e->getMessage() );
				}
			} else {
				error_log( '[CB_Gallery] Attempted to execute a non-callable.' );
			}

			return null;
		}

		/**
		 * Check if the given input is a valid callable.
		 *
		 * @param mixed $callable The callable to check.
		 *
		 * @return bool
		 */
		public static function is_valid( $callable ): bool {
			return is_callable( $callable );
		}
	}
}
