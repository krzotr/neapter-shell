<script src="http://code.jquery.com/jquery-1.6.3.min.js"></script>
<script>
$(function()
	{
		$( 'div#body' ).after( '<div id="status">&nbsp;</div>' );
		$( 'div#status' ).hide();

		$( 'form' ).submit( function()
			{
				var sCmd = $( 'input#cmd' ).val();

				if( ( $( 'input#cmd-send[value="Execute"]' ).length > 0 ) && ( sCmd.substring( 0, 5 ) != ':edit' ) && ( sCmd.substring( 0, 7 ) != ':upload' ) )
				{
					$( 'div#status' ).fadeIn( 250 );
					$.post( '', $( 'form' ).serialize(), function( sData )
						{
							$( 'pre#console' ).html( sData );
							$( 'div#status' ).fadeOut( 250 );
						}
					);
					return false;
				}
			}
		);
	}
);
</script>