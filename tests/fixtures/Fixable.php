<?php
/**
 * Auto-fixable fixture: CI copies it to a temp path and asserts that phpcbf converges.
 *
 * Contains long-form arrays and spacing issues that conflicting fixers could flip back and forth.
 *
 * @package ModernWpcsRuleset
 */

declare( strict_types=1 );

namespace ModernWpcsRuleset\Tests\Fixtures;

/**
 * Contains intentional, auto-fixable violations.
 */
final class Fixable {

	/**
	 * Returns some values.
	 *
	 * @return array<int|string, mixed>
	 */
	public function values(): array {
		$list   = array(1, 2);
		$nested = array( 'a' => array( 3 ), 'b' => [4] );

		if($list){
			return array_merge( $list, $nested );
		}

		return array();
	}
}
