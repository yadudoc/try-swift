var maxPage = -1;
var currentPage = 1;
var prev, next, topics; // buttons
var index; // examples
var maxPage;

var editor = ace.edit("editor");
editor.setFontSize('14px');
editor.setTheme("ace/theme/KatzenMilch");
editor.getSession().setMode("ace/mode/text");

function setVisiblePage(n) {
  var examples = document.getElementsByClassName('example');
  for(var i=0; i < examples.length; i++) {
    var page = examples[i];
    page.classList.add('hidden');
  }
  var visiblePage = document.getElementById('page-' + n);
  visiblePage.classList.remove('hidden');
}

// function page_convert(n) {
// 	return n<10? "0" + n: n;
// }

function hideFiles() {
    document.getElementById('outputs').innerHTML = "<option>File outputs</option>";
    $('#outputs').hide();
}

function check_buttons() {
	$('#topics').val(index[currentPage-1]);
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

$(document).ready(function () {
	$.get('scripts/index.txt', function(data) {	
		index = data.split("\n");
		maxPage = index.length;

		for (var i = 1; i <= maxPage; i++) {
			if (i < 10) i = "0" + i;
			var page = document.createElement('div');
			page.className = 'example hidden';
			page.id = 'page-' + i;
			document.getElementById('wrapright').appendChild(page);
			$("div#page-" + i).html(function() {
				var pageLoc = "scripts/" + i + "-page.html";
				return "<iframe src=\"" + pageLoc + "\" style=\"border-style: none; width: 100%; height: 1600px;\"></iframe>";
			});

			var source = document.createElement('div');
			source.id = 'source' + i;
			source.className = 'sourceToHide';
			document.getElementById('page-'+i).appendChild(source);
		}

		next = document.getElementById('nextButton');
		prev = document.getElementById('previousButton');
		reset = document.getElementById('resetButton');
		execute = document.getElementById('executeButton');
		topics = document.getElementById('topics');

		next.addEventListener('click', show_next);
		prev.addEventListener('click', show_prev);
		// reset.addEventListener('click', reset_text);
		// execute.addEventListener('click', execute_code);
		
		setVisiblePage(1);
		editor.setValue($('#source-' + 4).text(), -1);
		
	});
});





















