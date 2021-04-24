del Classes.swf
del Shoot.swf
..\mtasc-1.14\mtasc.exe *.as -version 8 -wimp -swf Classes.swf -header 1:1:30
..\swfmill-0.2.12-win32\swfmill.exe simple Shoot.xml Shoot.swf
pause