<?php
/**
 * Known-bad fixture: CI asserts that specific sniff codes are reported here.
 *
 * Deliberately has no strict_types declaration.
 *
 * @package ModernWpcsRuleset
 */

namespace ModernWpcsRuleset\Tests\Fixtures;

/**
 * Contains intentional violations.
 */
final class Dirty {

	/**
	 * Uses an unimported class, an undefined variable and a non-strict in_array().
	 *
	 * @param array $haystack Values.
	 * @return bool
	 */
	public function check( array $haystack ): bool {
		Helper::run();
		return in_array( $undefined, $haystack );
	}
}
