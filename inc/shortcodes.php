<?php
add_shortcode('pdf','issuu_pdf_embeder'); // two shortcodes referencing the same callback
function issuu_pdf_embeder( $atts, $content = null ) {
	
	global $ipu_options;
	
	extract( shortcode_atts( array( 'issuu_pdf_id' => null, 'width' => $ipu_options['width'], 'height' => $ipu_options['height'], 'allowfullscreen' => ( $ipu_options['allow_full_screen'] == 1 ) ? 'true' : 'false' ), $atts ) ); ?>
	
	<div>
		<object style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px" >
			<param name="movie" value="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf?mode=embed&amp;layout=http%3A%2F%2Fskin.issuu.com%2Fv%2Flight%2Flayout.xml&amp;showFlipBtn=true&amp;documentId=<?php echo $issuu_pdf_id; ?>&amp;docName=kpfrench&amp;username=BenjaminDev&amp;loadingInfoText=%2Fkpfrench&amp;et=1309515202394&amp;er=56" />
			<param name="allowfullscreen" value="<?php echo $allowfullscreen; ?>"/>
			<param name="menu" value="false"/>
			<embed src="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf" type="application/x-shockwave-flash" allowfullscreen="<?php echo $allowfullscreen; ?>" menu="false" style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px" flashvars="mode=embed&amp;layout=http%3A%2F%2Fskin.issuu.com%2Fv%2Flight%2Flayout.xml&amp;showFlipBtn=true&amp;documentId=<?php echo $issuu_pdf_id; ?>&amp;docName=kpfrench&amp;username=BenjaminDev&amp;loadingInfoText=%2Fkpfrench&amp;et=1309515202394&amp;er=56" />
		</object>
	</div>
	
	<?php
}