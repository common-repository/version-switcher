<?php

/**
 * Post Type Ajax Load More
 */
add_action('wp_ajax_irvs_get_all_version', 'irvs_ajax_handler_func');
function irvs_ajax_handler_func() {

	$security = check_ajax_referer('irvs_version_switcher', 'security');
	if (true == $security && !empty($_POST['plugin_slug'])) :

        $slug = sanitize_text_field( $_POST['plugin_slug'] );

        $transient_key = irvs_get_key( $slug );

        $versions = get_transient( $transient_key );

        if ( false === $versions ) {
            IqbalRony\VersionSwitcher\Version::save_version_data( $slug,  $transient_key, true );
            $versions = get_transient( $transient_key );
        }
        
        if( is_array($versions) && !array_key_exists('errors',$versions) ){
            $need_cache_update = \IqbalRony\VersionSwitcher\Version::is_version_exist_in_cache( $versions, str_replace( "-", "_", $slug ) );
            if( false == $need_cache_update ){
                IqbalRony\VersionSwitcher\Version::save_version_data( $slug,  $transient_key, true );
                $versions = get_transient( $transient_key );
                
            }
        }
        
        if( irvs_is_curl_failed($versions) ){
            IqbalRony\VersionSwitcher\Version::save_version_data( $slug,  $transient_key, true );
            $versions = get_transient( $transient_key ); 
        }

        $versions = $versions ? $versions : [];

        if( !empty($versions) ){
            if( !array_key_exists('errors',$versions) ){
                $option_html = "<option value='' >".esc_html__( 'Select Version', 'version-switcher' )."</option>";
                foreach ( $versions as $version ) {
                    $option_html .= "<option value='{$version}'>$version</option>";
                }
                echo $option_html;
            }else{
                $error = __( 'Plugin is not found in WordPress ORG', 'version-switcher' );
                wp_send_json_error( $error );
            }
            
        }else{
            $error = __( 'No Version Found', 'version-switcher' );
            wp_send_json_error( $error );
        }
	endif;
	wp_die();
}
