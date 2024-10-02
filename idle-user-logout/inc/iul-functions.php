<?php

function iul_execute_behavioural_action(){
	$behavior 		=  get_option('iul_behavior');
	$default_data	=  get_option('iul_data');
	
	if( !isset($default_data['iul_disable_admin']) && !is_admin() ){
		$user = wp_get_current_user();
		$roles = $user->roles[0];
		if(!empty($roles) && isset($behavior[$roles])){
			switch ($behavior[$roles]['idle_action']){
				case '5':
					if( isset($behavior[$roles]['idle_page']) ):
						$popup_page = get_post($behavior[$roles]['idle_page']);
						$url = get_permalink($popup_page->ID);
						$url = apply_filters( 'iul_redirect_without_logout', $url );
						wp_redirect($url);
						exit();
					endif;
				break;

				case '4':
					if( isset($behavior[$roles]['idle_page']) ):
						global $output;
						$popup_page = get_post($behavior[$roles]['idle_page']);
						$output = '';
						$content = '';
						$output .='<div class="modal-content">';
						$output	.='<a href="javascript:void(0)" id="close_modal"><span class="dashicons dashicons-no"></span></a>';
						if(has_post_thumbnail( $popup_page->ID )){
							$content .='<div class="featured">';
								$content .=get_the_post_thumbnail($popup_page->ID,'popup-image');
							$content .='</div>';
						}
						$content .='<h3>'.$popup_page->post_title.'</h3>';
						$content .= $popup_page->post_content;
						$output .= apply_filters( 'iul_modal_content', $content );	
						$output .='</div>';
						add_action('wp_footer',function(){ 
							global $output;
							iul_print_modal_script(json_encode($output) );
							unset($output);
						});

					endif;
				break;

				case '3':
					if( isset($behavior[$roles]['idle_page']) ):
						$popup_page = get_post($behavior[$roles]['idle_page']);
						$url = get_permalink($popup_page->ID);
						$url = apply_filters( 'iul_redirect_with_logout', $url );
                        delete_user_meta(get_current_user_id(),'last_active_time');
						wp_clear_auth_cookie();
						wp_redirect($url);
						exit();
					endif;
				break;
				
				case '2':
				default:
                    delete_user_meta(get_current_user_id(),'last_active_time');
					wp_clear_auth_cookie();
					wp_redirect( wp_login_url() );
					exit();
			}
		}
	}
}



function iul_print_modal_script($output){
?>
	<script>
		var content = <?php echo $output; ?>;
		var popup_open = 0;
		jQuery(window).on('load',function(){
			if (content && popup_open==0){
                var modal = UIkit.modal.blockUI(content);
                popup_open = 1;
                jQuery('#close_modal').on('click',function(e){
                  e.preventDefault();
                  modal.hide();
                  popup_open = 0;
                });
              }
		});
	</script>
<?php
}