<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SPOTIM_COMMENT_IMPORT_AGENT', 'Spot.IM/1.0 (Export)' );

/**
 * OW_Message
 *
 * Comment functions.
 *
 * @since 3.0.0
 * @since 5.0.0 Renamed from `SpotIM_Message` to `OW_Message`.
 */
class OW_Message {

    /**
     * Variable to map messages.
     *
     * @var array
     */
    private $messages_map;

    /**
     * Variable to store message data.
     *
     * @var void
     */
    private $message_data;

    /**
     * Variable to store comment data.
     *
     * @var array
     */
    private $comment_data;

    /**
     * Variable to store user details.
     *
     * @var object
     */
    private $users;

    /**
     * Variable to store post id.
     *
     * @var int
     */
    private $post_id;

    /**
     * Constructor.
     *
     * @param string $type    Type of comment.
     * @param object $message Message details.
     * @param object $users   Users details.
     * @param int    $post_id Post ID.
     *
     * @return void
     */
    public function __construct( $type, $message, $users, $post_id ) {
        $this->message = $message;
        $this->users   = count( (array) $users ) ? $users : new stdClass();
        $this->post_id = absint( $post_id );

        $this->messages_map = $this->get_messages_map();

        switch ( $type ) {
            case 'new':
                $this->comment_data = $this->new_comment_data();
                break;
            case 'update':
                $this->comment_data = $this->update_comment_data();
                break;
            case 'delete':
                break;
            case 'soft_delete':
                $this->comment_data = $this->soft_delete_comment_data();
                break;
            case 'anonymous_comment':
                $this->comment_data = $this->anonymous_comment_data();
                break;
        }
    }

    /**
     * Function to check is comment is exist or not.
     *
     * @return bool
     */
    public function is_comment_exists() {
        $comment_exists = false;

        // Query the spotim_id meta-data to check whether the comment already exist
        if ( $this->get_comment_by_ow_id() ) {
            return true;
        }

        if ( ! $this->get_comment_id() ) {
            $comments_args = array(
                'parent'  => absint( $this->comment_data['comment_parent'] ),
                'post_id' => absint( $this->post_id ),
                'status'  => 'approve',
                'user_id' => 0
            );

            $comments = get_comments( $comments_args );

            while ( ! empty( $comments ) ) {
                $comment = array_shift( $comments );

                if ( $comment->comment_author === $this->comment_data['comment_author'] &&
                     $comment->comment_author_email === $this->comment_data['comment_author_email'] &&
                     $comment->comment_content === $this->comment_data['comment_content'] &&
                     $comment->comment_date === $this->comment_data['comment_date'] &&
                     absint( $comment->comment_parent ) === absint( $this->comment_data['comment_parent'] ) ) {

                    $this->update_messages_map( $comment->comment_ID );

                    $comment_exists = true;

                    break;
                }
            }
        } else {
            $comment_exists = true;
        }

        return $comment_exists;
    }

    /**
     * Function to check same comment or not.
     *
     * @return bool
     */
    public function is_same_comment() {
        $same_comment = false;
        $comment_id   = absint( $this->get_comment_id() );

        if ( ! ! $comment_id ) {
            $comment = get_comment( $comment_id, ARRAY_A );

            if ( null !== $comment &&
                 $comment['comment_author'] === $this->comment_data['comment_author'] &&
                 $comment['comment_author_email'] === $this->comment_data['comment_author_email'] &&
                 $comment['comment_content'] === $this->comment_data['comment_content'] ) {
                $same_comment = true;
            }
        }

        return $same_comment;
    }

    /**
     * Function to get comment data.
     *
     * @return array
     */
    public function get_comment_data() {
        return $this->comment_data;
    }

    /**
     * Function to get comment ID.
     *
     * @return int
     */
    public function get_comment_id() {
        $comment_id = 0;

        if ( isset( $this->messages_map[ $this->message->id ] ) ) {
            $comment_id = $this->messages_map[ $this->message->id ]['comment_id'];
        }

        return $comment_id;
    }

    /**
     * Query the comments_meta Table to check if the comment already exists.
     *
     * @return bool
     */
    public function get_comment_by_ow_id() {
        if ( $this->message->id ) {
            // Meta query used to get comment by ow id.
            $args = array(
                'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    array(
                        'key'   => 'spotim_id',
                        'value' => $this->message->id
                    )
                )
            );

            $comments_query = new WP_Comment_Query;
            $comments       = $comments_query->query( $args );

            if ( ! empty( $comments ) ) {
                return $comments[0];
            }
        }

        return false;
    }

    /**
     * Function to update comment meta.
     *
     * @param int $comment_id Comment id to update meta.
     *
     * @return int|bool
     */
    public function update_comment_meta( $comment_id ) {
        return add_comment_meta( $comment_id, 'spotim_id', $this->message->id );
    }

    /**
     * Function to update message map.
     *
     * @param int $comment_id Comment ID.
     *
     * @return int|bool
     */
    public function update_messages_map( $comment_id ) {
        $this->messages_map[ $this->message->id ] = array(
            'comment_id' => $comment_id
        );

        if ( isset( $this->message->comment_id ) ) {
            $this->messages_map[ $this->message->id ]['parent_message_id'] = $this->message->comment_id;
        }

        return update_post_meta( $this->post_id, 'spotim_messages_map', $this->messages_map );
    }

    /**
     * Function to get message and children ids map.
     *
     * @return array
     */
    public function get_message_and_children_ids_map() {
        $messages_map  = [];
        $messages_map[ $this->message->id ] = $this->messages_map[ $this->message->id ]['comment_id'];

        foreach ( $this->messages_map as $message_id => $message ) {
            if ( isset( $message['parent_message_id'] ) &&
                 $this->message->id === $message['parent_message_id'] ) {
                $messages_map[ $message_id ] = $message['comment_id'];
            }
        }

        return $messages_map;
    }

    /**
     * Function to delete message from map array.
     *
     * @param int $message_id Message id to delete message from map array.
     *
     * @return bool
     */
    public function delete_from_messages_map( $message_id ) {
        if ( isset( $this->messages_map[ $message_id ] ) ) {
            unset( $this->messages_map[ $message_id ] );

            return ! ! update_post_meta( $this->post_id, 'spotim_messages_map', $this->messages_map );
        } else {
            return true;
        }
    }

    /**
     * Function to get comment parent ID.
     *
     * @return int
     */
    private function get_comment_parent_id() {
        $comment_parent_id = 0;

        if ( isset( $this->message->comment_id ) ) {
            if ( isset( $this->messages_map[ $this->message->comment_id ] ) ) {
                $comment_parent_id = $this->messages_map[ $this->message->comment_id ]['comment_id'];
            }
        }

        return $comment_parent_id;
    }

    /**
     * Function to get message map.
     *
     * @return array
     */
    private function get_messages_map() {
        $messages_map = get_post_meta( $this->post_id, 'spotim_messages_map', true );

        if ( is_string( $messages_map ) ) {
            $messages_map = array();

            add_post_meta( $this->post_id, 'spotim_messages_map', $messages_map );
        }

        return $messages_map;
    }

    /**
     * Function to get new comment data.
     *
     * @return array
     */
    private function new_comment_data() {
        $author         = $this->get_comment_author();
        $comment_parent = $this->get_comment_parent_id();
        $date           = date( 'Y-m-d H:i:s', absint( $this->message->written_at ) );
        $date_gmt       = get_gmt_from_date( $date );

        return array(
            'comment_agent'        => SPOTIM_COMMENT_IMPORT_AGENT,
            'comment_approved'     => 1,
            'comment_author'       => $author['comment_author'],
            'comment_author_email' => $author['comment_author_email'],
            'comment_author_url'   => '',
            'comment_content'      => wp_kses_post( $this->message->content ),
            'comment_date'         => $date,
            'comment_date_gmt'     => $date_gmt,
            'comment_parent'       => $comment_parent,
            'comment_post_ID'      => absint( $this->post_id ),
            'comment_type'         => 'comment',
            'user_id'              => 0
        );
    }

    /**
     * Function to update comment data.
     *
     * @return array
     */
    private function update_comment_data() {
        $comment_id  = absint( $this->get_comment_id() );
        $old_comment = get_comment( $comment_id, ARRAY_A );
        $new_comment = array(
            'comment_approved' => 1,
            'comment_ID'       => absint( $this->get_comment_id() ),
            'comment_parent'   => absint( $this->get_comment_parent_id() ),
            'comment_post_ID'  => absint( $this->post_id )
        );

        if ( null !== $old_comment ) {
            $new_comment = array_merge( $old_comment, $new_comment );
        }

        if ( ! empty( $this->message->content ) ) {
            $new_comment['comment_content'] = wp_kses_post( $this->message->content );
        }

        return $new_comment;
    }

    /**
     * Function to soft delete comment data.
     *
     * @return array
     */
    private function soft_delete_comment_data() {
        $comment_data = $this->anonymous_comment_data();

        $comment_data['comment_content'] = esc_html__( 'This message was deleted.', 'ow' );

        return $comment_data;
    }

    /**
     * Get anonymous comment data.
     *
     * @return array
     */
    private function anonymous_comment_data() {
        $comment_data = $this->update_comment_data();
        $author       = $this->get_comment_author();

        $comment_data = array_merge( $comment_data, $author );

        return $comment_data;
    }

    /**
     * Function to get comment author.
     *
     * @return array
     */
    private function get_comment_author() {
        $author = array(
            'comment_author'       => esc_html__( 'Guest', 'ow' ),
            'comment_author_email' => ''
        );

        if ( isset( $this->message->user_id ) ) {

            // set author's name
            if ( isset( $this->users->{$this->message->user_id}->nick_name ) &&
                 ! empty ( $this->users->{$this->message->user_id}->nick_name ) ) {
                $author['comment_author'] = sanitize_text_field(
                    $this->users->{$this->message->user_id}->nick_name
                );
            } elseif (
                isset( $this->users->{$this->message->user_id}->display_name ) &&
                ! empty ( $this->users->{$this->message->user_id}->display_name )
            ) {
                $author['comment_author'] = sanitize_text_field(
                    $this->users->{$this->message->user_id}->display_name
                );
            } elseif (
                isset( $this->users->{$this->message->user_id}->user_name ) &&
                ! empty ( $this->users->{$this->message->user_id}->user_name )
            ) {
                $author['comment_author'] = sanitize_text_field(
                    $this->users->{$this->message->user_id}->user_name
                );
            }

            // set author's email
            if (
                isset( $this->users->{$this->message->user_id}->email ) &&
                is_email( $this->users->{$this->message->user_id}->email )
            ) {
                $author['comment_author_email'] = $this->users->{$this->message->user_id}->email;
            }
        }

        return $author;
    }
}
