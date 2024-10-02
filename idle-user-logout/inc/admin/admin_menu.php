<?php

class IUL_ADMIN {

	private $iul_behavior, $iul_data;

	function __construct(){

		add_action( 'admin_menu', array($this,'iul_plugin_menu') );
		add_action( 'admin_init', array($this,'iul_page_init') );
		
		$this->iul_behavior =  get_option('iul_behavior');
		$this->iul_data 	=  get_option('iul_data');
	}

	function iul_plugin_menu() {

		add_submenu_page( 
	        'options-general.php', 
	        'Idle User Logout',
	        'Idle User Logout',
	        'manage_options',
	        'iul-settings', 
	        array($this,'iul_idleTimeHandle')
	    );
	}

	function iul_idleTimeHandle() { ?>
		<div class="wrap">
			<h2>Idle User Logout Settings</h2>
			<br />
			<?php 
				$active_tab = 'general_settings';
				if( isset( $_GET[ 'tab' ] ) ) {
	                $active_tab = $_GET[ 'tab' ];
	            }
			?>

			<h2 class="nav-tab-wrapper">
	            <a href="?page=iul-settings&tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>
	            <a href="?page=iul-settings&tab=idle_behavior" class="nav-tab <?php echo $active_tab == 'idle_behavior' ? 'nav-tab-active' : ''; ?>">Idle Behavior</a>
	        </h2>

			<form method="post" enctype="multipart/form-data" action="options.php">
		        <?php
		            if( $active_tab == 'idle_behavior' ) {
		            	settings_fields( 'iul-behaviorOpt' );   
		            	do_settings_sections( 'iul-idlebehavior' );
		            }else{
		            	settings_fields( 'iul-optiongroup' );   
		            	do_settings_sections( 'iul-dashboard' );
		            }
		          	submit_button(); 
		        ?>
	        </form>
		</div>
	<?php }

	function iul_page_init(){

		register_setting(
	        'iul-behaviorOpt',
	        'iul_behavior',
	        array($this,'iul_validate_behavior_fields')
	    );

	    add_settings_section(
	        'iul_behavior_section',
	        'Idle Behavior',
	        array($this,'iul_idle_behavior_table'),
	        'iul-idlebehavior'
	    );


		register_setting(
	        'iul-optiongroup',
	        'iul_data',
	        array($this,'iul_validate_fields')
	    );

	    add_settings_section(
	        'iul_general_section',
	        'User Logout General Settings',
	        array($this,'print_section_info'),
	        'iul-dashboard'
	    );

	    add_settings_field(
		    'iul_logout_duration',
		    '<label for="iul_logout_duration">'.__('Auto Logout Duration','iul').'</label>',
		    array($this,'iul_logout_duration'),
		    'iul-dashboard',
		    'iul_general_section' 
		);

		add_settings_field(
		    'iul_disable_admin',
		    '<label for="iul_disable_admin">'.__('Disable in WP Admin','iul').'</label>',
		    array($this,'iul_disable_admin'),
		    'iul-dashboard',
		    'iul_general_section' 
		); 
	}

	function iul_validate_behavior_fields($input){

		$new_input = array();

        if(isset($input['clear_setting'])){

            $iul_clear_settings = $input['clear_setting'];
            unset($input['clear_setting']);
        }

		foreach( $input as $actions){

			if( (!empty($actions['idle_action']) && !empty($actions['idle_page']) ) || ( $actions['idle_action'] == 1 || $actions['idle_action'] == 2 ) ){
				if(!empty($actions['user_role'])){
					if( ($actions['idle_timer'] != 0) && ($actions['idle_timer'] < 10) ){
						add_settings_error('', esc_attr( 'iul_error' ),__('Idle time must be atleast 10 second','iul'), 'error');
						$actions['idle_timer'] = '';
					}

					$new_input[$actions['user_role']] =  array(
						'idle_action' => $actions['idle_action'],
						'idle_page' => $actions['idle_page'],
						'idle_timer' => $actions['idle_timer']
					);
				}
			}
		}

        if(!empty($iul_clear_settings)){

            foreach($iul_clear_settings as $index){
                unset($new_input[$index]);
            }
        }

		return $new_input;
	}

	function iul_validate_fields($input){ return $input; }

	function print_section_info($args){ echo '<hr />'; }

	function iul_logout_duration(){

	    $value = isset( $this->iul_data['iul_idleTimeDuration'] ) ? esc_attr( $this->iul_data['iul_idleTimeDuration']) : '';
	    echo '<input type="text" id="iul_idleTimeDuration" name="iul_data[iul_idleTimeDuration]" value="'.$value.'" maxlength="8" size="5" /> seconds';
	}

	function iul_disable_admin(){

	    $value = isset( $this->iul_data['iul_disable_admin'] ) ? esc_attr( $this->iul_data['iul_disable_admin']) : '';
	    echo '<input type="checkbox" id="iul_disable_admin" name="iul_data[iul_disable_admin]" '.(($value)?"checked":"").' />';
	}

	private function get_set_roles(){

		if($this->iul_behavior):
			foreach ($this->iul_behavior as $role => $iul_behavior):
				$roles[] = $role;
			endforeach;
			return $roles;
		else:
			return '';
		endif;
	}

	private function append_current_role($roles,$current_role){

		$roles_opt = $used_roles = array();
		$temp_roles = get_editable_roles();

		foreach ($temp_roles as $role_name => $role_info){
			$all_roles[$role_name] = $role_info['name'];
		}

		foreach ($roles as $temp){
			$used_roles[$temp] = $temp_roles[$temp]['name'];
		}

		$selected_role[$current_role] = $temp_roles[$current_role]['name'];

		$filtered_roles = $selected_role + array_diff($all_roles, $used_roles);
		

		return $filtered_roles;
	}

	function iul_idle_behavior_table(){ ?>
		<table class="wp-list-table widefat fixed users">
			<thead>
				<tr>
					<th><?php echo __('User Role','iul'); ?></th>
					<th width="280px" ><?php echo __('Behavior','iul'); ?></th>
					<th><?php echo __('Destination','iul'); ?></th>
					<th><?php echo __('Duration (In seconds)','iul'); ?></th>
					<th><?php echo __('Action','iul'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					$roles = $this->get_set_roles();

					if(!$roles) {
						$roles = [];
					}

					if($this->iul_behavior):
						foreach ($this->iul_behavior as $role => $action):
							$all_roles = $this->append_current_role($roles,$role);
				?>
					<tr>
						<td>
							<select name="iul_behavior[<?php echo $i; ?>][user_role]" >
								<?php foreach ( $all_roles as $role_name => $text): ?>
									<option value="<?php echo $role_name; ?>" <?php if($role==$role_name){echo 'selected'; } ?> ><?php echo $text; ?></option>										
								<?php endforeach;  ?>	

							</select>
						</td>
						<td>
							<select name="iul_behavior[<?php echo $i; ?>][idle_action]" >
								<option value="" ><?php echo __('Select Action','iul'); ?></option>
								<option value="1" <?php if($action['idle_action']=="1"){echo 'selected'; } ?> ><?php echo __('By pass logout','iul'); ?></option>
								<option value="2" <?php if($action['idle_action']=="2"){echo 'selected'; } ?> ><?php echo __('Logout and redirect to login page','iul'); ?></option>
								<option value="3" <?php if($action['idle_action']=="3"){echo 'selected'; } ?> ><?php echo __('Logout user and redirect','iul'); ?></option>
								<option value="4" <?php if($action['idle_action']=="4"){echo 'selected'; } ?> ><?php echo __('Do not logout but show page in popup','iul'); ?></option>
								<option value="5" <?php if($action['idle_action']=="5"){echo 'selected'; } ?> ><?php echo __('Do not logout but redirect to page','iul'); ?></option>
							</select>
						</td>
						<td>
							<select name="iul_behavior[<?php echo $i; ?>][idle_page]" >
								<option value=""><?php echo __('Select Page','iul'); ?></option>
								<?php foreach( get_pages() as $page): ?>
									<option value="<?php echo $page->ID; ?>" <?php if($action['idle_page']== $page->ID ){echo 'selected'; } ?> ><?php echo $page->post_title; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<input type="number" name="iul_behavior[<?php echo $i; ?>][idle_timer]" value="<?php if(isset($action['idle_timer'])){echo $action['idle_timer']; } ?>" placeholder="<?php echo __('Leave blank to use default value','iul'); ?>" title="<?php echo __('Leave blank to use default value','iul'); ?>" />
						</td>
						<td>
							<div><label for="clear-setting" ><input id="clear-setting" type="checkbox" name="iul_behavior[clear_setting][]" value="<?php echo $role;  ?>" /> <span><?php echo __('Clear Settings','iul'); ?></span></label></div>
						</td>
					</tr>

				<?php 
							$i++;
						endforeach;
					endif; 
				?>
				<?php if(count($roles) < count(get_editable_roles()) ){ ?>
					<tr>
						<td>
							<select name="iul_behavior[<?php echo $i ?>][user_role]" >
								<option value=""><?php echo __('Select Role','iul'); ?></option>
								<?php 
								foreach (get_editable_roles() as $role_name => $role_info):
									if(!empty($roles)){
										if( !in_array($role_name,$roles) ){ ?>
											<option value="<?php echo $role_name; ?>" ><?php echo $role_info['name']; ?></option>
								<?php
										}
									}else{ ?>
										<option value="<?php echo $role_name; ?>" ><?php echo $role_info['name']; ?></option>
								<?php }
								endforeach; ?>
							</select>
						</td>
						<td>
							<select name="iul_behavior[<?php echo $i ?>][idle_action]" >
								<option value=""><?php echo __('Select Action','iul'); ?></option>
									<option value="1"><?php echo __('By pass logout','iul'); ?></option>
									<option value="2"><?php echo __('Logout and redirect to login page','iul'); ?></option>
									<option value="3" ><?php echo __('Logout and redirect','iul'); ?></option>
									<option value="4"><?php echo __('Do not logout but show page in popup','iul'); ?></option>
									<option value="5"><?php echo __('Do not logout but redirect to page','iul'); ?></option>
							</select>
						</td>
						<td>
							<select name="iul_behavior[<?php echo $i ?>][idle_page]" >
								<option value=""><?php echo __('Select Page','iul'); ?></option>
								<?php foreach( get_pages() as $page): ?>
									<option value="<?php echo $page->ID; ?>" ><?php echo $page->post_title; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<input type="number" name="iul_behavior[<?php echo $i; ?>][idle_timer]" placeholder="<?php echo __('Leave blank to use default value','iul'); ?>" title="<?php echo __('Leave blank to use default value','iul'); ?>" />
						</td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php		
	}
}