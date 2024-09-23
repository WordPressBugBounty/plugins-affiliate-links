<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die();
}
include_once AFFILIATE_LINKS_PRO_PLUGIN_DIR . '/' . 'class-affiliate-links-pro-base.php';

class Affiliate_Links_Pro_Import_Export extends Affiliate_Links_Pro_Base {

    public $template = 'link-express';
    public $messages = array();

    public function __construct() {
        parent::__construct();
        add_action( 'init', array( $this, 'maybe_export' ) );
        add_action( 'init', array( $this, 'maybe_import' ) );
        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'add_menu' ) );
        }
    }

    public function add_menu() {
        add_submenu_page(
        'edit.php?post_type=affiliate-links', __( 'Import Export XML or CSV', 'affiliate-links' ), __( 'Import Export', 'affiliate-links' ), 'manage_options', 'express', array( $this, 'controller' )
        );
    }

    public function controller() {
        $this->render_view( $this->template );
    }

    public function maybe_import() {
        if ( isset( $_POST[ 'file_nonce' ] ) && isset( $_FILES[ 'file' ] ) ) {
            $this->import();
        }
    }
    public function maybe_export() {
        if ( isset( $_POST[ 'export_nonce' ] ) ) {
            $this->export();
        }
    }
    
    public function import() {
        $total = 0;
        $have  = 0;
        $ext   = pathinfo( $_FILES[ 'file' ][ 'name' ], PATHINFO_EXTENSION );
        if ( $ext !== 'xml' && $ext !== 'csv' ) {
            $this->messages[ 'message' ] = sprintf( __( "This type file (.%s) not supported. Download only XML or CSV file", 'affiliate-links' ), $ext );
        } else {
            switch ( $ext ) {
                case 'csv' :
                    $result_importing = $this->import_from_csv( $total, $have );
                    break;
                case 'xml' :
                    $result_importing = $this->import_from_xml( $total, $have );
                    break;
            }
            $this->messages[ 'success' ] = sprintf( __( "Add new Links - %s , updated - %s (they were already on the site. Their metadata has been updated from file.)", 'affiliate-links' ), $total, $have );
        }
    }

    public function import_from_xml( &$total, &$have ) {
        $file   = file_get_contents( $_FILES[ 'file' ][ 'tmp_name' ] );
        $links = new SimpleXMLElement($file);
        $links_json = json_encode($links);
        $links = json_decode($links_json,TRUE);
        foreach ( $links['links'] as $link ) {
            if ( $link['target'] ) {
                $exists_links = $this->getPostBySlug( $link['name'] );
                if ( ! $exists_links ) {
                    $post_data = array(
                        'post_title'    => $link['title'],
                        'post_content'  => '',
                        'post_status'   => 'publish',
                        'post_author'   => 1,
                        'post_category' => array(),
                        'post_type'     => 'affiliate-links',
                        'post_name'     => (isset( $link['name'] ) && ! empty( $link['name'] )) ? $link['name'] : $link['title']
                    );
                    $id        = wp_insert_post( $post_data );
                    if ( $id ) {
                        $this->update_post_data_from_xml( $link, $id );
                        ++ $total;
                        if( isset( $link['categories']['category_name'] ) ) {
                            $this->import_categories( $link['categories']['category_name'], $id );
                        }
                    }
                } else {
                    if( isset( $link['categories']['category_name'] ) ) {
                        $this->update_post_data_from_xml( $link, $exists_links );
                        $this->import_categories( $link['categories']['category_name'], $exists_links );
                    }
                    ++ $have;
                }
            }
        }
    }

    public function import_from_csv( &$total, &$have ) {
        if ( ($handle = fopen( $_FILES[ 'file' ][ 'tmp_name' ], 'r' )) !== FALSE ) {
            $row_number = 0;
            while ( ($link_data  = fgetcsv( $handle, 1024, ',' )) !== FALSE ) {
                if ( $row_number > 0 && $link_data[ 0 ] ) {
                    $exists_links = $this->getPostBySlug( $link_data[ 6 ] );
                    if ( ! $exists_links ) {
                        $post_data = array(
                            'post_title'    => $link_data[ 5 ],
                            'post_content'  => '',
                            'post_status'   => 'publish',
                            'post_author'   => 1,
                            'post_category' => array(),
                            'post_type'     => 'affiliate-links',
                            'post_name'     => (isset( $link_data[ 6 ] ) && $link_data[ 6 ]) ? $link_data[ 6 ] : $link_data[ 5 ]
                        );
                        $id        = wp_insert_post( $post_data );
                        if ( $id ) {
                            $this->update_post_data_from_csv( $link_data, $id );
                            ++ $total;
                            $this->import_categories( explode( ',', $link_data[ 7 ] ), $id );
                        }
                    } else {
                        $this->update_post_data_from_csv( $link_data, $exists_links );
                        $this->import_categories( explode( ',', $link_data[ 7 ] ), $exists_links );
                        ++ $have;
                    }
                }
                $row_number ++;
            }
            fclose( $handle );
        }
    }

    public function export() {
        $links       = $this->get_links();
        $export_type = $_POST[ 'export_file_type' ];
        switch ( $export_type ) {
            case 'csv' :
                $this->export_to_csv( $links );
                break;
            case 'xml' :
                $this->export_to_xml( $links );
                break;
        }
    }

    public function export_to_xml( $links = array() ) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<affilate>';
        foreach ( $links as $link ) {
            $target_url    = get_post_meta( $link->ID, '_affiliate_links_target', TRUE );
            $redirect_type = get_post_meta( $link->ID, '_affiliate_links_redirect', TRUE );
            $adu           = get_post_meta( $link->ID, '_affiliate_links_additional_target_url', TRUE );
            $nofollow      = get_post_meta( $link->ID, '_affiliate_links_nofollow', TRUE );
            $iframe        = get_post_meta( $link->ID, '_affiliate_links_iframe', TRUE );
            $description   = get_post_meta( $link->ID, '_affiliate_links_description', TRUE );
            $link_categories_names = $this->get_categories($link->ID);
            $embedded_add_rel          = get_post_meta( $link->ID, '_embedded_add_rel', TRUE );
            $embedded_add_target      = get_post_meta( $link->ID, '_embedded_add_target', TRUE );
            $embedded_add_link_title  = get_post_meta( $link->ID, '_embedded_add_link_title', TRUE );
            $embedded_add_link_class  = get_post_meta( $link->ID, '_embedded_add_link_class', TRUE );
            $embedded_add_link_anchor = get_post_meta( $link->ID, '_embedded_add_link_anchor', TRUE );
            $xml .= "<links>";
            $xml .= "<target>" . $target_url . "</target>";
            $xml .= "<description>" . $description . "</description>";
            $xml .= "<iframe>" . $iframe . "</iframe>";
            $xml .= "<nofollow>" . $nofollow . "</nofollow>";
            $xml .= "<type>" . $redirect_type . "</type>";
            $xml .= "<title>" . $link->post_title . "</title>";
            $xml .= "<name>" . $link->post_name . "</name>";
            $xml .= "<categories>";
            foreach ( $link_categories_names as $link_category_name ) {
                $xml .= "<category_name>" . $link_category_name . "</category_name>";
            }
            $xml .= "</categories>";
            $xml .= '<embedded_add_rel>'.$embedded_add_rel.'</embedded_add_rel>';
            $xml .= '<embedded_add_target>'.$embedded_add_target.'</embedded_add_target>';
            $xml .= '<embedded_add_link_title>'.$embedded_add_link_title.'</embedded_add_link_title>';
            $xml .= '<embedded_add_link_class>'.$embedded_add_link_class.'</embedded_add_link_class>';
            $xml .= '<embedded_add_link_anchor>'.$embedded_add_link_anchor.'</embedded_add_link_anchor>';
            $xml .= "<adu>" . $adu . "</adu>";
            $xml .= "</links>";
        }
        $xml .= '</affilate>';
        header( $_SERVER[ "SERVER_PROTOCOL" ] . " 200 OK" );
        header( "Cache-Control: public" ); // needed for internet explorer
        header( "Content-Type: text/xml; charset=utf-8" );
        header( "Content-Disposition: attachment; filename=affilate-" . date( "Y-m-d H:i:s" ) . ".xml" );
        echo $xml;
        die();
    }

    public function export_to_csv( $links = array() ) {
        header( $_SERVER[ "SERVER_PROTOCOL" ] . " 200 OK" );
        header( "Cache-Control: public" ); // needed for internet explorer
        header( 'Content-Type: text/csv' );
        header( "Content-Disposition: attachment; filename=affilate-" . date( "Y-m-d H:i:s" ) . ".csv" );
        $file = fopen( 'php://output', 'w' );
        fputcsv( $file, array( 'Link Target URL', 'Link Description', 'Mask Link', 'Nofollow Link', 'Redirect Type', 'Link title', 'Link name', 'Categories', 'Add rel=`nofollow`', 'Add target=`_blank`', 'Add link title', 'Add link class', 'Add link anchor', 'Additional target URL' ) );
        foreach ( $links as $link ) {
            $link_categories_names = $this->get_categories( $link->ID );
            $csv_row               = array(
                get_post_meta( $link->ID, '_affiliate_links_target', TRUE ),
                get_post_meta( $link->ID, '_affiliate_links_description', TRUE ),
                get_post_meta( $link->ID, '_affiliate_links_iframe', TRUE ),
                get_post_meta( $link->ID, '_affiliate_links_nofollow', TRUE ),
                get_post_meta( $link->ID, '_affiliate_links_redirect', TRUE ),
                $link->post_title,
                $link->post_name,
                implode( ',', $link_categories_names ),
                get_post_meta( $link->ID, '_embedded_add_rel', TRUE ),
                get_post_meta( $link->ID, '_embedded_add_target', TRUE ),
                get_post_meta( $link->ID, '_embedded_add_link_title', TRUE ),
                get_post_meta( $link->ID, '_embedded_add_link_class', TRUE ),
                get_post_meta( $link->ID, '_embedded_add_link_anchor', TRUE ),
                get_post_meta( $link->ID, '_affiliate_links_additional_target_url', TRUE ),
            );
            fputcsv( $file, $csv_row );
        }
        die();
    }

    public function get_links() {
        $args  = array(
            'post_type'      => 'affiliate-links',
            'post_status'    => 'publish',
            'posts_per_page' => - 1,
        );
        $links = new WP_Query( $args );

        return $links->get_posts();
    }

    public function getPostBySlug( $slug ) {
        $args  = array(
            'post_type'      => 'affiliate-links',
            'post_status'    => 'publish',
            'posts_per_page' => - 1,
            'name'     => $slug
        );
        $links = new WP_Query( $args );
        return $links->get_posts();
    }

    public function getPost( $target_url ) {
        $args  = array(
            'post_type'      => 'affiliate-links',
            'post_status'    => 'publish',
            'posts_per_page' => - 1,
            'meta_query'     => array(
                array(
                    'key'   => '_affiliate_links_target',
                    'value' => $target_url
                ),
            )
        );
        $links = new WP_Query( $args );

        return $links->get_posts();
    }

    public function get_categories ($link_id) {
        global $Affiliate_Links;
        return wp_get_post_terms($link_id, $Affiliate_Links::$taxonomy, array( 'fields' => 'names' ));
    }
    
    public function import_categories( $term_names, $links ) {
        if ( ! is_array( $links ) ) {
            $links = array( $links );
	}
        if ( ! is_array( $term_names ) ) {
            $term_names = array( $term_names );
	}
        global $Affiliate_Links;
        foreach ( $links as $link ) {
            if ( is_int( $link ) ) {
                $link_id = $link;
            } elseif ( is_object( $link ) && isset( $link->ID ) ) {
                $link_id = $link->ID;
            }
            $terms_for_set_object = array();
            foreach ( $term_names as $term_name ) {
                $term_name = trim($term_name);
                $term_slug = sanitize_title($term_name);
                $term = get_term_by('slug', $term_slug, $Affiliate_Links::$taxonomy);
                if( ! empty( $term_name ) && ( empty( $term ) || is_wp_error( $term ) ) ) {
                    $term = wp_insert_term($term_name, $Affiliate_Links::$taxonomy);
                    if( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
                        $terms_for_set_object[] = $term['term_id'];
                    }
                } elseif( isset( $term->term_id ) ) {
                    $terms_for_set_object[] = $term->term_id;
                }
            }
            if( ! empty( $terms_for_set_object ) ) {
                wp_set_object_terms($link_id, $terms_for_set_object, $Affiliate_Links::$taxonomy, false);
            }
        }
    }
    
    public function update_post_data_from_csv( $link_data_csv, $posts ) {
        if ( ! is_array( $posts ) ) {
            $posts = (array) $posts;
        }
        foreach ( $posts as $post ) {
            if ( is_int( $post ) ) {
                $post_id = $post;
            } elseif ( is_object( $post ) && isset( $post->ID ) ) {
                $post_id = $post->ID;
                $new_post_data = array();
                $new_post_data['ID'] = $post_id;
                $new_post_data['post_title'] = $link_data_csv[5];
                $new_post_data['post_name'] = $link_data_csv[6];
                wp_update_post( wp_slash($new_post_data) );
            }
            update_post_meta( $post_id, '_affiliate_links_target', $link_data_csv[ 0 ] );
            update_post_meta( $post_id, '_affiliate_links_description', $link_data_csv[ 1 ] );
            update_post_meta( $post_id, '_affiliate_links_iframe', $link_data_csv[ 2 ] );
            update_post_meta( $post_id, '_affiliate_links_nofollow', $link_data_csv[ 3 ] );
            update_post_meta( $post_id, '_affiliate_links_redirect', $link_data_csv[ 4 ] );
            update_post_meta( $post_id, '_embedded_add_rel', $link_data_csv[ 8 ] );
            update_post_meta( $post_id, '_embedded_add_target', $link_data_csv[ 9 ] );
            update_post_meta( $post_id, '_embedded_add_link_title', $link_data_csv[ 10 ] );
            update_post_meta( $post_id, '_embedded_add_link_class', $link_data_csv[ 11 ] );
            update_post_meta( $post_id, '_embedded_add_link_anchor', $link_data_csv[ 12 ] );
            update_post_meta( $post_id, '_affiliate_links_additional_target_url', $link_data_csv[ 13 ] );
        }
    }
    
    public function update_post_data_from_xml( $link_data_xml, $posts ) {
        if ( ! is_array( $posts ) ) {
            $posts = (array) $posts;
        }
        foreach ( $posts as $post ) {
            if ( is_int( $post ) ) {
                $post_id = $post;
            } elseif ( is_object( $post ) && isset( $post->ID ) ) {
                $post_id = $post->ID;
                $new_post_data = array();
                $new_post_data['ID'] = $post_id;
                $new_post_data['post_title'] = $link_data_xml['title'];
                $new_post_data['post_name'] = $link_data_xml['name'];
                wp_update_post( wp_slash($new_post_data) );
            }
            update_post_meta( $post_id, '_affiliate_links_target', $link_data_xml[ 'target' ] );
            update_post_meta( $post_id, '_affiliate_links_redirect', ( ( ! empty( $link_data_xml[ 'type' ] ) ) ? $link_data_xml[ 'type' ] : '' ) );
            update_post_meta( $post_id, '_affiliate_links_additional_target_url', ( ( ! empty( $link_data_xml[ 'adu' ] ) ) ? $link_data_xml[ 'adu' ] : '' ) );
            update_post_meta( $post_id, '_affiliate_links_nofollow', ( ( ! empty( $link_data_xml[ 'nofollow' ] ) ) ? $link_data_xml[ 'nofollow' ] : '' ) );
            update_post_meta( $post_id, '_affiliate_links_iframe', ( ( ! empty( $link_data_xml[ 'iframe' ] ) ) ? $link_data_xml[ 'iframe' ] : '' ) );
            update_post_meta( $post_id, '_affiliate_links_description', ( ( ! empty( $link_data_xml[ 'description' ] ) ) ? $link_data_xml[ 'description' ] : '' ) );
            update_post_meta( $post_id, '_embedded_add_rel', ( ( ! empty( $link_data_xml[ 'embedded_add_rel' ] ) ) ? $link_data_xml[ 'embedded_add_rel' ] : '' ) );
            update_post_meta( $post_id, '_embedded_add_target', ( ( ! empty( $link_data_xml[ 'embedded_add_target' ] ) ) ? $link_data_xml[ 'embedded_add_target' ] : '' ) );
            update_post_meta( $post_id, '_embedded_add_link_title', ( ( ! empty( $link_data_xml[ 'embedded_add_link_title' ] ) ) ? $link_data_xml[ 'embedded_add_link_title' ] : '' ) );
            update_post_meta( $post_id, '_embedded_add_link_class', ( ( ! empty( $link_data_xml[ 'embedded_add_link_class' ] ) ) ? $link_data_xml[ 'embedded_add_link_class' ] : '' ) );
            update_post_meta( $post_id, '_embedded_add_link_anchor', ( ( ! empty( $link_data_xml[ 'embedded_add_link_anchor' ] ) ) ? $link_data_xml[ 'embedded_add_link_anchor' ] : '' ) );
        }
    }

}
