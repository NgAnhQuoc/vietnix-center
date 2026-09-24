<?php

add_filter( 'builder/settings/template/controls_data', function( $data ) {
  $all_user = get_users( array( 'fields' => array( 'ID' ) ) );
  $users = [];
  foreach($all_user as $user){
    $users[ $user->ID ] = get_user_meta( $user->ID, 'nickname', true );
  }

  // Add control to select the user roles for an author archive template type
  $data['controls']['templateConditions']['fields']['archiveAuthorID_User'] = [
    'type'        => 'select',
    'label'       => esc_html__( 'Author', 'vnx' ),
    'options'     => $users,
    'multiple'    => true,
    'placeholder' => esc_html__( 'Select User', 'vnx' ),
    'description' => esc_html__( 'Leave empty to apply template to all users.', 'vnx' ),
    'required'    => [ 'archiveType', '=', 'author' ],
  ];

  return $data;
} );

add_filter( 'bricks/screen_conditions/scores', function( $scores, $condition, $post_id, $preview_type ) {
  if ( is_author() && $condition['main'] === 'archiveType' && isset( $condition['archiveType'] ) && in_array( 'author', $condition['archiveType'] ) && isset( $condition['archiveAuthorID_User'] ) ) { 
    $user = get_queried_object();
    
    if ( ! empty( $user->data ) ) {
      
      
        if ( in_array( $user->data->ID, $condition['archiveAuthorID_User'] ) ) {
          
          if( isset($condition['exclude']))
          {
            $scores[] = 1;
          }
          else{
            $scores[] = 9;
          }
          
        }
    }
    else {
      global $wp_query;
      $wp_query->set_404();
      status_header( 404 );
      // $scores[] = 1;
    }
  }
  return $scores;
}, 10, 4 );

?>