{
	var helptexts = {
	"help":"main help here"
	,"count": "count help here"
	,"nextevent": "nextevent help here"
	};
	var countupdate = function(sl, c, lag) {
		return "this is a count update: " + sl + c + lag;
	};
	var correction = function(sl, lag) {
		return "this is a correction:" + sl + lag;
	};
	var nextevent = function(eventnumber) {
		return "next event should be set to " + eventnumber;
	}
}

Expression
 = Help
 / Count
 / Nextevent

Help
= "!help" _ arg:HelpArg? {
	return helptexts[arg||"help"];
}

HelpArg
 = "count"
 / "nextevent"

 _ "whitespace"
  = [ \t\n\r]*{return null;}
  
Count
 = slice:Slice _ ("@" _ )? cnt:CountSpec _ lag:ReportLag? {
 	return countupdate(slice, cnt, lag);
 }
 / slice:Slice _ "last" _ "flip"? _ lag:ReportLag {
 	return correction(slice, lag);
 }
 
Slice
 = [12345] "." [7689] {return text();}
 
CountSpec
 = Digits / "flip"
 
Digits
 = [0-9]+ {
 	var digits = parseInt(text(), 10);
    if (digits > 0 && digits < 999) {
 		return digits;
    }
 }
 
ReportLag 
 = (Digits "h" Digits "m"
 / Digits "h" Digits
 / Digits "m"
 / Digits "h" ){
  return text();
 }

Nextevent
= "next event" _ eventnum:Digits {return nextevent(eventnum);}