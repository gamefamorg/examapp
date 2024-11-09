function openHeader(evt, typeName) {    
  $(".tabcontent div.news.news-expanded").remove();    
  $(".tabcontent div.news").css("display", "none");
  $(".tabcontent div."+typeName+".news").css("display", "block");  
  $(".tabcontent").css("display", "block");  
}

function clearUrl()
{
	var url = window.location.href;
	var c = -1;
	c = url.indexOf('?');
	if(c != -1)
		window.history.pushState({}, "", url.split('?')[0]);
	else {
		c = url.indexOf('#');
		if(c != -1)
			window.history.pushState({}, "", url.split('#')[0]);		
	}
}

function openContent(evt, contentElement) {	
  var i, tabparent;

  $(".tabcontent div.news").css("display", "none");
  
  $.get( "../../../Contents/"+contentElement+".html", function(data) {    
	tabparent = $(".tabcontent");
	tabparent.html(data + tabparent.html());
	var offset = $(".thongcaohm").offset();
	$('html, body').animate({
		scrollTop: offset.top,
		scrollLeft: offset.left
	});	
	clearUrl();	
  });   
}

function openContentByUrl()
{
	var url = new URL(window.location.href);
	var c = url.searchParams.get("view");
	if(!c || c === "")
		return;
	var _html = document.body.innerHTML;
	var rs = _html.search(c);
	if(rs != -1){
		openContent(this, c);
	}
}

function openContentRank(url, idhtml){	
	openContent(this, idhtml);	
}

function copy2Clipboard(str)
{
	var el = document.createElement('textarea');
	el.value = str;
	document.body.appendChild(el);
	el.select();
	document.execCommand('copy');
	document.body.removeChild(el);
}

function copyShareLink(view)
{
	var linkUrl = window.location.origin + "?view=" + view;
	copy2Clipboard(linkUrl);
	alert("Link share của bạn đã được copied.");
}

function copyShareLink2(linkUrl)
{	
	copy2Clipboard(linkUrl);
	alert("Link share của bạn đã được copied.");
}