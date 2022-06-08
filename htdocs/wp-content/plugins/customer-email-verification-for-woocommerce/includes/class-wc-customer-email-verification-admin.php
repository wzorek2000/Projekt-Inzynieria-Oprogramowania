<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Customer_Email_Verification_Admin {		
	
	public $my_account_id;
	
	/**
	* Initialize the main plugin function
	*/
	public function __construct() {	
		$this->my_account_id = get_option( 'woocommerce_myaccount_page_id' );
	}
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Get the class instance
	 *
	 * @return woo_customer_email_verification_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* Init from parent mail class
	*/
	public function init() {
		add_action( 'wp_ajax_cev_settings_form_update', array( $this, 'cev_settings_form_update_fun') );
		add_filter( 'manage_users_columns', array( $this, 'add_column_users_list' ), 10, 1 );
		add_filter( 'manage_users_custom_column', array( $this, 'add_details_in_custom_users_list' ), 10, 3 );
		add_action( 'show_user_profile', array( $this, 'show_cev_fields_in_single_user' ) );
		add_action( 'edit_user_profile', array( $this, 'show_cev_fields_in_single_user' ) );
		add_action( 'admin_head', array( $this, 'cev_manual_verify_user' ) );      

		/*** Sort and Filter Users ***/
		add_action('restrict_manage_users', array( $this, 'filter_user_by_verified' ));	
		add_filter('pre_get_users', array( $this, 'filter_users_by_user_by_verified_section' ));
		
		/*** Bulk actions for Users ***/
		add_filter( 'bulk_actions-users', array( $this, 'add_custom_bulk_actions_for_user' ) );
		add_filter( 'handle_bulk_actions-users', array( $this, 'users_bulk_action_handler' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'user_bulk_action_notices' ) );
		
		if ( isset( $_GET['page'] ) && 'customer-email-verification-for-woocommerce' == $_GET['page'] ) {
			// Hook for add admin body class in settings page
			add_filter( 'admin_body_class', array( $this, 'cev_post_admin_body_class' ), 100 );
		}
		add_action( 'wp_ajax_cev_manualy_user_verify_in_user_menu', array( $this, 'cev_manualy_user_verify_in_user_menu') );
	}
	
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Customer Verification', 'Email Verification', 'manage_woocommerce', 'customer-email-verification-for-woocommerce', array( $this, 'wc_customer_email_verification_page_callback' ) ); 
	}
	
	/*
	* Add class in body tag
	*/
	public function cev_post_admin_body_class( $body_class ) {
		$body_class .= ' customer-email-verification-for-woocommerce';
		return $body_class;
	}
	
	/**
	* Load admin styles.
	*/
	public function admin_styles( $hook ) {						
		
		if ( !isset( $_GET['page'] ) ) {
			return;
		}
		
		if ( 'customer-email-verification-for-woocommerce' != $_GET['page'] ) {
			return;
		}
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';				

		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'select2');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'customer_email_verification_styles', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/admin.css', array(), woo_customer_email_verification()->version );
				
		wp_enqueue_script( 'customer_email_verification_script', woo_customer_email_verification()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery','wp-util' ), woo_customer_email_verification()->version , true);
		
		wp_localize_script( 'customer_email_verification_script', 'customer_email_verification_script', array() );
		
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		
		
		wp_enqueue_script( 'selectWoo');
		wp_enqueue_script( 'wc-enhanced-select');
		
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		
		
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );		
		wp_enqueue_script( 'jquery-ui-sortable' );		
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');		
		wp_enqueue_style('thickbox');								
	}
	
	/*
	* Callback for Customer Email Verification page
	*/
	public function wc_customer_email_verification_page_callback() { 
		wp_enqueue_script( 'customer_email_verification_table_rows' );	
		?>
		<div class="zorem-layout-cev__header">
			<h1 class="zorem-layout-cev__header-breadcrumbs">
				<span class="header-breadcrumbs-last-cev"> Customer Email Verification </span>
			</h1>
			<div class="woocommerce-layout__activity-panel">
				<div class="woocommerce-layout__activity-panel-tabs">
					<button type="button" id="activity-panel-tab-help" class="components-button woocommerce-layout__activity-panel-tab">
						<span class="dashicons dashicons-editor-help"></span>
						Help 
					</button>
				</div>
				<div class="woocommerce-layout__activity-panel-wrapper">
					<div class="woocommerce-layout__activity-panel-content" id="activity-panel-true">
						<div class="woocommerce-layout__activity-panel-header">
							<div class="woocommerce-layout__inbox-title">
								<p class="css-activity-panel-Text">Documentation</p>            
							</div>								
						</div>
						<div>
							<ul class="woocommerce-list woocommerce-quick-links__list">
								<li class="woocommerce-list__item has-action">
									<?php
									$support_link = class_exists( 'customer_email_verification_pro' ) ? 'https://www.zorem.com/?support=1' : 'https://wordpress.org/support/plugin/customer-email-verification-for-woocommerce/#new-topic-0' ;
									?>
									<a href="<?php echo esc_url( $support_link ); ?>" class="woocommerce-list__item-inner" target="_blank" >
										<div class="woocommerce-list__item-before">
											<span class="dashicons dashicons-media-document"></span>	
										</div>
										<div class="woocommerce-list__item-text">
											<span class="woocommerce-list__item-title">
												<div class="woocommerce-list-Text">Get Support</div>
											</span>
										</div>
										<div class="woocommerce-list__item-after">
											<span class="dashicons dashicons-arrow-right-alt2"></span>
										</div>
									</a>
								</li>            						
								<li class="woocommerce-list__item has-action">
									<a href="https://www.zorem.com/product/customer-verification-for-woocommerce/" class="woocommerce-list__item-inner" target="_blank">
										<div class="woocommerce-list__item-before">
											<span class="dashicons dashicons-media-document"></span>
										</div>
										<div class="woocommerce-list__item-text">
											<span class="woocommerce-list__item-title">
												<div class="woocommerce-list-Text">Upgrade To Pro</div>
											</span>
										</div>
										<div class="woocommerce-list__item-after">
											<span class="dashicons dashicons-arrow-right-alt2"></span>
										</div>
									</a>
								</li>						
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="woocommerce cev_admin_layout">
			<div class="cev_admin_content" >
				<div class="cev_nav_div">	
					<?php 
					$this->get_html_menu_tab( $this->get_cev_tab_settings_data());
					require_once( 'views/admin_options_settings.php' );
					do_action('cev_pro_tools_settings_tab');
					require_once( 'views/cev_addons_tab.php');
					?>
				</div>
			</div>				 
		</div>
		<?php
	}

	public function get_html_menu_tab( $arrays ) {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'email-verification';
		foreach ( (array) $arrays as $id => $array ) {
			if ( isset( $array['type'] ) && 'link' == $array['type'] ) {
				?>
				<a class="menu_cev_link" href="<?php esc_html_e( esc_url( $array['link'] ) ); ?>"><?php esc_html_e( $array['title'] ); ?></a>
				<?php 
			} else {
				?>
				<input class="cev_tab_input" id="<?php echo esc_html( $id ); ?>" name="<?php echo esc_html( $array['name'] ); ?>" type="radio"  data-tab="<?php echo esc_html( $array['data-tab'] ); ?>" data-label="<?php echo esc_html( $array['data-label'] ); ?>" <?php echo ( $tab == $array['data-tab'] ? 'checked' : '' ); ?> />
				<label class="<?php echo esc_html( $array['class'] ); ?>" for="<?php echo esc_html( $id ); ?>"><?php echo esc_html( $array['title'] ); ?></label>
				<?php
			}
		}
	}	
	
	public function get_cev_tab_settings_data() {
		
		$cev_customizer_settings = new cev_initialise_customizer_settings();
			
		$setting_data = array(
			'setting_tab' => array(					
				'title'		=> __( 'Email verification', 'customer-email-verification-for-woocommerce' ),
				'show'      => true,
				'class'     => 'cev_tab_label',
				'data-tab'  => 'email-verification',
				'data-label' => 'Email Verification',
				'name'  => 'tabs',				
			),
			
			'customize' => array(					
				'title'		=> __( 'Customize', 'customer-email-verification-pro' ),
				'type'		=> 'link',
				'link'		=> $cev_customizer_settings->get_customizer_url( 'cev_main_controls_section', 'settings' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'trackship',
				'data-label' => __( 'Customize', 'customer-email-verification-pro' ),
				'name'  => 'tabs',
			),
						
			'Add_tab' => array(					
				'title'		=> __( 'Go Pro', 'customer-email-verification-for-woocommerce' ),
				'show'      => true,
				'class'     => 'cev_tab_label',
				'data-tab'  => 'add-ons',
				'data-label' => __( 'Go Pro', 'customer-email-verification-for-woocommerce' ),
				'name'  => 'tabs',
			),
		);
		
		return $setting_data;
	}
		
	/*
	* get html of fields
	*/	
	public function get_html( $arrays ) {
		
		$checked = '';
		?>
		<table class="form-table box-border ">
			<tbody>
			<?php
			foreach ( (array) $arrays as $id => $array ) {
				if ( $array['show'] ) {
					if ( 'title' == $array['type'] ) {
						?>
						<tr valign="top titlerow">
							<th colspan="2"><h3><?php echo esc_html( $array['title'] ); ?></h3></th>
						</tr>    	
						<?php
						continue;
					}
					if ( 'toggle' == $array['type'] ) {
						?>
						<tr valign="top titlerow">
							<td colspan="2" class="<?php echo esc_html( $array['class'] ); ?>">
								<div class="<?php echo esc_html( $array['class'] ); ?>">
									<?php
									if ( get_option( $id, $array['Default'] ) ) {
										$checked = 'checked';
									} else {
										$checked = '';
									} 							
									?>
									<input type="hidden" name="<?php echo esc_html( $id ); ?>" value="0"/>
									<input class="tgl tgl-flat-cev" id="<?php echo esc_html( $id ); ?>" name="<?php echo esc_html( $id ); ?>" type="checkbox" <?php echo esc_html( $checked ); ?> value="1"/>
									<label class="tgl-btn" for="<?php echo esc_html( $id ); ?>"></label>
									<span class="multiple_label_new"><?php echo esc_html( $array['title'] ); ?>
										<?php if ( isset( $array['tooltip'] ) ) { ?>
										<span class="woocommerce-help-tip tipTip" title="<?php echo esc_html( $array['tooltip'] ); ?>"></span>
										<?php } ?>
									</span>
								</div>	
							</td>
						</tr>
					<?php
					continue;
					}
					if ( isset( $array['type'] ) && 'inline_checkbox' == $array['type'] ) {
									
						$checkbox_array = $array['checkbox_array'];
						?>
						
						<tr valign="top titlerow">
							<td colspan="2" class="<?php echo esc_html( $array['class'] ); ?>">
							<?php
							foreach ( $checkbox_array as $c_name => $c_label ) {
								if ( get_option( $c_name ) ) {
									$checked = 'checked'; 
								} else { 
									$checked = ''; 
								}
								?>
							<p class="inline_checkbox">
								<input type="hidden" name="<?php echo esc_html( $c_name ); ?>" value="0"/>
								<input id="<?php echo esc_html( $c_name ); ?>" class="<?php echo esc_html( isset( $c_label['class'] ) ? $c_label['class'] : '' ); ?>" name="<?php echo esc_html( $c_name ); ?>" type="checkbox" <?php echo esc_html( $checked ); ?> value="1"/>
								<label for="<?php echo esc_html( $c_name ); ?>"><?php echo esc_html( $c_label['title'] ); ?>
									<?php
									if ( isset( $c_label['tooltip'] ) ) {
										if ( class_exists( 'customer_email_verification_pro' ) ) {
											?>
										<span class="woocommerce-help-tip tipTip" title="<?php echo esc_html( $c_label['tooltip'] ); ?>"></span>
									<?php } } ?>
								</label>	
							</p>								
					<?php } continue; } ?>
				<tr valign="top" class="<?php echo esc_html( $array['class'] ); ?>">
				
				<?php if ( 'desc' != $array['type'] ) { ?>										
					<th scope="row" class="titledesc"  >
						<label for="">
							<?php
							echo esc_html( $array['title'] );
							if ( isset( $array['title_link'] ) ) {
								echo esc_html( $array['title_link'] );
							}
							if ( isset( $array['tooltip'] ) ) {
								?>
								<span class="woocommerce-help-tip tipTip" title="<?php echo esc_html( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>
					</th>
					<?php } ?>
					<td class="forminp" <?php echo ( 'desc' == $array['type'] ) ? 'colspan=2' : '' ; ?>>
						<?php
						if ( isset( $array['type'] ) && 'dropdown' == $array['type'] ) {
							if ( isset( $array['multiple'] ) ) {
								$multiple = 'multiple';
								$field_id = $array['multiple'];
							} else {
								$multiple = '';
								$field_id = $id;
							}
							?>
							<fieldset>
								<select class="select select2" id="<?php echo esc_html( $field_id ); ?>" name="<?php echo esc_html( $id ); ?>" <?php echo esc_html( $multiple ); ?>>  
								<?php
								foreach ( (array) $array['options'] as $key => $val ) {
									$selected = '';
									if ( isset( $array['multiple'] ) ) {
										if ( in_array( $key, (array) $this->data->$field_id ) ) {
											$selected = 'selected';
										}
									} else {
										if ( get_option($id) == (string) $key ) {
											$selected = 'selected';
										}
									}
									?>
									<option value="<?php echo esc_html( $key ); ?>" <?php echo esc_html( $selected ); ?> ><?php echo esc_html( $val ); ?></option>
								<?php } ?>
								</select>
							</fieldset>
						<?php
						} elseif ( 'textarea' == $array['type'] ) {
							?>
							<fieldset>
								<textarea placeholder="<?php echo ( !empty( $array['placeholder'] ) ? esc_html( $array['placeholder'] ) : '' ); ?>" class="input-text regular-input cev_height" name="<?php echo esc_html( $id ); ?>" id="<?php echo esc_html( $id ); ?>"><?php echo esc_html( get_option( $id, $array['Default'] ) ); ?></textarea>
							</fieldset>
							<span class="cev_desc_tip"><?php echo esc_html( $array['desc_tip'] ); ?></span>
						<?php
						} else {
							?>
							<fieldset>
								<input class="input-text regular-input " type="text" name="<?php echo esc_html( $id ); ?>" id="<?php echo esc_html( $id ); ?>" style="" value="<?php echo esc_html( get_option( $id, $array['Default'] ) ); ?>" placeholder="<?php echo ( !empty( $array['placeholder'] ) ? esc_html( $array['placeholder'] ) : '' ); ?>">
							</fieldset>
						<?php } ?> 
							</td>
						</tr>
				<?php } } ?>
			</tbody>
		</table>
	<?php 
	}
	
	/*
	* Get html get_html_new of fields
	*/
	
	public function get_html_new( $arrays ) { 
		
		$checked = '';
		?>
		<table class="form-table box-border">
			<tbody>
				<?php
				foreach ( (array) $arrays as $id => $array ) {
				
					if ( $array['show'] ) {				
						?>
				<tr valign="top" class="<?php echo esc_html( $array['class'] ); ?>">
					<?php if ( 'desc' != $array['type'] ) { ?>										
					<th scope="row" class="titledesc">
						<label for=""><?php echo esc_html( $array['title'] ); ?><?php echo ( isset( $array['title_link'] ) ) ? esc_html( $array['title_link'] ) : '' ; ?>
							<?php
							if ( isset( $array['tooltip'] ) ) { 
								if ( class_exists( 'customer_email_verification_pro' ) ) {
									?>
								<span class="woocommerce-help-tip tipTip" title="<?php echo esc_html( $array['tooltip'] ); ?>"></span>
							<?php } } ?>
						</label>
					</th>
					<?php } ?>
					<td class="forminp" <?php echo ( 'desc' == $array['type'] ) ? 'colspan=2' : '' ; ?>>
						<?php
						if ( isset( $array['type'] ) && 'dropdown' == $array['type'] ) {
							
							if ( isset( $array['multiple'] ) ) {
								$multiple = 'multiple';
								$field_id = $array['multiple'];
							} else {
								$multiple = '';
								$field_id = $id;
							}
							?>
							<fieldset>
								<select class="select select2" id="<?php echo esc_html( $field_id ); ?>" name="<?php echo esc_html( $id ); ?>" <?php echo esc_html( $multiple ); ?>>  
									<?php
									foreach ( (array) $array['options'] as $key => $val ) {                                    	
										$selected = '';
										if ( isset( $array['multiple'] ) ) {
											if ( in_array( $key, (array) $this->data->$field_id ) ) {
												$selected = 'selected';
											}
										} else {
											if ( get_option( $id, $array['Default'] ) == (string) $key ) {
												$selected = 'selected';
											}
										}                                        
										?>
										<option value="<?php echo esc_html( $key ); ?>" <?php echo esc_html( $selected ); ?> ><?php echo esc_html( $val ); ?></option>
									<?php } ?>
								</select>
							</fieldset>                          
							<?php
						} elseif ( 'multiple_select' == $array['type'] ) {
							?>
							<div class="multiple_select_container">	
								<select multiple class="wc-enhanced-select" name="<?php esc_html_e( $id ); ?>[]" id="<?php esc_html_e( $id ); ?>">
								<?php
								foreach ( (array) $array['options'] as $key => $val ) {
									$multi_checkbox_data = get_option( $id );
									$checked = isset( $multi_checkbox_data[$key] ) && 1 == $multi_checkbox_data[$key] ? 'selected' : '' ; 
									?>
									<option value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $checked ); ?>>
										<?php esc_html_e( $val ); ?>
									</option>
								<?php } ?>
								</select>	
							</div>
							<?php
						} elseif ( 'multiple_checkbox' == $array['type'] ) { 
							$op = 1;	
							foreach ( (array) $array['options'] as $key => $val ) {
																	
								$multi_checkbox_data = get_option( $id );
								if ( isset( $multi_checkbox_data[ $key ] ) && 1 == $multi_checkbox_data[$key] ) {
									$checked='checked';
								} else {
									$checked='';
								}
								?>
								<span class="multiple_checkbox">
									<label class="" for="<?php echo esc_html( $key ); ?>">
										<input type="hidden" name="<?php echo esc_html( $id ); ?>[<?php echo esc_html( $key ); ?>]" value="0"/>
										<input type="checkbox" id="<?php echo esc_html( $key ); ?>" name="<?php echo esc_html( $id ); ?>[<?php echo esc_html( $key ); ?>]" class=""  <?php echo esc_html( $checked ); ?> value="1"/>
										<span class="multiple_label"><?php echo esc_html( $val ); ?></span>	
										</br>
									</label>																		
								</span>												
							<?php 								
							}								
						} elseif ( 'textarea' == $array['type'] ) {
							?>
							<fieldset>
								<textarea placeholder="<?php echo ( !empty( $array['placeholder'] ) ? esc_html( $array['placeholder'] ) : '' ); ?>" class="input-text regular-input" name="<?php echo esc_html( $id ); ?>" id="<?php echo esc_html( $id ); ?>"><?php echo esc_html( get_option( $id, $array['Default'] ) ); ?></textarea>                                
							</fieldset>
							<span class="" style="font-size: 12px;"><?php echo esc_html( $array['desc_tip'] ); ?></span>
							<?php
						} elseif ( 'tag_block' == $array['type'] ) {
							?>
							<fieldset class="tag_block">
								<code>{customer_email_verification_code}</code><code>{cev_user_verification_link}</code><code>{cev_resend_email_link}</code><code>{cev_display_name}</code><code>{cev_user_login}</code><code>{cev_user_email}</code> 								
							</fieldset>
							<?php
						} else {
							?>
							<fieldset>
								<input class="input-text regular-input " type="text" name="<?php echo esc_html( $id ); ?>" id="<?php echo esc_html( $id ); ?>" style="" value="<?php echo esc_html( get_option( $id, $array['Default'] ) ); ?>" placeholder="<?php echo ( !empty( $array['placeholder'] ) ? esc_html( $array['placeholder'] ) : '' ); ?>">
							</fieldset>
						<?php } ?>
					</td>
				</tr>
				
				<?php 
						if ( isset( $array['dropdown_checkbox_options'] ) ) {
							foreach ( (array) $array['dropdown_checkbox_options'] as $key => $val ) {
								
								$checked = '';					
								if ( 1 == get_option( $key, '' ) ) {
									$checked = 'checked';
								}
								?>
								<tr class="cev-setting-checkbox">
									<td colspan="2" class="cev-setting-checkbox-padding">
										<input type="hidden" name="<?php echo esc_html( $key ); ?>" value="0" />
										<input class="" id="<?php echo esc_html( $key ); ?>" name="<?php echo esc_html( $key ); ?>" type="checkbox" <?php echo esc_html( $checked ); ?> value="1" />
										<label class="" for="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $val ); ?></label>
									</td>
								</tr> 
							<?php
							}
						}
				
						if ( isset( $array['desc'] ) && '' != $array['desc'] ) {
							?>
							<tr class="<?php echo esc_html( $array['class'] ); ?>"><td colspan="2" style=""><p class="description"><?php echo ( isset( $array['desc'] ) ) ? esc_html( $array['desc'] ) : '' ; ?></p></td></tr>
						<?php } ?>				
			<?php } } ?>
			</tbody>
		</table>
	<?php 
	}
	
	/*
	* Get settings tab array data
	* return array
	*/
	public function get_cev_settings_data() {	
	
		$form_data_new = array( 'cev_enter_account_after_registration' => array(
			'title' => __( 'Allow first login after registration without email verification', 'customer-email-verification-for-woocommerce' ),
			'tooltip' => __( 'Allow your customers to access their account for the first time after registration before they verify the email address', 'customer-email-verification-for-woocommerce' ),
			),
			'cev_email_for_verification' => array(
				'title' => __( 'Verification code in new account email', 'customer-email-verification-for-woocommerce' ),
				'tooltip' => __( 'if you select this option, the verification message, code and link will be added in New Account Emails. The separate email verification will be sent only when the customer (or admin) resend verification', 'customer-email-verification-for-woocommerce' ),
				'class' =>  'delay_account_check',
			),
		);							
		$form_data = array(
			'cev_enable_email_verification' => array(
				'type'		=> 'toggle',
				'title'		=> __( 'Enable email verification for new customer accounts', 'customer-email-verification-for-woocommerce' ),
				'show' => true,
				'Default'   => 'yes',
				'class'     => 'cev-skip-padding',
				
			),
			'cev_enter_account_after_registration' => array(
				'type'		=> 'inline_checkbox',
				'show' => true,
				'tooltip' 		=> __('Allow your customers to access their account for the first time after registration before they verify the email address', 'customer-emial-verification-for-woocommerce'),
				'title'    => '',
				'checkbox_array' => apply_filters( 'cev_enter_account_after_registration_options', $form_data_new ),
				'Default'   => '',
				'class'     => 'cev-skip-padding-checkbox ',
			),							
		);
		$form_data = apply_filters( 'cev_general_settings_options', $form_data );
		return $form_data;
	}

	/*
	* Get settings tab array data
	* return array
	*/
	public function get_cev_settings_data_new() {
		
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$all_roles_array = array();
		
		foreach ( $all_roles as $key => $role ) {
			if ( 'administrator' != $key ) {
				$role = array( $key => $role['name'] );
				$all_roles_array = array_merge( $all_roles_array, $role );	
			}
		}
		
		$page_list = wp_list_pluck( get_pages(), 'post_title', 'ID' );
		
		$form_data_2 = array(		
			'cev_redirect_page_after_varification' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Page to redirect after successful verification', 'customer-email-verification-for-woocommerce' ),				
				'class'		=> 'redirect_page border_class',
				'show' => true,
				'tooltip'	=> __('select a page to redirect users after successful verification. In case the email verification was during checkout, the user will be directed to checkout', 'customer-email-verification-for-woocommerce'),
				'Default'   => get_option( 'woocommerce_myaccount_page_id' ),	
				'options'   => $page_list, 				
			),
			'cev_verification_success_message' => array(
				'type'		=> 'textarea',
				'title'		=> __( 'Email verification success message', 'customer-email-verification-for-woocommerce' ),				
				'show'		=> true,
				'tooltip'	=> __('the message that will appear on the top of the my-account or checkout page after successful email verification', 'customer-email-verification-for-woocommerce'),
				'Default'   => __('Your email is verified!', 'customer-email-verification-for-woocommerce' ),
				'id'        => '',
				'placeholder' => __('Your email is verified!', 'customer-email-verification-for-woocommerce' ),	
				'desc_tip'      => '',
				'class'     => 'border_class top',
			),	
			'cev_skip_verification_for_selected_roles' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Skip email verification for the selected user roles:', 'customer-email-verification-for-woocommerce' ),
				'options'   => $all_roles_array,				
				'show' => true,
				'Default'   => '',				
				'class'     => 'border_class top',
			),			
		);
		
		return $form_data_2;
	}

	public function cev_settings_form_update_fun() {
		if ( ! empty( $_POST ) && check_admin_referer( 'cev_settings_form_nonce', 'cev_settings_form_nonce' ) ) {
			
			$data = $this->get_cev_settings_data();
			$data_2 = $this->get_cev_settings_data_new();		
			
			foreach ( $data as $key => $val ) {
				
				if ( isset( $_POST[ $key ] ) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}
			
				if ( isset( $val['type'] ) && 'inline_checkbox' == $val['type'] ) {
					foreach ( (array) $val['checkbox_array'] as $key1 => $val1 ) {
						if ( isset( $_POST[ $key1 ] ) ) {
							update_option( $key1, wc_clean( $_POST[ $key1 ] ) );
						}
					}					
				}
			}
			
			foreach ( $data_2 as $key => $val ) {
				
				if ( isset( $_POST[ $key ] ) ) {
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}
				
				if ( isset( $val['type']) && 'dropdown_checkbox' == $val['type'] ) {
					if ( isset( $val['dropdown_checkbox_options'] ) ) {
						foreach ( (array) $val['dropdown_checkbox_options'] as $key1 => $val1 ) {
							if ( isset( $_POST[ $key1 ] ) ) {						
								update_option( $key1, wc_clean($_POST[ $key1 ]) );
							}
						}					
					}
				}
				
				if ( isset( $val['type'] ) && 'inline_checkbox' == $val['type'] ) {
					foreach ( (array) $val['checkbox_array'] as $key1 => $val1 ) {
						if ( isset( $_POST[ $key1 ] ) ) {						
							update_option( $key1, wc_clean($_POST[ $key1 ]) );
						}
					}					
				}
				
				if ( isset( $val['type'] ) && 'multiple_select' == $val['type'] ) {					
	
					if ( isset( $_POST[ $key ] ) ) {
					
						foreach ( $val['options'] as $op_status => $op_data ) {
							$_POST[ $key ][$op_status] = 0;					
						}
					
						foreach ( wc_clean( $_POST[ $key ] ) as $key1 => $val ) {
							$_POST[ $key ][$val] = 1;									
						}	
						update_option( $key, wc_clean($_POST[ $key ]) );
					} else {
						update_option( $key, '' );
					}
				
				}
			}
		}
	}
	
	
	/**
	 * This function adds custom columns in user listing screen in wp-admin area.
	 */
	public function add_column_users_list( $column ) {
		$column['cev_verified'] = __( 'Email verification', 'customer-email-verification-for-woocommerce' );
		$column['cev_action'] = __( 'Actions', 'customer-email-verification-for-woocommerce' );
		return $column;
	}
	
	/**
	 * This function adds custom values to custom columns in user listing screen in wp-admin area.
	 */	
	public function add_details_in_custom_users_list( $val, $column_name, $user_id ) {
		
		wp_enqueue_script( 'jquery-blockui' );
		
		wp_enqueue_style( 'customer_email_verification_user_admin_styles', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/user-admin.css', array(), woo_customer_email_verification()->version );
				
		wp_enqueue_script( 'customer_email_verification_user_admin_script', woo_customer_email_verification()->plugin_dir_url() . 'assets/js/user-admin.js', array( 'jquery','wp-util' ), woo_customer_email_verification()->version , true);
		
		$user_role = get_userdata( $user_id );
		$verified  = get_user_meta( $user_id, 'customer_email_verified', true );
		
		if ( 'cev_verified' === $column_name ) {
			if ( !woo_customer_email_verification()->is_admin_user( $user_id ) ) {
				if ( !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {
					
					$verified_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverified_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverified_btn_css . '" class="dashicons dashicons-yes cev_5 cev_verified_admin_user_action" title="Verified"></span>';
					$html .= '<span style="' . $verified_btn_css . '" class="dashicons dashicons-no no-border cev_unverified_admin_user_action cev_5" title="Unverify"></span>';					
					return $html;
				} 
			} 
			return '-';	
		}
		if ( 'cev_action' === $column_name ) {
			if ( !woo_customer_email_verification()->is_admin_user( $user_id ) ) {
				if ( !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {	
					
					$verify_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverify_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverify_btn_css . '" class="dashicons dashicons-no cev_dashicons_icon_unverify_user" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span>';
					$html .= '<span style="' . $verify_btn_css . '" class="dashicons dashicons-yes small-yes cev_dashicons_icon_verify_user cev_10" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span>';
					$html .= '<span style="' . $verify_btn_css . '" class="dashicons dashicons-image-rotate cev_dashicons_icon_resend_email" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span></span>';
					return $html;
				}
			}			
		}		
		return $val;
	}
	
	public function cev_manualy_user_verify_in_user_menu() {
		
		if ( isset( $_POST['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wp_nonce'] ), 'wc_cev_email' ) ) { 
		
			$user_id = isset( $_POST['id'] ) ? wc_clean( $_POST['id'] ) : '';
			$action_type = isset( $_POST['actin_type'] ) ? wc_clean( $_POST['actin_type'] ) : '';
			
			if ( 'unverify_user' == $action_type ) {
				delete_user_meta( $user_id, 'customer_email_verified' ); 
			}
			
			if ( 'verify_user' == $action_type ) {
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
			}
			
			if ( 'resend_email' == $action_type ) {
				$current_user           = get_user_by( 'id', $user_id );
				$is_secret_code_present = get_user_meta( $user_id, 'customer_email_verification_code', true );
	
				if ( '' === $is_secret_code_present ) {
					$secret_code = md5( $user_id . time() );
					update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
				}					
				
				WC_customer_email_verification_email_Common()->wuev_user_id = $user_id; // WPCS: input var ok, CSRF ok.
				WC_customer_email_verification_email_Common()->wuev_myaccount_page_id = $this->my_account_id;
				
				WC_customer_email_verification_email_Common()->code_mail_sender( $current_user->user_email );
			}
		}
		exit;
	}
	
	/**
	 * This function manually verifies a user from wp-admin area.
	 */
	public function cev_manual_verify_user() {
		
		if ( isset( $_GET['user_id'] ) && isset( $_GET['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['wp_nonce'] ), 'wc_cev_email' ) ) {
			
			$user_id = wc_clean( $_GET['user_id'] );
			
			if ( isset( $_GET['wc_cev_confirm'] ) && 'true' === $_GET['wc_cev_confirm'] ) { 
				
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
				add_action( 'admin_notices', array( $this, 'manual_cev_verify_email_success_admin' ) );
				
			} else {
				delete_user_meta( $user_id, 'customer_email_verified' ); 
				add_action( 'admin_notices', array( $this, 'manual_cev_verify_email_unverify_admin' ) );				
			}				
		}
		
		if ( isset( $user_id ) && isset( $_GET['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['wp_nonce'] ), 'wc_cev_email_confirmation' ) ) {			
			$current_user           = get_user_by( 'id', $user_id );
			$is_secret_code_present = get_user_meta( $user_id, 'customer_email_verification_code', true );

			if ( '' === $is_secret_code_present ) {
				$secret_code = md5( $user_id . time() );
				update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
			}					
			
			WC_customer_email_verification_email_Common()->wuev_user_id = $user_id; // WPCS: input var ok, CSRF ok.
			WC_customer_email_verification_email_Common()->wuev_myaccount_page_id = $this->my_account_id;
			
			WC_customer_email_verification_email_Common()->code_mail_sender( $current_user->user_email );
			add_action( 'admin_notices', array( $this, 'manual_confirmation_email_success_admin' ) );
		}		
	}
	
	public function manual_confirmation_email_success_admin() {
		$text = __( 'Verification email successfully sent.', 'customer-email-verification-for-woocommerce' );
		?>
		<div class="updated notice">
			<p><?php echo esc_html( $text ); ?></p>
		</div>
		<?php
	}
	
	public function manual_cev_verify_email_success_admin() {
		$text = __( 'User verified successfully.', 'customer-email-verification-for-woocommerce' );
		?>
		<div class="updated notice">
			<p><?php echo esc_html( $text ); ?></p>
		</div>
		<?php
	}
	public function manual_cev_verify_email_unverify_admin() {
		$text = __( 'User unverified.', 'customer-email-verification-for-woocommerce' );
		?>
		<div class="updated notice">
			<p><?php echo esc_html( $text ); ?></p>
		</div>
		<?php
	}

	// define the woocommerce_login_form_end callback 
	public function action_woocommerce_login_form_end() {
		?>
		<p class="woocommerce-LostPassword lost_password">
			<a href="<?php echo esc_url( get_home_url() ); ?>?p=reset-verification-email"><?php esc_html_e( 'Resend verification email', 'customer-email-verification-for-woocommerce' ); ?></a>
		</p>
		<?php
	} 

	public function show_cev_fields_in_single_user( $user ) { 
	
		wp_enqueue_style( 'customer_email_verification_user_admin_styles', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/user-admin.css', array(), woo_customer_email_verification()->version );
				
		wp_enqueue_script( 'customer_email_verification_user_admin_script', woo_customer_email_verification()->plugin_dir_url() . 'assets/js/user-admin.js', array( 'jquery','wp-util' ), woo_customer_email_verification()->version , true);
		
		$user_id = $user->ID; 
		$verified  = get_user_meta( $user_id, 'customer_email_verified', true );
		$user_role = get_userdata( $user_id );
		?>
		
		<table class="form-table cev-admin-menu">
			<th colspan="2"><h4 class="cev_admin_user"><?php esc_html_e( 'Customer verification', 'customer-email-verification-for-woocommerce' ); ?></h4></th>
			<tr>
				<th class="cev-admin-padding"><label for="year_of_birth"><?php esc_html_e( 'Email verification status:', 'customer-email-verification-for-woocommerce' ); ?></label></th>
				<td>
				<?php 
				if ( !woo_customer_email_verification()->is_admin_user( $user_id )  && !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {
					
					$verified_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverified_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverified_btn_css . '" class="dashicons dashicons-yes cev_5 cev_verified_admin_user_action_single" title="Verified"></span>';
					$html .= '<span style="' . $verified_btn_css . '" class="dashicons dashicons-no no-border cev_unverified_admin_user_action_single cev_5" title="Unverify"></span>';					
					echo wp_kses_post( $html );
				} else {
					echo 'Admin';
				}
				?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<?php					
				if ( !woo_customer_email_verification()->is_admin_user( $user_id ) && !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {
					
					$verify_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverify_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
				
					$text = '<span class="dashicons dashicons-yes cev-admin-dashicons" style="color:#ffffff; margin-right: 2px;"></span><span>' . __('Verify email manually', 'customer-email-verification-for-woocommerce') . '</span>'; 
					
					echo '<a style="' . esc_html( $verify_btn_css ) . '" class="button-primary cev-admin-verify-button cev_dashicons_icon_verify_user" id="' . esc_html( $user_id ) . '" wp_nonce="' . esc_html( wp_create_nonce( 'wc_cev_email' ) ) . ' "> ' . wp_kses_post( $text ) . '</a>';
						
					$text = '<span class="dashicons dashicons-image-rotate cev-admin-dashicons cev-rotate" ></span><span> ' . __('Resend verification email', 'customer-email-verification-for-woocommerce') . '</span>';
	
					echo '<a style="' . esc_html( $verify_btn_css ) . '" class="button-primary cev-admin-resend-button cev_dashicons_icon_resend_email" id="' . esc_html( $user_id ) . '" wp_nonce="' . esc_html( wp_create_nonce( 'wc_cev_email' ) ) . ' "> ' . wp_kses_post( $text ) . '</a>';
				
					$text = '<span class="dashicons dashicons-no cev-admin-dashicons"></span><span>' . __( 'Un-verify email', 'customer-email-verification-for-woocommerce' ) . '</span>';
	
					echo '<a style="' . esc_html( $unverify_btn_css ) . '" class="button-primary cev-admin-unverify-button cev_dashicons_icon_unverify_user" id="' . esc_html( $user_id ) . '" wp_nonce="' . esc_html( wp_create_nonce( 'wc_cev_email' ) ) . '">' . wp_kses_post( $text ) . '</a>';					
				}
				?>
				</td>
			
			</tr>
		</table>
		
	<?php
	}

	public function filter_user_by_verified( $which ) {
		
		$true_selected = '';
		$false_selected = '';
		
		// figure out which button was clicked. The $which in filter_by_job_role()
		if ( isset( $_GET['customer_email_verified_top'] ) ) {
			$top = wc_clean( $_GET['customer_email_verified_top'] ) ? wc_clean( $_GET['customer_email_verified_top'] ) : null;
		}
		
		if ( isset( $_GET['customer_email_verified_bottom']) ) {
			$bottom = wc_clean( $_GET['customer_email_verified_bottom'] ) ? wc_clean( $_GET['customer_email_verified_bottom'] ) : null;
		}
		
		if ( !empty($top) || !empty($bottom) ) {
			
			$section = !empty($top) ? $top : $bottom;
			
			if ( 'true' == $section ) {
				$true_selected = 'selected';	
			}
			
			if ( 'false' == $section ) {
				$false_selected = 'selected';	
			}
		}
		
		// template for filtering
		$st = '<select name="customer_email_verified_%s" style="float:none;margin-left:10px;">
			<option value="">%s</option>%s</select>';
		
		
		// generate options
		$options = '<option value="true" ' . $true_selected . '>' . __( 'Verified', 'customer-email-verification-for-woocommerce' ) . '</option>
			<option value="false" ' . $false_selected . '>' . __( 'Non verified', 'customer-email-verification-for-woocommerce' ) . '</option>';
		
		// combine template and options
		$select = sprintf( $st, $which, __( 'User verification', 'customer-email-verification-for-woocommerce' ), $options );
		
		// output <select> and submit button
		echo $select;
		submit_button(__( 'Filter' ), null, $which, false);	
	}
	
	public function filter_users_by_user_by_verified_section( $query ) {
		global $pagenow;
		if ( is_admin() && 'users.php' == $pagenow ) {
			
			// figure out which button was clicked. The $which in filter_by_job_role()
			if ( isset( $_GET['customer_email_verified_top'] ) ) {
				$top = wc_clean( $_GET['customer_email_verified_top'] ) ? wc_clean( $_GET['customer_email_verified_top'] ) : null;
			}
			
			if ( isset( $_GET['customer_email_verified_bottom'] ) ) {
				$bottom = wc_clean( $_GET['customer_email_verified_bottom'] ) ? wc_clean( $_GET['customer_email_verified_bottom'] ) : null;
			}
			
			if ( !empty( $top ) || !empty( $bottom ) ) {
				
				$section = !empty($top) ? $top : $bottom;
				
				if ( 'true' == $section ) {
					// change the meta query based on which option was chosen
					$meta_query = array (array (
						'key' => 'customer_email_verified',
						'value' => $section,
						'compare' => 'LIKE'
					));
				} else {
					$meta_query = array (
						'relation' => 'AND',
						array (
							'key' => 'cev_email_verification_pin',							
							'compare' => 'EXISTS'
						),
						array (
							'key' => 'customer_email_verified',
							'value' => $section,
							'compare' => 'NOT EXISTS'
						),	
					);
				}
				$query->set('meta_query', $meta_query);				
			}
		}	
	}
 
	public function add_custom_bulk_actions_for_user( $bulk_array ) {
	 
		$bulk_array['verify_users_email'] = __('Verify users email', 'customer-email-verification-for-woocommerce');
		$bulk_array['send_verification_email'] = __('Send verification email', 'customer-email-verification-for-woocommerce');
		return $bulk_array;
	 
	}

	public function users_bulk_action_handler( $redirect, $doaction, $object_ids ) {
	 
		$redirect = remove_query_arg( array( 'user_id', 'wc_cev_confirm', 'wp_nonce', 'wc_cev_confirmation', 'verify_users_emails', 'send_verification_emails' ), $redirect );

		if ( 'verify_users_email' == $doaction ) {
	 
			foreach ( $object_ids as $user_id ) {
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
			}
	 
			$redirect = add_query_arg( 'verify_users_emails', count( $object_ids ), $redirect );
	 
		}
	 
		if ( 'send_verification_email' == $doaction ) {
			foreach ( $object_ids as $user_id ) {
				
				$current_user = get_user_by( 'id', $user_id );
				$this->user_id                         = $current_user->ID;
				$this->email_id                        = $current_user->user_email;
				$this->user_login                      = $current_user->user_login;
				$this->user_email                      = $current_user->user_email;
				WC_customer_email_verification_email_Common()->wuev_user_id  = $current_user->ID;
				WC_customer_email_verification_email_Common()->wuev_myaccount_page_id = $this->my_account_id;
				$this->is_user_created                 = true;		
				$is_secret_code_present                = get_user_meta( $this->user_id, 'customer_email_verification_code', true );
		
				if ( '' === $is_secret_code_present ) {
					$secret_code = md5( $this->user_id . time() );
					update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
				}
				
				$cev_email_for_verification = get_option( 'cev_email_for_verification', 0 );
				$verified = get_user_meta( $this->user_id, 'customer_email_verified', true );
				$cev_email_for_verification_mode = get_option( 'cev_email_for_verification_mode', 1 );
				
				if ( 0 == $cev_email_for_verification && 'true' != $verified ) {
					WC_customer_email_verification_email_Common()->code_mail_sender( $current_user->user_email );
				}	
			}
			$redirect = add_query_arg( 'send_verification_emails', count( $object_ids ), $redirect );
		}
	 
		return $redirect;
	 
	}
 
	public function user_bulk_action_notices() {
	 
		if ( ! empty( $_REQUEST['verify_users_emails'] ) ) {			
			printf( '<div id="message" class="updated notice is-dismissible"><p>' .
				/* translators: %s: replace with email */
				esc_html( _n( 'Varification Status updated for  %s user.',
				'Varification Status updated for  %s users.',
				intval( $_REQUEST['verify_users_emails'] )
			) ) . '</p></div>', intval( $_REQUEST['verify_users_emails'] ) );
		}
	 
		if ( ! empty( $_REQUEST['send_verification_emails'] ) ) {			
			printf( '<div id="message" class="updated notice is-dismissible"><p>' .
				/* translators: %s: replace with email */
				esc_html( _n( 'Varification email sent to %s user.',
				'Varification email sent to %s users.',
				intval( $_REQUEST['send_verification_emails'] )
			) ) . '</p></div>', intval( $_REQUEST['send_verification_emails'] ) );
	 
		}
	 
	}	
}
