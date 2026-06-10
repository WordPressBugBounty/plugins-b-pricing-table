<?php

if (! defined('ABSPATH')) {
    exit;
}

/** @var array $attributes */

$id = wp_unique_id('bptbPricingTables-');

$bptb_className = isset($attributes['className']) ? $attributes['className'] : 'is-style-basic';

$bptb_isStandard = false !== strpos($bptb_className, 'standard');
$bptb_isUltimate = false !== strpos($bptb_className, 'ultimate');
 
$bptb_basicClass = !$bptb_isStandard && !$bptb_isUltimate ? ' is-style-basic' : '';

?>
<div
    <?php echo get_block_wrapper_attributes(['class' => "$bptb_basicClass"]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
    id='<?php echo esc_attr($id); ?>'
    data-attributes='<?php echo esc_attr(wp_json_encode($attributes)); ?>'
    data-pipecheck='<?php echo esc_attr(bptbIsPremium()); ?>'></div> 