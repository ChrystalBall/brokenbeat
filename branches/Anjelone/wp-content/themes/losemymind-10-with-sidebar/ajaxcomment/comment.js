// JScript File
//short for document.getElementById
function $() {
	if (arguments.length == 1) return get$(arguments[0]);
	var elements = [];
	$c(arguments).each(function(el){
		elements.push(get$(el));
	});
	return elements;

	function get$(el){
		if (typeof el == 'string') el = document.getElementById(el);
		return el;
	}
}

function $c(array){
	var nArray = [];
	for (i=0;el=array[i];i++) nArray.push(el);
	return nArray;
}
// get parameters of the comment form
function getParams(){
	var s = "comment="+encodeURIComponent($('comment').value), i;
	var f = $('commentform').getElementsByTagName('input');
	for(i=0;i<f.length;i++)	s+="&"+f[i].name+"="+encodeURIComponent(f[i].value);
	return s;
}
// move comment form to proper position to reply exist comments.
function moveForm(a) {
	var id		= $('comment_reply_ID'),
		disp	= $('reRoot').style,
		form	= $('commentform'),
		e		= $('comment-'+a);
	var es = $('commentform').getElementsByTagName('*');
	
	form.parentNode.removeChild(form);
	if (a) {
		var c = e.getElementsByTagName('ul')[0];
		if (c)
			e.insertBefore(form, c);
		else
			e.appendChild(form);
	} else {
		$('cmtForm').appendChild(form);
	}
	disp.display = (a ? 'inline' : 'none');
	if (id) id.value = (a ? a : 0);
	return;
}

//the function to get an ajax instance
function getXMLInstant()
{
	if (window.XMLHttpRequest) 
	{ // Mozilla, Safari,...
		xx = new XMLHttpRequest();
		if (xx.overrideMimeType) 
		{
			xx.overrideMimeType('text/xml');
		}
	} 
	else if (window.ActiveXObject) { // IE
		try {
			xx = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				xx = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
	if (!xx) {
		alert('Giving up :( Cannot create an XMLHTTP instance');
		return false;
	}
	
	return xx;
}
//the function to send comment
function AjaxSendComment()
{
    var gi = getXMLInstant(), r = $('comment_reply_ID').value, t, c, now = parseInt(Math.random()*1000);
	
    if (r == 0) {
		t = 'commentlist';
		c = $(t);
	} else {
		t = 'comment-'+r;
		var u = $(t).getElementsByTagName('ul')[0];
		if (u) {
			c = u;
		} else {
			c = document.createElement('ul');
			$(t).appendChild(c);
		}
	}
	// backup comment in case of Comment fail
	var content = $("comment").value;
	var author = "admin", email="a@b.c";
	var temp = content;
	if($("author"))author = $("author").value;
	if($("email"))email = $("email").value;
	//check the author and content input area is fixed
	if(content == "" || (needemail=="" && (author == "" || email == ""))) {
	    if($("nm")) {
	        $("nm").style.color="red";
	        if(author =="") $("nm").innerHTML = "name is necessory";
			else if(email=="") $("nm").innerHTML = "email is necessory";
	        else $("nm").innerHTML = "comment can not be empty";
	    }
	    return true;
	}
	//add comment to local comment list
	content = content.replace(/\r\n\r\n/g, "</p><p>");
	content = content.replace(/\r\n/g, "<br />");
	content = content.replace(/\n\n/g, "</p><p>");
	content = content.replace(/\n/g, "<br />");
	var dateObj = new Date();
	c.innerHTML = c.innerHTML + "<li id='newComment"+now+"'><div class=\"commenthead\">At "+dateObj.toLocaleString()+", <span class=\"author\">"+ author+ "</span> said: </div><div class=\"body\"><p>"  + content+ "</p></div><div class='meta'>submitting...</div></div></li>";
	
	//the state function of ajax 
	gi.onreadystatechange = function()
    {
        if (gi.readyState == 4) {
            if (gi.status == 200) {
                $('newComment'+now).innerHTML=gi.responseText;
				if(gi.responseText.search(/Slow down cowboy/) > -1 || gi.responseText.search(/Duplicate comment detected/) > -1)
					$('comment').value = temp;
            } 
            else {
                alert('Failed to add your comment. ');
				$('comment').value = temp;
            }
        }
    }
	
	//send form by ajax
	var parameters=getParams(); // get parameters of form
    gi.open('POST', blogurl+"/wp-content/plugins/ajaxcomment/comments-ajax.php", true);
    gi.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    gi.setRequestHeader("Content-length", parameters.length);
    gi.setRequestHeader("Connection", "close");
    gi.send(parameters);
    
	//after send form, move the comment form to original position.
    moveForm(0);
    $('comment').value = '';
    
    return true;
}
// Get content by AJAX
function ajaxGet(url, SP){
	var gp = getXMLInstant();
	co = $(SP);
	co.innerHTML = "<image src ='http://zhiqiang.org/blog/images/working.gif'/> Loading...";
	
	gp.onreadystatechange = function(){
        if (gp.readyState == 4) {
        	if (gp.status == 200) {
            	co.innerHTML = gp.responseText;
			}else {
                alert('There was a problem with the request.');
            }
        }
    }
    
	gp.open('GET', blogurl+url, true);
    gp.send(null);
}