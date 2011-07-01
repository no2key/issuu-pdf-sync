<?php
class IPU_Admin {
	
	/**
	 * Constructor PHP4 like
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	function IPU_Admin() {

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
		$md5_signature = md5( IPU_SECRET_KEY . "actionissuu.document.url_uploadapiKey" . IPU_API_KEY . "formatjsonslurpUrl" . $post_data->guid );
		
		// Call the Webservice
		$url_to_call = "http://api.issuu.com/1_0?action=issuu.document.url_upload&apiKey=" . IPU_API_KEY . "&slurpUrl=" . $post_data->guid . "&format=json&signature=" . $md5_signature; 
		
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
		
	}
	
}
?>