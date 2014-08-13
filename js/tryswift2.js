var maxPage = -1;
var currentPage = 1;
var prev, next, topics; // buttons
var index; // examples
var maxPage;

function setVisiblePage(n) {
  var examples = document.getElementsByClassName('example');
  for(var i=0; i < examples.length; i++) {
    var page = examples[i];
    page.classList.add('hidden');
  }
  var visiblePage = document.getElementById('page-' + n);
  visiblePage.classList.remove('hidden');
}

var editor = ace.edit("editor");
editor.setFontSize('14px');
editor.setTheme("ace/theme/KatzenMilch");
editor.getSession().setMode("ace/mode/text");

$(document).ready(function () {
	$.get('scripts/index.txt', function(data) {	
		index = data.split("\n");
		maxPage = index.length;

		for (var i = 1; i <= maxPage; i++) {
			var page = document.createElement('div');
			page.className = 'example hidden';
			page.id = 'page-' + i;
	
			document.getElementById('wrapright').appendChild(page);
			$("div#page-1").html(function() {
				source = "scripts/01-page.html";
				return "<iframe src=\"" + source + "\" style=\"border-style: none; width: 100%; height: 1600px;\"></iframe>";
			});

			var source = document.createElement('div');
			source.id = 'source' + i;
			source.className = 'sourceToHide';
			document.getElementById('page-'+i).appendChild(source);
			// editor.setValue($('#source-' + i).text(), -1);
		}

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
		});

		setVisiblePage(1);
		editor.setValue($("#source-1").text(), -1);

	});
});
























