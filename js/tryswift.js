var maxPage = -1;
var currentPage = 1;
var prev, next, topics;

var index = [
	"blah",
	"Introduction",
	"Hello World!",
	"Foreach",
	"Multiple apps",
	"Multi-stage workflows"
];

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
    $('#outputs').hide();
}

function check_buttons() {
	$('#topics').val(index[currentPage]);
	if (currentPage == maxPage) {
	    next.setAttribute('disabled', true);
	    prev.removeAttribute('disabled');
	} else if (currentPage == 1) {
	    prev.setAttribute('disabled', true);
	    next.removeAttribute('disabled');
	} else {
		prev.removeAttribute('disabled');
		next.removeAttribute('disabled');
	}
}


function show_next() {
	currentPage++;
	if (currentPage <= maxPage) {
		setVisiblePage(currentPage);
		editor.setValue($('#source-' + currentPage).text(), -1);
		document.getElementById('swiftOutput').innerHTML = "";
		hideFiles();
		check_buttons();
	}
}

function show_prev() {
	currentPage--;
	if (currentPage > 0) {
		setVisiblePage(currentPage);
		editor.setValue($('#source-' + currentPage).text(), -1);
		document.getElementById('swiftOutput').innerHTML = "";
		hideFiles();
		check_buttons();
	} 
}


var editor = ace.edit("editor");
editor.setFontSize('14px');
editor.setTheme("ace/theme/KatzenMilch");
editor.getSession().setMode("ace/mode/text");

function reset_text() {
	editor.setValue($('#source-' + currentPage).text(), -1);
}

function execute_code() {
	var sourceCode = editor.getValue();
	if (!sourceCode) {
		alert("Source text is empty!");
		return false;
	}
	next.setAttribute('disabled', true);
	prev.setAttribute('disabled', true);
	topics.setAttribute('disabled', true);
	document.getElementById('swiftOutput').innerHTML = "";
	hideFiles();

	$.ajax({
		type: 'POST',
		url: 'tryswift.php',
		data:{'source': sourceCode},
	})
	.done(function (data) {
		var urlArray = data.split("\n");
		tailf(urlArray[0], "#swiftOutput");
		$.post("getfiles.php", {dir: urlArray[1]}).done(function(filedata) {
			$('#outputs').append(filedata);
			var x = document.getElementById("outputs");
			$('#outputs').show();
			check_buttons();
			topics.removeAttribute('disabled');
		    });
	    });
	
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
	topics = document.getElementById('topics');

	next.addEventListener('click', show_next);
	prev.addEventListener('click', show_prev);
	reset.addEventListener('click', reset_text);
	execute.addEventListener('click', execute_code);
	

	$(document).on("change", "#topics", function() {
		var selectedTopic = $('#topics').val();
		var page_num = index.indexOf(selectedTopic);
		currentPage = page_num;
		setVisiblePage(currentPage);
		editor.setValue($('#source-' + currentPage).text(), -1);
		document.getElementById('swiftOutput').innerHTML = "";
		hideFiles();
		check_buttons();
		
	});

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




















