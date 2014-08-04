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

function show_next() {
	currentPage++;
	if (currentPage <= maxPage) {
		setVisiblePage(currentPage);
		editor.setValue($('#source-' + currentPage).text(), -1);
		prev.removeAttribute('disabled');
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
		if (currentPage == 1) {
			prev.setAttribute('disabled', true);
		}
	} 
}


var editor = ace.edit("editor");
editor.setFontSize('12px');
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

	$.ajax({
		type: 'POST',
		url: 'tryswift.php',
		data:{'source': sourceCode},
	})
	.done(function (data) {
		var urlArray = data.split("\n");
		tailf(urlArray[0], "#swiftOutput");
	});

	// $.post("tryswift.php", { source: sourceCode }).done(function(data) {
	// 	alert("done!");
	// })

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

	setVisiblePage(1);
	editor.setValue($("#source-1").text(), -1);
})




















