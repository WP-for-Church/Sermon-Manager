<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/*
 *
 *
 * The following functions are designed to be improvements of the above.
 * In the short term they will not replace the existing functions.
 * They are more flexible and are designed to work within a shortcode system.
 *
 *
*/

/*
 * Get the image url. Pass $id and $size attributes
*/
function wpfc_get_sermon_image_url( $id, $size ) {
	if ( empty( $id ) ) {
		$id = '';
	}
	if ( empty( $size ) ) {
		$size = 'sermon_small';
	}
	$image_id        = get_post_thumbnail_id( $id );
	$image_url_array = wp_get_attachment_image_src( $image_id, $size, true );
	$image_url       = $image_url_array[0];
}

function wpfc_get_sermon_image_html( $id, $size ) {

	$image_html = '';

	if ( empty( $id ) ) {
		$id = '';
	}

	if ( empty( $size ) ) {
		$size = 'sermon_medium';
	}

	$image_html = get_the_post_thumbnail( $id, $size, '' );

	return $image_html;
}

function wpfc_get_sermon_title( $id ) {
	if ( empty( $id ) ) {
		$id = '';
	}
	$title = '';
	$title = get_the_title( $id );

	return $title;
}

function wpfc_get_sermon_title_html( $id ) {

	$title = wpfc_get_sermon_title( $id );
	if ( ! empty( $title ) ) {
		$title_html = '<h1 class="sermon-title">';
		$title_html .= $title;
		$title_html .= '</h1>';
	} else {
		$title_html = '';
	}

	return $title_html;

}

function wpfc_get_sermon_description( $id ) {

	if ( empty( $id ) ) {
		$id = '';
	}

	$sermon_description = '';

	$sermon_description = get_post_meta( $id, 'sermon_description', true );

	return $sermon_description;

}

function wpfc_get_sermon_description_html( $id ) {

	$description = wpfc_get_sermon_description( $id );
	if ( empty( $description ) ) {
		$description_html = '';
	} else {
		$description_html = '<p class="content sermon-description">';
		$description_html .= $description;
		$description_html .= '</p>';
	}

	return $description_html;

}

function wpfc_get_sermon_video( $id ) {

	$sermon_video = get_post_meta( $id, 'sermon_video', true );

	if ( empty( $sermon_video ) ) {
		$sermon_video = '';
	}

	return $sermon_video;

}

function wpfc_get_sermon_notes( $id ) {

	$sermon_notes = get_post_meta( $id, 'sermon_notes', true );

	if ( empty( $sermon_notes ) ) {
		$sermon_notes = '';
	}

	return $sermon_notes;

}

function wpfc_get_sermon_audio( $id ) {

	$sermon_audio = get_post_meta( $id, 'sermon_audio', true );

	if ( empty( $sermon_audio ) ) {
		$sermon_audio = '';
	}

	return $sermon_audio;

}

function wpfc_get_sermon_passage( $id ) {

	$sermon_passage = get_post_meta( $id, 'bible_passage', true );

	if ( empty( $sermon_passage ) ) {
		$sermon_passage = '';
	}

	return $sermon_passage;

}

function wpfc_get_sermon_speaker( $id ) {

	$sermon_speaker = get_the_terms( $id, 'wpfc_preacher' );

	if ( empty( $sermon_speaker ) || ! is_array( $sermon_speaker ) ) {
		$sermon_speaker = '';
	} else {
		$sermon_speaker = $sermon_speaker[0]->name;
	}

	return $sermon_speaker;

}

function wpfc_get_sermon_speaker_html( $id ) {

	$speaker = wpfc_get_sermon_speaker( $id );

	$speaker_html = '<span class="preacher_name">';
	$speaker_html .= $speaker;
	$speaker_html .= '</span>';

	return $speaker_html;

}

function wpfc_get_sermon_series( $id ) {

	$sermon_series = get_the_terms( $id, 'wpfc_sermon_series' );

	if ( empty( $sermon_series ) || ! is_array( $sermon_series ) ) {
		$sermon_series = '';
	} else {
		$sermon_series = $sermon_series[0]->name;
	}

	return $sermon_series;

}

function wpfc_get_sermon_series_html( $id ) {

	$series = wpfc_get_sermon_series( $id );

	$series_html = '<span class="sermon_series">';
	$series_html .= __( 'Series: ', 'sermon-manager' );
	$series_html .= $series;
	$series_html .= '</span>';

	return $series_html;

}
