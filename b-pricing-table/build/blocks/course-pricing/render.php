<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Thin dynamic wrapper — all rendering happens in view.js (React).
$id = wp_unique_id( 'bptbCoursePricing-' );
?>
<div
    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is properly escaped ?>
    <?php echo get_block_wrapper_attributes(); ?>
    id='<?php echo esc_attr( $id ); ?>'
    data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'
    data-pipecheck='<?php echo esc_attr( function_exists( 'bptbIsPremium' ) ? bptbIsPremium() : true ); ?>'
></div>
