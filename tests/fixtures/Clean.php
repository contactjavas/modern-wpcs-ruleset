<?php
/**
 * Known-good fixture: the ModernWpcsRuleset must report nothing for this file.
 *
 * @package ModernWpcsRuleset
 */

declare( strict_types=1 );

namespace ModernWpcsRuleset\Tests\Fixtures;

/**
 * A small class written in the style the ruleset enforces.
 */
final class Clean {

	/**
	 * Greeting prefix.
	 *
	 * @var string
	 */
	private string $prefix;

	/**
	 * Constructor.
	 *
	 * @param string $prefix Greeting prefix.
	 */
	public function __construct( string $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Build a greeting.
	 *
	 * @param string $name Name to greet.
	 * @return string
	 */
	public function greet( string $name ): string {
		if ( '' === $name ) {
			return $this->prefix;
		}

		return sprintf( '%s, %s', $this->prefix, $name );
	}
}
