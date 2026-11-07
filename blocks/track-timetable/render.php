<?php
/**
 * Server-side rendering of the `bm/track-timetable` block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package BernskioldMedia\WP\Event
 */

use BernskioldMedia\WP\Event\Blocks\Track_Timetable\Track_Timetable_Block;

defined( 'ABSPATH' ) || exit;

echo Track_Timetable_Block::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the templates.
