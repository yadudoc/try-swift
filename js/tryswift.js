var maxPage = -1;
var currentPage = 1;
var prev, next;

function setVisiblePage(n) {
  var examples = document.getElementsByClassName('example');
  for(var i=0; i < examples.length; i++) {
    var page = examples[i];
    page.classList.add('hidden');
  }
  var visiblePage = document.getElementById('page-' + n);
  visiblePage.classList.remove('hidden');
}

function hideFiles() {
    document.getElementById('outputs').innerHTML = "<option>File outputs</option>";
}

function show_next() {
	currentPage++;
	if (currentPage <= maxPage) {
		setVisiblePage(currentPage);
		editor.setValue($('#source-' + currentPage).text(), -1);
		prev.removeAttribute('disabled');
		document.getElementById('swiftOutput').innerHTML = "";
		hideFiles();
		if (currentPage == maxPage) {
			next.setAttribute('disabled', true);
		}
	}
}

function show_prev() {
	currentPage--;
	if (currentPage > 0) {
		setVisiblePage(currentPage);
		editor.setValue($('#source-' + currentPage).text(), -1);
		next.removeAttribute('disabled');
		document.getElementById('swiftOutput').innerHTML = "";
		hideFiles();
		if (currentPage == 1) {
			prev.setAttribute('disabled', true);
		}
	} 
}


var editor = ace.edit("editor");
editor.setFontSize('16px');
editor.setTheme("ace/theme/KatzenMilch");
editor.getSession().setMode("ace/mode/text");
// editor.setShowPrintMargin(false);
// editor.setHighlightActiveLine(false);
// editor.setDisplayIndentGuides(false);

function reset_text() {
	editor.setValue($('#source-' + currentPage).text(), -1);
}

function execute_code() {
	var sourceCode = editor.getValue();
	if (!sourceCode) {
		alert("Source text is empty!");
		return false;
	}
	//alert("execute clicked. in tryswift.js 1 ");
	$.ajax({
		type: 'POST',
		url: 'tryswift.php',
		data:{'source': sourceCode},
	})
	.done(function (data) {
		
		var urlArray = data.split("\n");
		// alert(urlArray[0]);
		tailf(urlArray[0], "#swiftOutput");
		
		$.ajax({
			type: 'POST',
			    url: 'getfiles.php',
			    data:{'dir': urlArray[1]},
			    })
		  .done(function (filedata) {
			  $('#outputs').append(filedata);
			  var x = document.getElementById("outputs");
			  if (x.length > 0) {
			      x.remove(x.length-1);
			  }
			});
	});
	//alert("execute clicked. in tryswift.js 2");
	// $.post("tryswift.php", { source: sourceCode }).done(function(data) {
	// 	alert("done!");
	// })

}

function popupwindow(url, name, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    return window.open(url, name, 'width='+w+', height='+h+', top='+top+', left='+left);
} 

document.addEventListener('DOMContentLoaded', function() {

	maxPage = document.getElementsByClassName('example').length;

	next = document.getElementById('nextButton');
	prev = document.getElementById('previousButton');
	reset = document.getElementById('resetButton');
	execute = document.getElementById('executeButton');

	next.addEventListener('click', show_next);
	prev.addEventListener('click', show_prev);
	reset.addEventListener('click', reset_text);
	execute.addEventListener('click', execute_code);

	$(document).on("change", "#outputs", function(){
		var selected = $('#outputs').val();
		if (selected != "File outputs") {
		popupwindow($('#outputs').val(), '', 800, 600); 
		}
		// $('#swiftOutput').load($("#outputs").val());
	    });

	setVisiblePage(1);
	editor.setValue($("#source-1").text(), -1);
})




















