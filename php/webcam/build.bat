del jscam.swf
compiler\swfmill\swfmill.exe simple src/jscam.xml ./jscam.swf
compiler\mtasc\mtasc.exe -v -swf jscam.swf -main jscam.as -version 8 -cp src -cp compiler\mtasc\std
pause