<?php
defined( 'ABSPATH' ) or die;

/**
 * Used to import data from Sermon Browser
 *
 * @since 2.9
 *
 * @todo  finish it when we do https://trello.com/c/hMczBNM4
 */
class SM_Import_SB {
	/** @var array */
	private $imported_sermons;

	/** @var array */
	private $imported_books;

	/** @var array */
	private $imported_preachers;

	/** @var array */
	private $imported_series;

	/** @var array */
	private $imported_service_types;

	/** @var array */
	private $imported_stuff;

	/** @var array */
	private $imported_tags;

	/**
	 * Checks if Sermon Browser databases exist
	 *
	 * @return bool
	 */
	public static function is_installed() {
		global $wpdb;

		return $wpdb->query( "SELECT id FROM {$wpdb->prefix}sb_sermons LIMIT 1 " ) === 1;
	}

	/**
	 * Do the import
	 */
	public function import() {
		add_action( 'init', array( $this, '_do_import' ) );
	}

	/**
	 * @access private
	 */
	public function _do_import() {
		echo '<div style="margin-left: 170px">';
		do_action( 'sm_import_before_sb' );

		$this->_import_books();
		$this->_import_preachers();
		$this->_import_series();
		$this->_import_service_types();
		$this->_import_stuff();
		$this->_import_sermon_tags();
		$this->_import_sermons();

		do_action( 'sm_import_after_sb' );
		echo '</div>';
	}

	/**
	 * Creates Bible Books
	 */
	private function _import_books() {
		$used_books = $this->_get_used_books();

		foreach ( $used_books as $book ) {
			if ( ! term_exists( $book, 'wpfc_bible_book' ) ) {
				wp_insert_term( $book, 'wpfc_bible_book' );
			}
		}
	}

	/**
	 * Gets the names of all Bible Books that were used in Sermon Browser
	 *
	 * @return array
	 */
	private function _get_used_books() {
		global $wpdb;

		$used_books = array();
		$books      = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_books_sermons" );

		foreach ( $books as $book ) {
			if ( ! in_array( $book->book_name, $used_books ) ) {
				$used_books[] = $book->book_name;
			}
		}

		/**
		 * Allows to filter books that will be imported
		 *
		 * @var array $books list of book names that will be imported
		 */
		return apply_filters( 'sm_import_sb_books', $used_books );
	}

	/**
	 * Imports Preachers
	 */
	private function _import_preachers() {
		global $wpdb;

		$preachers = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_preachers" );

		/**
		 * Allows to filter preachers that will be imported
		 *
		 * @var array $preachers raw database data of preachers
		 */
		$preachers = apply_filters( 'sm_import_sb_preachers', $preachers );

		foreach ( $preachers as $preacher ) {
			if ( ! term_exists( $preacher->name, 'wpfc_preacher' ) ) {
				wp_insert_term( $preacher->name, 'wpfc_preacher', array(
					'desc' => apply_filters( 'sm_import_preacher_description', $preacher->description )
				) );
			}
		}
	}

	private function _import_series() {
	}

	private function _import_service_types() {
	}

	private function _import_stuff() {
	}

	private function _import_sermon_tags() {
	}

	private function _import_sermons() {
		$verse = $this->_get_verse();
	}

	private function _get_verse() {
	}
}