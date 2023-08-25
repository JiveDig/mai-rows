<?php
/**
 * Plugin Name:       Mai Rows
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mai-rows
 *
 * @package           create-block
 */

add_action( 'init', 'jivedig_mai_columns_block_init' );
/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function jivedig_mai_columns_block_init() {
	register_block_type( __DIR__ . '/build/columns',
		[
			'render_callback' => 'jivedig_mai_do_columns_block',
		]
	);

	register_block_type( __DIR__ . '/build/column',
		[
			'render_callback' => 'jivedig_mai_do_column_block',
		]
	);
}

function jivedig_mai_do_columns_block( $attributes, $content, $block ) {
	if ( is_admin() ) {
		return $content;
	}

	// Get arrangements.
	$arrangements = [
		'xl' => $attributes['columnsXl'],
		'lg' => $attributes['columnsLg'],
		'md' => $attributes['columnsMd'],
		'sm' => $attributes['columnsSm'],
	];

	// Set fallbacks.
	foreach ( $arrangements as $key => $value ) {
		if ( ! $value ) {
			$keys                 = array_keys( $arrangements );
			$shift                = array_shift( $keys );
			$arrangements[ $key ] = $arrangements[ $shift ];
		}
	}

	$i    = 0;
	$tags = new WP_HTML_Tag_Processor( $content );

	while ( $tags->next_tag( [ 'tag_name' => 'div', 'class_name' => 'jivedig-column' ] ) ) {
		$columns = [];
		$flexes  = [];
		$styles  = (string) $tags->get_attribute( 'style' );
		$styles  = explode( ';', $styles );
		$styles  = array_map( 'trim', $styles );
		$styles  = array_filter( $styles );

		foreach ( $arrangements as $key => $values ) {
			$size      = $values ? jivedig_get_index_value_from_array( $i, $values ) : '';
			$columns[] = sprintf( '--columns-%s:%s', $key, jivedig_get_fraction( $size ) ?: 1 );
			$flexes[]  = sprintf( '--flex-%s:%s', $key, jivedig_get_flex( $size ) );
		}

		// Increment.
		$i++;

		// Merge.
		$styles = array_merge( $styles, $columns, $flexes );

		// Set styles attribute.
		$tags->set_attribute( 'style', implode( ';', $styles ) );
	}

	$content = $tags->get_updated_html();

	return sprintf( '<div class="jivedig-columns">%s</div>', $content );
}

function jivedig_mai_do_column_block( $attributes, $content, $block ) {
	if ( is_admin() ) {
		return $content;
	}

	return sprintf( '<div class="jivedig-column">%s</div>', $content );
}

/**
 * Gets flex value from column size.
 *
 * @param string $break Either xs, sm, md, etc.
 * @param string $size
 *
 * @return string
 */
function jivedig_columns_get_flex( $break, $size ) {
	$flex  = '';
	$basis = jivedig_columns_get_flex_basis( $size );

	switch ( $size ) {
		case 'equal':
			$flex .= sprintf( '--flex-%s:1 1 %s;', $break, $basis );
		break;
		case 'auto':
			$flex .= sprintf( '--flex-%s:0 1 %s;', $break, $basis );
		break;
		case 'fill':
			$flex .= sprintf( '--flex-%s:1 0 %s;', $break, $basis );
		break;
		case 'full':
			$flex .= sprintf( '--flex-%s:0 0 %s;', $break, $basis );
		break;
		default:
			$flex .= sprintf( '--flex-%s:0 0 %s;', $break, $basis );
	}

	return $flex;
}

/**
 * Gets flex basis value from column size.
 *
 * Uses: `flex-basis: calc(25% - (var(--column-gap) * 3/4));`
 * This also works: `flex-basis: calc((100% / var(--columns) - ((var(--columns) - 1) / var(--columns) * var(--column-gap))));`
 * but it was easier to use the same formula with fractions. The latter formula is still used for entry columns since we can't
 * change it because it would break backwards compatibility.
 *
 * @param string $size The size from column setting. Either 'auto', 'fill', 'full', a fraction `1/3`.
 *
 * @return string
 */
function jivedig_columns_get_flex_basis( string $size ) {
	static $all = [];

	if ( isset( $all[ $size ] ) ) {
		return $all[ $size ];
	}

	if ( in_array( $size, [ 'equal', 'auto', 'fill', 'full' ] ) ) {
		switch ( $size ) {
			case 'equal':
				$all[ $size ] = '0%';
			break;
			case 'auto':
				$all[ $size ] = 'auto';
			break;
			case 'fill':
				$all[ $size ] = '0';
			break;
			case 'full':
				$all[ $size ] = '100%';
			break;
		}

		return $all[ $size ];
	}

	// Set columns.
	if ( jivedig_is_fraction( $size ) ) {
		$all[ $size ] = sprintf( 'calc(100%% * %s)', $size );
	}
	// Use raw value
	else {
		$all[ $size ] =  $size;
	}

	return $all[ $size ];
}

/**
 * Gets the correct column value from the repeated arrangement array.
 * Alternate, but slower, versions below.
 *
 * // Slow.
 * $array = array_merge(...array_fill( 0, $index, $array ));
 * return $array[ $index ] ?? $default;
 *
 * // Slowest.
 * $array = [];
 * for ( $i = 0; $i < ( $index + 1) / count( $pattern ); $i++ ) {
 * 	$array = array_merge( $array, $pattern );
 * }
 * return $array[ $index ] ?? $default;
 *
 * @access private
 *
 * @param int   $index   The current item index to get the value for.
 * @param array $array   The array to get index value from.
 * @param mixed $default The default value if there is no index.
 *
 * @return mixed
 */
function jivedig_get_index_value_from_array( $index, $array, $default = null ) {
	// If index is already available, return it.
	if ( isset( $array[ $index ] ) ) {
		return $array[ $index ];
	}

	// If only 1 item in array, return the first.
	if ( 1 === count( $array ) ) {
		return reset( $array );
	}

	return $array[ $index % count( $array ) ] ?? $default;
}

function jivedig_get_fraction( $value ) {
	if ( ! $value ) {
		return false;
	}

	if ( in_array( $value, [ 'auto', 'fill', 'full'] ) ) {
		return false;
	}

	if ( jivedig_is_fraction( $value ) ) {
		return $value;
	}

	// If not a fraction, it's a percentage. Convert to fraction and reduce.
	$percentage   = floatval( str_replace( '%', '', $value ) );
	$decimalValue = $percentage / 100;
	$numerator    = intval( round( $decimalValue * 100 ) );
	$denominator  = 100;
	$gcd          = jivedig_get_gcd( $numerator, $denominator );
	$numerator    = $numerator / $gcd;
	$denominator  = $denominator / $gcd;

	return "$numerator/$denominator";
}

function jivedig_is_fraction( $value ) {
	return preg_match( '/^\\d+\\/\\d+$/', $value );
}

function jivedig_get_gcd( $a, $b ) {
	if ( 0 === $b ) {
		return $a;
	}

	return jivedig_get_gcd( $b, $a % $b );
}

function jivedig_get_flex( $size ) {
	if ( ! $size ) {
		return '1';
	}

	switch ( $size ) {
		case 'auto':
			return '0 1 0%';
		case 'fit':
			return '0 1 auto';
		case 'fill':
			return '1 0 0';
	}

	return '0 1 var(--flex-basis)';
}
