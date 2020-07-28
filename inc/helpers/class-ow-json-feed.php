<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_JSON_Feed
 *
 * JSON feed.
 *
 * @since 4.1.0
 * @since 5.0.0 Renamed from `SpotIM_JSON_Feed` to `OW_JSON_Feed`.
 */
class OW_JSON_Feed {

	/**
	 * Post ID
	 *
	 * @since  4.1.0
	 *
	 * @access private
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Comments
	 *
	 * @since  4.1.0
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $comments = array();

	/**
	 * Conversation
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @var array
	 */
	public $conversation = array();

	/**
	 * Comments IDs
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @var array
	 */
	public $comments_ids = array();

	/**
	 * Tree of child-parent comments
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @var object
	 */
	public $tree;

	/**
	 * Messages
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @var array
	 */
	public $messages = array();

	/**
	 * Users
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @var array
	 */
	public $users = array();

	/**
	 * Constructor
	 *
	 * Get things started.
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @param mixed $post_id The ID of the post.
	 *
	 * @return OW_JSON_Feed SpotIM JSON feed.
	 */
	public function __construct( $post_id ) {
		$this->post_id = $post_id;

		// Load post comments
		$json_feed_query_args = array(
			'status'  => 'approve',
			'post_id' => $post_id,
		);

		/**
		 * Filtering the default comments query args used to generate OW JSON feed.
		 *
		 * @since 4.1.0
		 *
		 * @param array $json_feed_query_args Default feed query args.
		 */
		$json_feed_query_args = apply_filters( 'spotim_json_feed_query_args', $json_feed_query_args );
		$comments_query       = new WP_Comment_Query();
		$comments             = $comments_query->query( $json_feed_query_args );

		// Structure Comments
		foreach ( $comments as $comment ) {
			$this->comments[ $comment->comment_ID ] = $comment;
		}

		// Aggregate Data
		$this->conversation = $this->aggregate_conversation();
		$this->comments_ids = $this->aggregate_comments_ids();
		$this->tree         = $this->aggregate_tree();
		$this->messages     = $this->aggregate_messages();
		$this->users        = $this->aggregate_users();

		/**
		 * Filter the JSON feed
		 *
		 * @since 4.1.0
		 *
		 * @param OW_JSON_Feed $this SpotIM JSON feed.
		 */
		return apply_filters( 'spotim_json_feed', wp_json_encode( $this ) );
	}

	/**
	 * Has Comments
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return bool Whether there are any comments.
	 */
	public function has_comments() {
		return empty( $this->comments );
	}

	/**
	 * Get Comment Count
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return int Comments Count.
	 */
	public function get_comment_count() {
		return count( $this->comments );
	}

	/**
	 * Has Parent Comment
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @param object $comment Comment object.
	 *
	 * @return bool Whether the comments has a parent comment.
	 */
	public static function has_parent_comment( $comment ) {
		return ( 0 === absint( $comment->comment_parent ) );
	}

	/**
	 * Get Top Level Comments
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return array Parrent comments.
	 */
	public function get_top_level_comments() {
		return array_filter( $this->comments, array( $this, 'has_parent_comment' ) );
	}

	/**
	 * Get Children
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @param int $parent_id Parrent comment ID.
	 *
	 * @return array Child comments.
	 */
	public function get_children( $parent_id ) {
		$children = array();
		foreach ( $this->comments as $comment ) {
			if ( $comment->comment_parent === $parent_id ) {
				$children[] = $comment;
			}
		}

		return $children;
	}

	/**
	 * Traverse
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @param int   $comment_id The ID of the comment.
	 * @param array $bank       Comments tree.
	 *
	 * @return void
	 */
	private function traverse( $comment_id, &$bank ) {
		$child_comments = $this->get_children( $comment_id );
		// if no comments under this one, we're ending it here
		if ( ! empty( $child_comments ) ) {
			$bank[ $comment_id ] = wp_list_pluck( $child_comments, 'comment_ID' );
			// recurse down the tree
			foreach ( $child_comments as $comment ) {
				$this->traverse( $comment->comment_ID, $bank );
			}
		}
	}

	/**
	 * Aggregate Conversation
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return array General post data.
	 */
	public function aggregate_conversation() {
		$conversation                     = array();
		$conversation['post_id']          = $this->post_id;
		$conversation['published_at']     = get_the_time( 'U', $this->post_id );
		$conversation['conversation_url'] = get_the_permalink( $this->post_id );

		return $conversation;
	}

	/**
	 * Aggregate Comments IDs
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return array Comments IDs.
	 */
	public function aggregate_comments_ids() {
		$comments_ids = array();
		$comments_ids = array_reverse( array_values( wp_list_pluck( $this->get_top_level_comments(), 'comment_ID' ) ) );

		return $comments_ids;
	}

	/**
	 * Aggregate Tree
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return object Comments tree.
	 */
	public function aggregate_tree() {
		$tree            = array();
		$parent_comments = $this->get_top_level_comments();
		foreach ( $parent_comments as $comment ) {
			$this->traverse( $comment->comment_ID, $tree );
		}

		return (object) $tree;
	}

	/**
	 * Aggregate Messages
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return array Comments list.
	 */
	public function aggregate_messages() {
		$messages = array();
		foreach ( $this->comments as $comment ) {
			$messages[ $comment->comment_ID ]['content']    = apply_filters( 'get_comment_text', $comment->comment_content, $comment, array() );
			$messages[ $comment->comment_ID ]['written_at'] = strtotime( $comment->comment_date_gmt );

			if ( ! trim( $comment->comment_author_email ) ) {
				// Comment without an email
				$messages[ $comment->comment_ID ]['anonymous'] = true;
			} else {
				// Registered User
				$registered_user = get_user_by( 'email', $comment->comment_author_email );
				if ( ! $registered_user ) {
					// Anonymous comment
					$messages[ $comment->comment_ID ]['anonymous']           = true;
					$messages[ $comment->comment_ID ]['anonymous_user_name'] = $comment->comment_author;
				} else {
					$messages[ $comment->comment_ID ]['anonymous'] = false;
					$messages[ $comment->comment_ID ]['user_id']   = $registered_user->ID;
				}
			}
		}

		return $messages;
	}

	/**
	 * Aggregate Users
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return array Comments users.
	 */
	public function aggregate_users() {
		$users = array();
		foreach ( $this->comments as $comment ) {
			if ( ! empty( $comment->comment_author_email ) ) {
				// Registered User
				$registered_user = get_user_by( 'email', $comment->comment_author_email );
				if ( false !== $registered_user ) {
					// User exists - SHOW
					$users[ $registered_user->ID ]['display_name'] = $comment->comment_author;
					$users[ $registered_user->ID ]['user_name']    = $registered_user->user_login;
					$users[ $registered_user->ID ]['image_url']    = esc_url( get_avatar_url( $registered_user->ID ) );
				}
			}
		}

		return $users;
	}

}
