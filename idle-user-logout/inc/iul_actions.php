<?php

	class IUL_ACTIONS{
		private $iul_data, $iul_behavior, $popup_page;
		function __construct(){
			add_action('wp_ajax_logout_idle_user', array($this,'logout_idle_user') );
			add_action('wp_ajax_update_user_time', array($this,'update_user_time') );
			add_action('admin_head', array($this,'start_iul_action') );
			add_action('wp_head', array($this,'start_iul_action') );
			
			$this->popup_page = '';
			$this->iul_behavior =  get_option('iul_behavior');
			$this->iul_data 	=  get_option('iul_data');
		}

		function start_iul_action(){
			global $is_iphone;
			$is_mobile = false;
			if(wp_is_mobile() || $is_iphone ){
				$is_mobile = true;
			}
			if(is_user_logged_in()):	
				$output = '';
				$behavior = $this->iul_behavior;
				$default_data = $this->iul_data;

				$action = array();
				$action['action_type'] = '2';
				$action['timer'] = $default_data['iul_idleTimeDuration'];

				if(is_admin() && isset($default_data['iul_disable_admin'])){
					$action['disable_admin'] = true;
				}
				
				$user = wp_get_current_user();
				$roles = $user->roles[0];
				
				if(!empty($roles) && isset($behavior[$roles])){
					$action['action_type'] = $behavior[$roles]['idle_action'];
					$action['action_value'] = '';
					
					if( ($action['action_type'] == 3 || $action['action_type'] == 4 || $action['action_type'] == 5) && ($behavior[$roles]['idle_page']) ){
						$this->popup_page = get_post($behavior[$roles]['idle_page']);
						$url = get_permalink($this->popup_page->ID);
						if(!is_page($this->popup_page->ID)){
							$action['action_value'] = $url;
						}else{
							$action['action_value'] = '';
						}
					}

					$action['timer'] = empty($behavior[$roles]['idle_timer'])?$default_data['iul_idleTimeDuration']:$behavior[$roles]['idle_timer'];
				}

				if( isset($action['action_type']) && ($action['action_type'] == 4) && ($behavior[$roles]['idle_page']) && $this->popup_page ){
					$content = '';
					$output .='<div class="modal-content">';
					$output	.='<a href="javascript:void(0)" id="close_modal"><span class="dashicons dashicons-no"></span></a>';
					
					if(has_post_thumbnail( $this->popup_page->ID )){
						$content .='<div class="featured">';
							$content .=get_the_post_thumbnail($this->popup_page->ID,'popup-image');
						$content .='</div>';
					}

					$content .='<h3>'.$this->popup_page->post_title.'</h3>';
					$content .=$this->popup_page->post_content;
					
					$output .= apply_filters( 'iul_modal_content', $content );
					
					$output .='</div>';

					$action['modal'] = $output;
				}
				
				$final_action = apply_filters( 'iul_action', $action );
				wp_localize_script( 'iul-script','iul', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'actions' => $final_action,
					'is_mobile'	=> $is_mobile,
					) 
				);
			endif;
		}

		function logout_idle_user(){
			if(is_user_logged_in()){
				do_action( 'uil_before_logout', get_current_user_id() );
			}
            delete_user_meta(get_current_user_id(),'last_active_time');
			wp_logout();
			do_action( 'uil_after_logout' );
			die('true');	
		}

		function update_user_time(){
			$type = $_POST['callType'];
			if($type == 'active' ){
				$active_time = date('H:i:s');
				update_user_meta(get_current_user_id(),'last_active_time',$active_time);
			}else{
				delete_user_meta(get_current_user_id(),'last_active_time');	
			}
		}
	}