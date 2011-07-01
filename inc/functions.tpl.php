<?php
//functions for users

function test_issuu(){
	global $ipu;
	$ipu['admin']->sendPDFToIssuu( 4 );
	/*
	$md5_signature = md5( IPU_SECRET_KEY . "actionissuu.document.url_uploadapiKey" . IPU_API_KEY . "formatjsonslurpUrlhttp://dev.beapi.fr/benjamin/sandbox/wp-content/uploads/2011/07/kpfrench.pdf" );
	$url_to_call = "http://api.issuu.com/1_0?action=issuu.document.url_upload&apiKey=" . IPU_API_KEY . "&slurpUrl=http://dev.beapi.fr/benjamin/sandbox/wp-content/uploads/2011/07/kpfrench.pdf&format=json&signature=" . $md5_signature; 
	
	$reponse = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );
	
	if( is_wp_error($response) || isset($reponse->errors) || $reponse == null ) {
		return false;
	}
	
	$response = json_decode( $reponse['body'] );
	
	//check stat of the action
	if ( $response->rsp->stat == "fail" )
		return false;
	
	//check if the publication id exists
	if ( !isset( $response->rsp->_content->document->documentId ) || empty( $response->rsp->_content->document->documentId ) )
		return false;
	
	$publication_id = $response->rsp->_content->document->documentId;
	*/
}
?>