<?php

	define ( DOCUMENT_ROOT, dirname(__FILE__) );

	require_once ( DOCUMENT_ROOT . '/config.php' );
	
	if ( file_exists( DOCUMENT_ROOT.'/install.php' ) ) { 
		require_once ( DOCUMENT_ROOT.'/install.php' ); }
		
	else { 
		require_once ( DOCUMENT_ROOT . '/core.php' ); 
		$out = new A(); }
	
?>