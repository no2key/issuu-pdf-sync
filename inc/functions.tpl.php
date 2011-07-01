<?php
//functions for users

function test_issuu(){
	global $ipu;
	$ipu['admin']->sendPDFToIssuu( 4 );
}
?>