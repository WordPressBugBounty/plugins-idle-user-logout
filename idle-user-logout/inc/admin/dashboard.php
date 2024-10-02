<?php
class IUL_DASHBOARD{
	function __construct(){
		add_action( 'wp_dashboard_setup', array($this,'initialize_iul_dashboard') );
	}

	function initialize_iul_dashboard() {
		wp_add_dashboard_widget(
			'iul_dashboard_widget',         // Widget slug.
			'<span class="dashicons dashicons-admin-users"></span> Idle User Logout Stats',         // Title.
			array($this,'show_iul_dashboard') // Display function.
		);	
	}

	function show_iul_dashboard() {
		$iul_behavior =  get_option('iul_behavior');
		$iul_data =  get_option('iul_data');
		$default_time =  $iul_data['iul_idleTimeDuration'];

		if(empty($iul_behavior)){
			echo '<p>'.__('No behavior has been defined yet','iul').'</p>';
		}else{ ?>
			<div class="wrap">
				<ul>
					<?php foreach($iul_behavior as $role => $behavior): ?>
						<?php
						if($behavior['idle_action'] == 1){
							echo '<li><span class="dashicons dashicons-arrow-right"></span> '.__('Bypass logout for ','iul').'<strong>'.$role.'</strong></li>';
						}else if($behavior['idle_action'] == 2){
							echo '<li><span class="dashicons dashicons-arrow-right"></span> '.__('Logout and redirect to Login page for ','iul').'<strong>'.$role.'</strong></li>';
						}else{	
							$page = get_post($behavior['idle_page']);
							$action_word = $this->get_action_by_opt($behavior['idle_action']);
							$action = str_replace('#pagename',$page->post_title, $action_word );
						echo '<li><span class="dashicons dashicons-arrow-right"></span> '.$action.' for <strong>'.$role.'</strong> after '.(empty($behavior['idle_timer'])?$default_time:$behavior['idle_timer']).' seconds</li>';
					} ?>
					<?php endforeach; ?>
				</ul>
			</div>
	<?php }
	}


	function get_action_by_opt($num){
		$word = '';
		switch($num){
			case 1:
				$word = 'By pass logout';
			break;

			case 2:
				$word = 'Logout user and redirect to login page';
			break;

			case 3:
				$word = 'Logout user and redirect to #pagename page';
			break;

			case 4:
				$word = 'Do not logout but show #pagename# page in popup';
			break;

			case 5:
				$word = 'Do not logout but redirect to #pagename# page';
			break;

			default:
				$word = '';
		}

		return $word;
	}

}