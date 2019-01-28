<?php
/**
 * To edit this file, please copy the contents of this file to one of these locations:
 * - `/wp-content/themes/<your_theme>/partials/content-sermon-filtering.php`
 * - `/wp-content/themes/<your_theme>/template-parts/content-sermon-filtering.php`
 * - `/wp-content/themes/<your_theme>/content-sermon-filtering.php`
 *
 * That will ensure that your changes are not deleted on plugin update.
 *
 * Sometimes, we need to edit this file to add new features or to fix some bugs, and when we do so, we will modify the
 * changelog in this header comment.
 *
 * @package SermonManager\Views\Partials
 *
 * @since   2.13.0 - added
 * @since   2.15.2 - fixed filtering 404 error
 */

global $post;

if ( ! empty( $GLOBALS['wpfc_partial_args'] ) ) {
	extract( $GLOBALS['wpfc_partial_args'] ); // phpcs:ignore
}

foreach (
	array(
		'action',
		'filters',
		'visibility_mapping',
		'args',
	) as $required_variable
) {
	if ( ! isset( $$required_variable ) ) {
		echo '<p><b>Sermon Manager</b>: Partial "<i>' . str_replace( '.php', '', basename( __FILE__ ) ) . '</i>" loaded incorrectly.</p>';

		return;
	}
}

?>
<div id="<?php echo $args['id']; ?>" class="<?php echo $args['classes']; ?>">
	<?php foreach ( $filters as $filter ) : ?>
		<?php if ( isset( $visibility_mapping[ $filter['taxonomy'] ] ) && in_array( $args[ $visibility_mapping[ $filter['taxonomy'] ] ], array(
			'yes',
			'hide',
			1,
			'1',
			true,
		), true ) ) : ?>
			<?php continue; ?>
		<?php endif; ?>

		<?php if ( ( ! empty( $args[ $filter['taxonomy'] ] ) && 'none' !== $args['visibility'] ) || empty( $args[ $filter['taxonomy'] ] ) ) : ?>
			<div class="<?php echo $filter['className']; ?>" style="display: inline-block">
				<form action="<?php echo $args['action']; ?>" method="get">
					<select name="<?php echo $filter['taxonomy']; ?>"
							title="<?php echo $filter['title']; ?>"
							id="<?php echo $filter['taxonomy']; ?>"
							onchange="if(this.options[this.selectedIndex].value !== ''){return this.form.submit()}else{window.location = window.location.href.split('?')[0];}"
							autocomplete="off"
						<?php echo ! empty( $args[ $filter['taxonomy'] ] ) && 'disable' === $args['visibility'] ? 'disabled' : ''; ?>>
						<option value=""><?php echo $filter['title']; ?></option>
						<?php echo wpfc_get_term_dropdown( $filter['taxonomy'], ! empty( $args[ $filter['taxonomy'] ] ) ? $args[ $filter['taxonomy'] ] : '' ); ?>
					</select>
					<?php $series = explode( ',', $args['series_filter'] ); ?>
					<?php if ( isset( $args['series_filter'] ) && '' !== $args['series_filter'] && $series ) : ?>
						<?php if ( $series > 1 ) : ?>
							<?php foreach ( $series as $item ) : ?>
								<input type="hidden" name="wpfc_sermon_series[]"
										value="<?php echo esc_attr( trim( $item ) ); ?>">
							<?php endforeach; ?>
						<?php else : ?>
							<input type="hidden" name="wpfc_sermon_series"
									value="<?php echo esc_attr( $series[0] ); ?>">
						<?php endif; ?>
					<?php endif; ?>
					<?php $service_types = explode( ',', $args['service_type_filter'] ); ?>
					<?php if ( isset( $args['service_type_filter'] ) && '' !== $args['service_type_filter'] && $service_types ) : ?>
						<?php if ( $service_types > 1 ) : ?>
							<?php foreach ( $service_types as $service_type ) : ?>
								<input type="hidden" name="wpfc_service_type[]"
										value="<?php echo esc_attr( trim( $service_type ) ); ?>">
							<?php endforeach; ?>
						<?php else : ?>
							<input type="hidden" name="wpfc_service_type"
									value="<?php echo esc_attr( $service_types[0] ); ?>">
						<?php endif; ?>
					<?php endif; ?>
					<noscript>
						<div><input type="submit" value="Submit"/></div>
					</noscript>
				</form>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
