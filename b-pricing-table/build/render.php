<?php
$id = wp_unique_id( 'bptbPricingTables-' );

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
    data-pipecheck='<?php echo esc_attr( bptbIsPremium() ); ?>'
></div>
