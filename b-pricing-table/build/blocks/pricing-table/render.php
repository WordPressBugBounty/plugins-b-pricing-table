<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$id = wp_unique_id( 'bptbPricingTables-' );

// Replace button links that use a disallowed protocol (e.g. javascript:) with "#". Safe links are left untouched.
if ( ! empty( $attributes['pricingTables'] ) && is_array( $attributes['pricingTables'] ) ) {
	foreach ( $attributes['pricingTables'] as $index => $table ) {
		if ( empty( $table['link'] ) || ! is_string( $table['link'] ) ) {
			continue;
		}

		// Read the scheme the way browsers do: ignore surrounding control characters/spaces and inner tabs/newlines.
		$normalizedLink = preg_replace( '/[\t\n\r]/', '', trim( $table['link'], "\x00..\x20" ) );

		if ( preg_match( '/^([a-z][a-z0-9+.\-]*):/i', $normalizedLink, $matches ) && ! in_array( strtolower( $matches[1] ), wp_allowed_protocols(), true ) ) {
			$attributes['pricingTables'][ $index ]['link'] = '#';
		}
	}
}

extract( $attributes );
$className = $className ?? 'is-style-basic';

$isStandard = false !== strpos($className, 'standard');
$isUltimate = false !== strpos($className, 'ultimate');

$basicClass = !$isStandard && !$isUltimate ? ' is-style-basic': '';

?>
<div
    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is properly escaped ?>
    <?php echo get_block_wrapper_attributes( [ 'class' => "$basicClass" ] ); ?>
    id='<?php echo esc_attr( $id ); ?>'
    data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'
></div>
