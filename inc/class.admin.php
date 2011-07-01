<?php
class IPU_Admin {
	
	/**
	 * Constructor PHP4 like
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	function IPU_Admin() {
		add_filter("attachment_fields_to_edit", array(&$this, "insertIPUButton"), 10, 2);
		add_filter("media_send_to_editor", array(&$this, "sendToEditor"));
		
		// ADD the admin options page
		add_action( 'admin_menu', array( &$this, 'IPU_plugin_menu' ) );

	}
	
	function IPU_plugin_menu() {
		add_options_page( __('Options for Issuu PDF Uploader', 'ipu'), __('Issuu PDF Uploader', 'ipu'), 'manage_options', 'ipu-options', array( &$this, 'display_IPU_options' ) );
	}
	
	/**
	 * Call the admin option template
	 * 
	 * @echo the form 
	 * @author Benjamin Niess
	 */
	function display_IPU_options() {
	
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
						<th scope="row"><?php _e('Width', 'ipu'); ?></th>
						<td><input type="text" name="ipu[width]" value="<?php echo isset(  $fields['width'] ) ? $fields['width'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Height', 'ipu'); ?></th>
						<td><input type="text" name="ipu[height]" value="<?php echo isset(  $fields['height'] ) ? $fields['height'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e('Allow full screen', 'ipu'); ?></th>
						<td><input type="checkbox" <?php checked( isset( $fields['allow_full_screen'] ) ? $fields['allow_full_screen'] : '' , 1 ); ?> name="ipu[allow_full_screen]" value="1" /></td>
					</tr>
					
				</table>
				
				<p class="submit">
					<input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes', 'ipu') ?>" />
				</p>
			</form>
		</div>
		<?php
	}
	
	function sendPDFToIssuu( $post_id = 0 ){
		
		if ( (int)$post_id == 0 )
			return false;
		
		// Get attachment infos
		$post_data = get_post( $post_id );
		
		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;
		
		// Prepare the MD5 signature for the Issuu Webservice
		$md5_signature = md5( IPU_SECRET_KEY . "actionissuu.document.url_uploadapiKey" . IPU_API_KEY . "formatjsonslurpUrl" . $post_data->guid . "title" . $post_data->post_title );
		
		// Call the Webservice
		$url_to_call = "http://api.issuu.com/1_0?action=issuu.document.url_upload&apiKey=" . IPU_API_KEY . "&slurpUrl=" . $post_data->guid . "&format=json&title=" . $post_data->post_title . "&signature=" . $md5_signature; 
		
		// Cath the response
		$reponse = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );
		
		// Check if no sever error
		if( is_wp_error($response) || isset($reponse->errors) || $reponse == null ) {
			return false;
		}
		
		// Decode the Json
		$response = json_decode( $reponse['body'] );
		
		// Check stat of the action
		if ( $response->rsp->stat == "fail" )
			return false;
		
		// Check if the publication id exists
		if ( !isset( $response->rsp->_content->document->documentId ) || empty( $response->rsp->_content->document->documentId ) )
			return false;
		
		// Update the attachment post meta with the Issuu PDF ID
		update_post_meta( $post_id, 'issuu_pdf_id', $response->rsp->_content->document->documentId );
		
		return $response->rsp->_content->document->documentId;
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
		
		// Upload the PDF to Issuu if necessary and if the Auto upload feature is enabled
		if ( empty( $issuu_pdf_id ) && isset( $ipu_options['auto_upload'] ) && $ipu_options['auto_upload'] == 1 )
			$issuu_pdf_id = $this->sendPDFToIssuu( $post->ID );
		
		if ( empty( $issuu_pdf_id ) )
			return $form_fields;
		
		$form_fields["url"]["html"] .= "<button type='button' class='button urlissuupdfuploader issuu-pdf-" . $issuu_pdf_id . "' value='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]' title='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]'>Issuu PDF</button>";
		
		$form_fields["url"]["html"] .= "<script type='text/javascript'>
		jQuery('issuu-pdf-" . $issuu_pdf_id . "').bind('click', function(){jQuery(this).siblings('input').val(this.value);});
		</script>\n";
		
		return $form_fields;
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
}
?>