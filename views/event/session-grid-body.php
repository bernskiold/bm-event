<?php

use BernskioldMedia\WP\Event\Data\Session;

$sessions = Session::get_all( ['meta_key' => 'session_start_datetime', 'meta_type' => 'DATETIME']);

$timezone = array_key_exists( 'event_timezone', $_COOKIE ) ? sanitize_text_field( wp_unslash( $_COOKIE['event_timezone'] ) ) : 'Europe/London';
$date_timezone = str_replace( 'UTC', '', $timezone );

if ( $date_timezone === '' ) {
	$date_timezone = 'Europe/London';
}

// Manual offsets from wp_timezone_choice() come as e.g. "UTC+5.5". Turn them into "+05:30".
if ( preg_match( '/^([+-])(\d{1,2})(?:\.(\d+))?$/', $date_timezone, $offset ) ) {
	$date_timezone = sprintf( '%s%02d:%02d', $offset[1], $offset[2], round( (float) ( '0.' . ( $offset[3] ?? '0' ) ) * 60 ) );
}

// The cookie is set by the visitor, so fall back rather than fail on an unknown zone.
try {
	new DateTimeZone( $date_timezone );
} catch ( Exception $e ) {
	$date_timezone = 'Europe/London';
}

foreach($sessions as $session_obj) : $session = new Session( $session_obj->ID ); ?>

	<?php
	$start_date_time = new DateTime($session->get_start_datetime(), new DateTimeZone('Europe/London'));
	$start_date_time->setTimezone(new DateTimeZone($date_timezone));
	$end_time = new DateTime($session->get_end_datetime(), new DateTimeZone('Europe/London'));
	$end_time->setTimezone(new DateTimeZone($date_timezone));
	?>

	<article <?php post_class( 'session-grid-item has-white-background-color' ); ?> id="session-<?php the_ID(); ?>">

		<?php if ( has_post_thumbnail($session_obj->ID) ) : ?>
			<figure class="session-grid-item-figure media-object is-fitted">
				<?php echo get_the_post_thumbnail( $session_obj->ID, 'medium', ['class' => 'media-object-image'] ); ?>
			</figure>
		<?php endif; ?>

		<div class="session-grid-item-time">

			<?php if ( $session->get_start_time_from_datetime() ) : ?>
				<time datetime="<?php echo esc_attr( $session->get_start_date_from_datetime( 'Y-m-d' ) ); ?> <?php echo esc_attr( $session->get_start_time_from_datetime() ); ?>" class="session-grid-item-time-start js-event-start-time">
					<?php echo esc_html( $start_date_time->format('j M H:i') ); ?>
				</time>
			<?php endif; ?>

			<?php if ( $session->get_end_time_from_datetime() ) : ?>
				<span class="session-grid-item-time-separator">-</span>
				<time datetime="<?php echo esc_attr( $session->get_end_date_from_datetime( 'Y-m-d' ) ); ?> <?php echo esc_attr( $session->get_end_time_from_datetime() ); ?>" class="session-grid-item-time-end js-event-end-time">
					<?php echo esc_html( $end_time->format('H:i T') ); ?>
				</time>
			<?php endif; ?>
		</div>
		<div class="session-grid-item-info">

			<h3 class="session-grid-item-title"><?php echo $session->get_title(); ?></h3>

			<?php if ( $session->get_topic() ) : ?>
				<p class="session-grid-item-topic"><?php echo esc_html( $session->get_topic() ); ?></p>
			<?php endif; ?>

			<?php if ( $session->get_short_description() ) : ?>
				<p class="session-grid-item-description">
					<?php echo esc_html( $session->get_short_description() ); ?>
				</p>
			<?php endif; ?>
		</div>


		<?php if( $session->get_start_date_from_datetime( 'U' ) < date('U')) : ?>
			<div class="session-grid-item-actions">
				<a href="<?php echo esc_url( get_the_permalink($session_obj->ID) ); ?>" class="session-grid-item-action">
					<?php esc_html_e( 'Watch Now', 'bm-event' ); ?> ›
				</a>
			</div>
		<?php elseif( get_theme_mod('bm_event_registration_url') ) : ?>
			<div class="session-grid-item-actions">
				<a href="<?php echo esc_url( get_theme_mod('bm_event_registration_url') ); ?>" class="session-grid-item-action">
					<?php esc_html_e( 'Register Now', 'bm-event' ); ?> ›
				</a>
			</div>
		<?php endif; ?>

	</article>

<?php endforeach; ?>
