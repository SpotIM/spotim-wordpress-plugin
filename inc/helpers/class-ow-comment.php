<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OW_Comment
 *
 * Comment functions.
 *
 * @since 3.0.0
 * @since 5.0.0 Renamed from 'SpotIM_Comment' to 'OW_Comment'.
 */
class OW_Comment {

    /**
     * Function to sync comments.
     *
     * @param object $events  Fetched comment event details.
     * @param object $users   Commented users details.
     * @param int    $post_id Post ID.
     *
     * @return bool
     */
    public static function sync( $events, $users, $post_id ) {
        $flag = true;

        foreach ( $events as $event ) {
            switch ( $event->type ) {
                case 'c+':
                case 'r+':
                    $flag = self::add_new_comment( $event->message, $users, $post_id );
                    break;
                case 'c~':
                case 'r~':
                    $flag = self::update_comment( $event->message, $users, $post_id );
                    break;
                case 'c-':
                case 'r-':
                    $flag = self::delete_comment( $event->message, $users, $post_id );
                    break;
                case 'c*':
                    $flag = self::soft_delete_comment( $event->message, $users, $post_id );
                    break;
                case 'c@':
                case 'r@':
                    $flag = self::anonymous_comment( $event->message, $users, $post_id );
                    break;
            }

            if ( ! $flag ) {
                break;
            }
        }

        return $flag;
    }

    /**
     * Function to add new comment.
     *
     * @param object $ow_message Comment details.
     * @param object $ow_users   User details.
     * @param int    $post_id    Post ID.
     *
     * @return bool
     */
    private static function add_new_comment( $ow_message, $ow_users, $post_id ) {
        $comment_created = false;

        $message = new OW_Message( 'new', $ow_message, $ow_users, $post_id );

        if ( ! $message->is_comment_exists() ) {
            $comment_id = wp_insert_comment( $message->get_comment_data() );

            if ( $comment_id ) {

                // Generate the ow_id comment meta-data.
                $message->update_comment_meta( $comment_id );

                $comment_created = $message->update_messages_map( $comment_id );
            }
        } else {
            $comment_created = self::update_comment( $ow_message, $ow_users, $post_id );
        }

        return ! ! $comment_created;
    }

    /**
     * Function to update comment.
     *
     * @param object $ow_message Comment details.
     * @param object $ow_users   User details.
     * @param int    $post_id    Post ID.
     *
     * @return bool
     */
    private static function update_comment( $ow_message, $ow_users, $post_id ) {
        $comment_updated = false;

        $message = new OW_Message( 'update', $ow_message, $ow_users, $post_id );

        if ( $message->is_comment_exists() && ! $message->is_same_comment() ) {
            $comment_updated = wp_update_comment( $message->get_comment_data() );
        } else {
            $comment_updated = true;
        }

        return ! ! $comment_updated;
    }

    /**
     * Function to delete comment.
     *
     * @param object $ow_message Comment details.
     * @param object $ow_users   User details.
     * @param int    $post_id    Post ID.
     *
     * @return bool
     */
    private static function delete_comment( $ow_message, $ow_users, $post_id ) {
        $comment_deleted          = false;
        $message_deleted_from_map = false;

        $message = new OW_Message( 'delete', $ow_message, $ow_users, $post_id );
        if ( $message->get_comment_id() ) {
            $messages_ids = $message->get_message_and_children_ids_map();

            foreach ( $messages_ids as $message_id => $comment_id ) {
                $comment_deleted = wp_delete_comment( $comment_id, true );

                if ( $comment_deleted ) {
                    $message_deleted_from_map = $message->delete_from_messages_map( $message_id );

                    if ( ! ! $message_deleted_from_map ) {
                        break;
                    }
                } else {
                    break;
                }
            }
        } else {
            $comment_deleted          = true;
            $message_deleted_from_map = true;
        }

        return ! ! $comment_deleted && ! ! $message_deleted_from_map;
    }

    /**
     * Function to soft delete comment.
     *
     * @param object $ow_message Comment details.
     * @param object $ow_users   User details.
     * @param int    $post_id    Post ID.
     *
     * @return bool
     */
    private static function soft_delete_comment( $ow_message, $ow_users, $post_id ) {
        $comment_soft_deleted = false;

        $message = new OW_Message( 'soft_delete', $ow_message, $ow_users, $post_id );

        if ( $message->is_comment_exists() ) {
            $comment_soft_deleted = wp_update_comment( $message->get_comment_data() );
        }

        return ! ! $comment_soft_deleted;
    }

    /**
     * Function to add anonymous comment.
     *
     * @param object $ow_message Comment details.
     * @param object $ow_users   User details.
     * @param int    $post_id    Post ID.
     *
     * @return bool
     */
    private static function anonymous_comment( $ow_message, $ow_users, $post_id ) {
        $comment_anonymized = false;

        $message = new OW_Message( 'anonymous_comment', $ow_message, $ow_users, $post_id );

        if ( $message->is_comment_exists() && ! $message->is_same_comment() ) {
            $comment_anonymized = wp_update_comment( $message->get_comment_data() );
        } else {
            $comment_anonymized = true;
        }

        return ! ! $comment_anonymized;
    }
}
