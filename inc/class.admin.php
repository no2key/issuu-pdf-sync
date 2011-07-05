<?php
class IPU_Admin {
	
	/**
	 * Constructor PHP4 like
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	function IPU_Admin() {
		global $pagenow;
		
		add_filter("attachment_fields_to_edit", array(&$this, "insertIPUButton"), 10, 2);
		add_filter("media_send_to_editor", array(&$this, "sendToEditor"));
		
		if ( $pagenow == "media.php" )
			add_action("admin_head", array(&$this, "editMediaJs"), 50 );
		add_action( 'admin_init', array( &$this, 'checkJsPdfEdition' ) );
		add_action( 'admin_menu', array( &$this, 'addPluginMenu' ) );
		
		wp_enqueue_script( 'jquery' );
	}
	
	
	function addPluginMenu() {
		add_options_page( __('Options for Issuu PDF Uploader', 'ipu'), __('Issuu PDF Uploader', 'ipu'), 'manage_options', 'ipu-options', array( &$this, 'displayOptions' ) );
	}
	
	/**
	 * Call the admin option template
	 * 
	 * @echo the form 
	 * @author Benjamin Niess
	 */
	function displayOptions() {
		if ( isset($_POST['save']) ) {
			$new_options = array();
			
			// Update existing
			foreach( (array) $_POST['ipu'] as $key => $value ) {
				$new_options[$key] = stripslashes($value);
			}
			
			update_option( 'ipu_options', $new_options );
		}
		
		if (isset($_POST['save']) ) {
			echo '<div class="message updated"><p>'.__('Options updated!', 'ipu').'</p></div>';
		}
		
		$fields = get_option('ipu_options');
		if ( $fields == false ) {
			$fields = array();
		}
		?>
		<div class="wrap" id="ipu_options">
			<h2><?php _e('Issuu PDF Uploader', 'ipu'); ?></h2>
			
			<form method="post" action="">
				<table class="form-table">
					
					<tr><td colspan="2"><h3><?php _e('Issuu configuration', 'ipu'); ?></h3></td></tr> 
					
					<tr valign="top">
						<th scope="row"><?php _e('Issuu API Key', 'ipu'); ?><br /><a href="http://issuu.com/" target="_blank"><?php _e('Get an Issuu API Key', 'ipu'); ?></a></th>
						<td><input type="text" name="ipu[issuu_api_key]" value="<?php echo isset( $fields['issuu_api_key'] ) ? $fields['issuu_api_key'] : '' ; ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Issuu private key', 'ipu'); ?></th>
						<td><input type="text" name="ipu[issuu_private_key]" value="<?php echo isset( $fields['issuu_private_key'] ) ? $fields['issuu_private_key'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Automatically upload PDFs to Issuu', 'ipu'); ?></th>
						<td><input type="checkbox" <?php checked( isset( $fields['auto_upload'] ) ? $fields['auto_upload'] : '' , 1 ); ?> name="ipu[auto_upload]" value="1" /></td>
					</tr>
					
					<tr><td colspan="2"><h3><?php _e('Default embed code configuration', 'ipu'); ?></h3></td></tr> 
					
					<tr valign="top">
						<th scope="row"><?php _e('Layout', 'ipu'); ?></th>
						<td>
							<input type="radio" name="ipu[layout]" value="1" <?php checked( isset( $fields['layout'] ) ? $fields['layout'] : 0 , 1 ); ?> /> <?php _e('Two up', 'ipu'); ?>
							<input type="radio" name="ipu[layout]" value="2" <?php checked( isset( $fields['layout'] ) ? $fields['layout'] : 0 , 2 ); ?> /> <?php _e('Single page', 'ipu'); ?>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Width', 'ipu'); ?></th>
						<td><input type="text" name="ipu[width]" value="<?php echo isset(  $fields['width'] ) ? $fields['width'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Height', 'ipu'); ?></th>
						<td><input type="text" name="ipu[height]" value="<?php echo isset(  $fields['height'] ) ? $fields['height'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Background color', 'ipu'); ?></th>
						<td># <input type="text" name="ipu[bgcolor]" value="<?php echo isset(  $fields['bgcolor'] ) ? $fields['bgcolor'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Allow full screen', 'ipu'); ?></th>
						<td><input type="checkbox" <?php checked( isset( $fields['allow_full_screen'] ) ? $fields['allow_full_screen'] : '' , 1 ); ?> name="ipu[allow_full_screen]" value="1" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Always show flip buttons', 'ipu'); ?></th>
						<td><input type="checkbox" <?php checked( isset( $fields['show_flip_buttons'] ) ? $fields['show_flip_buttons'] : '' , 1 ); ?> name="ipu[show_flip_buttons]" value="1" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Auto flip (every 6 seconds)', 'ipu'); ?></th>
						<td>
							<input type="checkbox" name="ipu[autoflip]" value="1" <?php checked( isset( $fields['autoflip'] ) ? $fields['autoflip'] : 0 , 1 ); ?> />
						</td>
					</tr>
					
				</table>
				
				<p class="submit">
					<input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes', 'ipu') ?>" />
				</p>
			</form>
		</div>
		<?php
	}
	
	/*
	 * Send a WordPress PDF to Issuu webservice
	 * 
	 * @param $post_id the WP post id
	 * @return string the issuu document id | false 
	 * @author Benjamin Niess
	 */
	function sendPDFToIssuu( $post_id = 0 ){
		global $ipu_options;
		
		if ( (int)$post_id == 0 )
			return false;
		
		if ( $this->hasApiKeys() == false )
			return false;
		
		// Get attachment infos
		$post_data = get_post( $post_id );
		
		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;
		
		// Prepare the MD5 signature for the Issuu Webservice
		$md5_signature = md5( $ipu_options['issuu_secret_key'] . "actionissuu.document.url_uploadapiKey" . $ipu_options['issuu_api_key'] . "formatjsonslurpUrl" . $post_data->guid . "title" . sanitize_title( $post_data->post_title ) );
		
		// Call the Webservice
		$url_to_call = "http://api.issuu.com/1_0?action=issuu.document.url_upload&apiKey=" . $ipu_options['issuu_api_key'] . "&slurpUrl=" . $post_data->guid . "&format=json&title=" . sanitize_title( $post_data->post_title ) . "&signature=" . $md5_signature; 
		
		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );
		
		// Check if no sever error
		if( is_wp_error($response) || isset($response->errors) || $response == null ) {
			return false;
		}
		// Decode the Json
		$response = json_decode( $response['body'] );
		
		if ( empty( $response) )
			return false;
			
		// Check stat of the action
		
		if ( $response->rsp->stat == "fail" )
			return false;
		
		// Check if the publication id exists
		if ( !isset( $response->rsp->_content->document->documentId ) || empty( $response->rsp->_content->document->documentId ) )
			return false;
		
		// Update the attachment post meta with the Issuu PDF ID
		update_post_meta( $post_id, 'issuu_pdf_id', $response->rsp->_content->document->documentId );
		update_post_meta( $post_id, 'issuu_pdf_name', $response->rsp->_content->document->name );
		
		return $response->rsp->_content->document->documentId;
	}

	
	/*
	 * Delete an Issuu PDF from Issuu webservice
	 * 
	 * @param $post_id the WP post id
	 * @return true | false 
	 * @author Benjamin Niess
	 */
	function deletePDFFromIssuu( $post_id = 0 ){
		if ( (int)$post_id == 0 )
			return false;
		
		// Get attachment infos
		$post_data = get_post( $post_id );
		
		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;
		
		$issuu_pdf_name = get_post_meta( $post_id, 'issuu_pdf_name', true );
		if ( empty( $issuu_pdf_name ) )
			return false;
		
		// Prepare the MD5 signature for the Issuu Webservice
		$md5_signature = md5( $ipu_options['issuu_secret_key'] . "actionissuu.document.deleteapiKey" . $ipu_options['issuu_api_key'] . "formatjsonnames" . $issuu_pdf_name );
		
		// Call the Webservice
		$url_to_call = "http://api.issuu.com/1_0?action=issuu.document.delete&apiKey=" . $ipu_options['issuu_api_key'] . "&format=json&names=" . $issuu_pdf_name . "&signature=" . $md5_signature; 
		
		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );
		
		// Check if no sever error
		if( is_wp_error($response) || isset($response->errors) || $response == null ) {
			return false;
		}
		// Decode the Json
		$response = json_decode( $response['body'] );
		
		if ( empty( $response) )
			return false;
			
		// Check stat of the action
		if ( $response->rsp->stat == "fail" )
			return false;
		
		// Update the attachment post meta with the Issuu PDF ID
		delete_post_meta( $post_id, 'issuu_pdf_id' );
		delete_post_meta( $post_id, 'issuu_pdf_name' );
		update_post_meta( $post_id, 'disable_auto_upload', 1 );

		return true;
	}

	/**
	 * Inserts Issuu PDF Uploader button into media library popup
	 * @return the amended form_fields structure
	 * @param $form_fields Object
	 * @param $post Object
	 */
	function insertIPUButton( $form_fields, $post ) {
		global $wp_version, $ipu_options;
		
		if ( !isset( $form_fields ) || empty( $form_fields ) || !isset( $post ) || empty( $post ) )
			return $form_fields;
		
		$file = wp_get_attachment_url( $post->ID );
		
		// Only add the extra button if the attachment is a PDF file
		if ( $post->post_mime_type != 'application/pdf' )
			return $form_fields;
		
		// Check on post meta if the PDF has already been uploaded on Issuu
		$issuu_pdf_id = get_post_meta( $post->ID, 'issuu_pdf_id', true );
		$disable_auto_upload = get_post_meta( $post->ID, 'disable_auto_upload', true );
		
		// Upload the PDF to Issuu if necessary and if the Auto upload feature is enabled
		if ( empty( $issuu_pdf_id ) && isset( $ipu_options['auto_upload'] ) && $ipu_options['auto_upload'] == 1 && $disable_auto_upload != 1)
			$issuu_pdf_id = $this->sendPDFToIssuu( $post->ID );
		
		if ( empty( $issuu_pdf_id ) )
			return $form_fields;
		
		$form_fields["url"]["html"] .= "<button type='button' class='button urlissuupdfuploader issuu-pdf-" . $issuu_pdf_id . "' value='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]' title='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]'>Issuu PDF</button>";
		
		$form_fields["url"]["html"] .= "<script type='text/javascript'>
		jQuery('issuu-pdf-" . $issuu_pdf_id . "').bind('click', function(){jQuery(this).siblings('input').val(this.value);});
		</script>\n";
		
		return $form_fields;
	}

	function hasApiKeys(){
		global $ipu_options;
		
		if ( !isset( $ipu_options['issuu_api_key'] ) || empty( $ipu_options['issuu_api_key'] ) || !isset( $ipu_options['issuu_secret_key'] ) || empty( $ipu_options['issuu_secret_key'] ) )
			return false;
		
		return true;
	}

	/**
	 * Format the html inserted when the Audio Player button is used
	 * @param $html String
	 * @return String 
	 */
	function sendToEditor($html) {
		if( preg_match( '|\[pdf (.*?)\]|i', $html, $matches ) ) {
			if ( isset($matches[0]) ) {
				$html = $matches[0];
			}
		}
		return $html;
	}
	
	/*
	 * Check if an action is set on the $_GET var and call the PHP function corresponding 
	 * @author Benjamin Niess
	 */
	function checkJsPdfEdition(){
		if ( !isset( $_GET['attachment_id'] ) || (int)$_GET['attachment_id'] == 0 || !isset( $_GET['action'] ) || empty( $_GET['action'] ) )
			return false;
		
		if ( $_GET['action'] == 'send_pdf' ){
			die( $this->sendPDFToIssuu( $_GET['attachment_id'] ) );
		} elseif ( $_GET['action'] == 'delete_pdf' ){
			die( $this->deletePDFFromIssuu( $_GET['attachment_id'] ) );
		}
	}
	
	/*
	 * Print some JS code for the media.php page (for PDFs only)
	 * @author Benjamin Niess
	 */
	function editMediaJs(){
		if ( !isset( $_GET['attachment_id'] ) || (int)$_GET['attachment_id'] <= 0 )
			return false;
			
		// Get attachment infos
		$post_data = get_post( $_GET['attachment_id'] );
		
		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;
		
		// Check on post meta if the PDF has already been uploaded on Issuu
		$issuu_pdf_id = get_post_meta( $_GET['attachment_id'], 'issuu_pdf_id', true );
		
		?>
		<script type="text/javascript">
			jQuery(function() {
				
				jQuery('#media-single-form .slidetoggle tbody tr').last().after('<tr class="reload_pdf"><th valign="top" scope="row" class="label"><label><span class="alignleft"><?php _e( 'Issuu status', 'ipu' ); ?></span><br class="clear"></label></th><td class="field"><?php 
					if ( !empty( $issuu_pdf_id ) ) : 
						?><p style="color:#00AA00;" id="admin_delete_pdf"><?php _e( 'This PDF is already synchronised on Issuu', 'ipu' ); ?> <br /><a href=""><?php _e( '> Click here to delete this PDF from Issuu', 'ipu' ); ?></a></p><?php 
					else : 
						?><p style="color:#AA0000;" id="admin_send_pdf"><?php _e( 'This PDF is not synchronised on Issuu', 'ipu' ); ?> <br /><a href=""><?php _e( '> Click here to send this PDF to Issuu', 'ipu' ); ?></a></p><?php 
					endif; 
				?></td></tr>');
				
				// Sending PDF
				jQuery('#admin_send_pdf a').click(function( e ) {
					jQuery('#admin_send_pdf').html('<?php _e( 'Loading', 'ipu' ); ?>...');
					jQuery('#admin_send_pdf').css( 'color', '#000000');
					jQuery.get('<?php echo admin_url( 'media.php?attachment_id=' . $_GET['attachment_id'] . '&action=send_pdf' ); ?>', function(data) {
						
						if ( data == false ){
							jQuery('#admin_send_pdf').html('<?php _e( 'An error occured during synchronisation with Issuu', 'ipu' ); ?>');
							jQuery('#admin_send_pdf').css( 'color', '#AA0000');
						}else {
							jQuery('#admin_send_pdf').html('<?php _e( 'Your PDF is now on Issuu !', 'ipu' ); ?>');
							jQuery('#admin_send_pdf').css( 'color', '#00AA00');
						};
					});
					e.preventDefault();
					
					
				});
				
				// Deleting PDF
				jQuery('#admin_delete_pdf a').click(function( e ) {
					jQuery('#admin_delete_pdf').html('<?php _e( 'Loading', 'ipu' ); ?>...');
					jQuery('#admin_delete_pdf').css( 'color', '#000000');
					jQuery.get('<?php echo admin_url( 'media.php?attachment_id=' . $_GET['attachment_id'] . '&action=delete_pdf' ); ?>', function(data) {
						
						if ( data == true ){
							jQuery('#admin_delete_pdf').html('<?php _e( 'Your PDF has been successfuly deleted', 'ipu' ); ?>');
							jQuery('#admin_delete_pdf').css( 'color', '#00AA00');
						}else {
							jQuery('#admin_delete_pdf').html('<?php _e( 'An error occured during PDF deletion', 'ipu' ); ?>');
							jQuery('#admin_delete_pdf').css( 'color', '#AA0000');
						};
					});
					e.preventDefault();
					
				});

			});
		</script>
		<?php
	}
}
?>