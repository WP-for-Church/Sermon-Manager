<?php

/**
 * Important!
 * Sometime in the past, not sure when, SM (or CMB2) developers decided to change (sermon) date format from (mm/dd/YY)
 * to Unix time. This caused some (un)expected bugs, and with this fix, I'm hoping that those bugs will be solved.
 *
 * This fix will select all sermon dates, which are stored as sermon post meta field, called `sermon_date`.
 * Those dates, if not numeric, will be put through `strtotime()` in hope to get them converted to Unix time.
 *
 * For every non Unix sermon date, this function will also create a backup post meta, called `sermon_date_old`.
 * Purpose of this field will be to restore dates if we screw up something. I hope not, but this plugin is a huge pile
 * of spaghetti code.
 *
 * This script will also increase PHP's max execution time, to avoid timeouts.
 *
 * TODO:
 * 1) Add "revert" functionality. That will remove `sermon_date` post meta fields where there is an existing
 * `sermon_date_old`, and it will rename `sermon_date_old` to `sermon_date`. If something goes wrong.
 * 2) Add stop detection, i.e. let's write an index (sermon ID) to a db value. If that key exists, that means that
 * function has been interrupted and it will start converting from that index.
 *
 * @see   https://github.com/WP-for-Church/Sermon-Manager/issues/27
 * @since 2.0.9
 */
class WPFC_Fix_Dates {

	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Main init function. Fills class variables, initializes other functions.
	 *
	 * @return bool False if there were some errors; True otherwise.
	 */
	public function init() {
		$this->attachWP();
		$this->defineActions();

		if ( ! boolval( get_option( 'wpfc_sm_dates_all_fixed', '0' ) ) ) {
			add_action( 'admin_notices', array( self::getInstance(), 'render_warning' ) );
		}

		return true;
	}

	/**
	 * Add actions/filters to WP
	 *
	 * @return void
	 */
	public function attachWP() {
		add_action( 'wpfc_fix_dates', array( self::getInstance(), 'fix' ) );
	}

	/**
	 * Get new instance self or current one if exists
	 *
	 * @return WPFC_Fix_Dates
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Will define actions and corresponding integers:
	 *  SM_DATES_NONE = 0
	 *  SM_DATES_CHECK = 1
	 *  SM_DATES_FIX = 2
	 *  SM_DATES_REVERT = 9
	 *
	 * @return bool True on success
	 * @throws ErrorException Throws if constants are already defined and have a different value. This should never
	 * happen, but better be safe than sorry.
	 */
	public function defineActions() {
		$actions = array(
			'SM_DATES_NONE'   => 0,
			'SM_DATES_CHECK'  => 1,
			'SM_DATES_FIX'    => 2,
			'SM_DATES_REVERT' => 9,
		);

		foreach ( $actions as $action => $value ) {
			if ( defined( $action ) ) {
				if ( constant( $action ) !== $value ) {
					throw new ErrorException( 'Please try to deactivate all plugins except Sermon Manager and try again.', 1 );
				}
			} else {
				define( $action, $value );
			}
		}

		return true;
	}

	/**
	 * Gets last action that has been executed
	 *
	 * @return int|null Action int if success, null on failure
	 * @see defineActions()
	 */
	public function getLastAction() {
		$action = get_option( 'wpfc_sm_dates_last_action', SM_DATES_NONE );

		if ( $action !== SM_DATES_NONE ) {
			if ( ! is_numeric( $action ) ) {
				return $this->getAction( $action );
			}
		}

		return $action;
	}

	/**
	 * Converts textual action to int
	 *
	 * @param string $action The action
	 *
	 * @return int|null Action int if success, null on failure
	 * @see defineActions()
	 */
	public function getAction( $action ) {
		if ( is_numeric( $action ) ) {
			return intval( $action );
		}

		switch ( $action ) {
			case "check":
				return SM_DATES_CHECK;
			case "fix":
				return SM_DATES_FIX;
			case "revert";
				return SM_DATES_REVERT;
		}

		return SM_DATES_NONE;
	}

	/**
	 * Function used to display a message in WP admin area.
	 *
	 * @return void
	 */
	public function render_warning() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><strong>Important!</strong> Sermon Manager needs to check dates of old sermons.
				<a href="<?php echo admin_url( 'edit.php?post_type=wpfc_sermon&page=' . basename( SERMON_MANAGER_PATH ) . '/includes/options.php#sermon-options-dates-fix' ); ?>">Click
					here</a> if you want to do it now.</p>
		</div>
		<?php
	}

	/**
	 * Main fixing function
	 *
	 * @return bool
	 */
	public function fix() {
		$action    = $this->getCurrentAction();
		$old_dates = $this->getDatesStats();

		if ( $action === SM_DATES_NONE && $old_dates['total'] < 0 ) {
			?>
			Click on "<span style="color:#fff">Check dates for errors</span>" to begin...
			<?php
		} else if ( $action === SM_DATES_CHECK ) {
			?>
			Checking for errors...
			<?php
			$dates       = $this->getAllDates( true );
			$dates_stats = $this->getDatesStats();
			if ( $dates_stats['total'] < 1 ) {
				update_option( 'wpfc_sm_dates_total', count( $dates ) );
				update_option( 'wpfc_sm_dates_remaining', count( $dates ) );
				update_option( 'wpfc_sm_dates_fixed', 0 );
				update_option( 'wpfc_sm_dates_checked', 1 );
				update_option( 'wpfc_sm_dates_last_action', SM_DATES_CHECK );
				update_option( 'wpfc_sm_dates_old', serialize( $dates ) );

				if ( count( $dates ) === 0 ) {
					update_option( 'wpfc_sm_dates_all_fixed', 1 );
				}
			}
			?>
			<br>Done. <?php echo count( $dates ) ? 'Please reload the page and click on "Fix All".' : 'No errors.'; ?>
			</span><?php echo wpfc_console_zsh( '', false ); ?>
			<?php
		} else if ( $action === SM_DATES_FIX ) {
			echo 'Starting date fixing...<br>';
			flush();

			$dates = unserialize( get_option( 'wpfc_sm_dates_old', serialize( array() ) ) );
			$fixed = 0;

			if ( ! empty( $dates ) ) {
				echo 'Fixing dates...<br>';
				flush();
				foreach ( $dates as $date ) {
					// for backup
					add_post_meta( $date['post_id'], 'sermon_date_old', $date['date'] );
					// update the date
					update_post_meta( $date['post_id'], 'sermon_date', strtotime( $date['date'] ) );
					// add it to fixed dates
					$fixed ++;
				}

				update_option( 'wpfc_sm_dates_fixed', $fixed );
				update_option( 'wpfc_sm_dates_remaining', intval( get_option( 'wpfc_sm_dates_total', true ) ) - $fixed );
			}

			update_option( 'wpfc_sm_dates_all_fixed', 1 );

			echo 'Date fixing completed.<br>';
			flush();

			echo '</span>';
			echo wpfc_console_zsh( '', false );
		}
	}

	/**
	 * Gets current action that is being executed
	 *
	 * @return int|null Action int if success, null on failure
	 * @see defineActions()
	 */
	public function getCurrentAction() {
		$action = SM_DATES_NONE;

		if ( isset( $_GET['fix_dates'] ) && $_GET['fix_dates'] !== '' ) {
			$action = $_GET['fix_dates'];
		} else if ( isset( $_POST['fix_dates'] ) && $_POST['fix_dates'] !== '' ) {
			$action = $_POST['fix_dates'];
		}

		return $this->getAction( $action );
	}

	/**
	 * Gets some statistics about dates scan
	 *
	 * @return array[] Details about old dates.
	 * @type int $old_dates ['total'] = Total old dates, the number on the first scan
	 * @type int $old_dates ['fixed'] = Number of fixed dates
	 * @type int $old_dates ['remaining'] = How many are left to do.
	 */
	public function getDatesStats() {
		$option_names = array(
			'total'     => 'wpfc_sm_dates_total',
			'fixed'     => 'wpfc_sm_dates_fixed',
			'remaining' => 'wpfc_sm_dates_remaining',
		);

		$stats = array();
		foreach ( $option_names as $name => $option_name ) {
			$stats[ $name ] = get_option( $option_name, - 1 );
		}

		return $stats;
	}

	/**
	 * Get all sermon dates from post meta
	 *
	 * @param bool $filter_old True to get only old dates, false for all (default false)
	 *
	 * @return array Array of post meta IDs, dates and sermon dates
	 */
	public function getAllDates( $filter_old = false ) {
		global $wpdb;
		$wp_query   = new WP_Query( array(
			'post_type'      => 'wpfc_sermon',
			'posts_per_page' => - 1,
			'post_status'    => 'any'
		) );
		$posts_meta = array();

		$sermons = $wp_query->posts;

		foreach ( $sermons as $sermon ) {
			// get post meta directly from DB. The reason for not using get_post_meta() is that we need meta_id too.
			$date = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $sermon->ID, 'sermon_date' ) );

			// if for some reason, the date is blank or something else, continue to next sermon
			if ( empty( $date[0] ) ) {
				continue;
			}

			// assign first sermon_date meta to $date variable
			$date = $date[0];

			// skip if we need only old dates
			if ( is_numeric( $date->meta_value ) && $filter_old ) {
				continue;
			}

			$posts_meta[] = array(
				'date'    => $date->meta_value,
				'meta_id' => $date->meta_id,
				'post_id' => $sermon->ID,
			);
		}

		return $posts_meta;
	}
}

try {
	$WPFC_Fix_Dates = new WPFC_Fix_Dates();
	$WPFC_Fix_Dates->init();
} catch ( Exception $e ) {
	print_r( $e );
}