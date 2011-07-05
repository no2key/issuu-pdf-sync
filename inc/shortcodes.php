<?php
add_shortcode('pdf','issuu_pdf_embeder'); // two shortcodes referencing the same callback
function issuu_pdf_embeder( $atts, $content = null ) {
	
	global $ipu_options;
	
	if ( isset( $ipu_options['layout'] ) && $ipu_options['layout'] == 2 )
		$layout = "presentation";
	else
		$layout = "browsing";
	
	extract( shortcode_atts( array( 
		'issuu_pdf_id' => null, 
		'width' => $ipu_options['width'], 
		'height' => $ipu_options['height'], 
		'layout' => $layout,
		'backgroundColor' => $ipu_options['bgcolor'],
		'autoFlipTime' => 6000,
		'autoFlip' => ( isset( $ipu_options['autoflip'] ) && $ipu_options['autoflip'] == 1 ) ? 'true' : 'false', 
		'showFlipBtn' => ( isset( $ipu_options['show_flip_buttons'] ) && $ipu_options['show_flip_buttons'] == 1 ) ? 'true' : 'false', 
		'allowfullscreen' => ( isset( $ipu_options['allow_full_screen'] ) && $ipu_options['allow_full_screen'] == 1 ) ? 'true' : 'false' ),
		$atts ) ); ?>
	
	<div>
		<object style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px" >
			<param name="movie" value="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf?mode=embed&amp;backgroundColor=<?php echo $backgroundColor; ?>&amp;viewMode=<?php echo $layout; ?>&amp;showFlipBtn=<?php echo $showFlipBtn; ?>&amp;documentId=<?php echo $issuu_pdf_id; ?>&amp;autoFlipTime=6000&amp;autoFlip=<?php echo $autoFlip; ?>&amp;loadingInfoText=%2Fkpfrench&amp;et=1309515202394&amp;er=56" />
			<param name="allowfullscreen" value="<?php echo $allowfullscreen; ?>"/>
			<param name="menu" value="false"/>
			<embed src="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf" type="application/x-shockwave-flash" allowfullscreen="<?php echo $allowfullscreen; ?>" menu="false" style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px" flashvars="mode=embed&amp;backgroundColor=<?php echo $backgroundColor; ?>&amp;viewMode=<?php echo $layout; ?>&amp;autoFlipTime=6000&amp;autoFlip=<?php echo $autoFlip; ?>&amp;showFlipBtn=<?php echo $showFlipBtn; ?>&amp;documentId=<?php echo $issuu_pdf_id; ?>&amp;loadingInfoText=%2Fkpfrench&amp;et=1309515202394&amp;er=56" />
		</object>
	</div>
	 
	<?php
}