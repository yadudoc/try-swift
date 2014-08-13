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
		
		setVisiblePage(1);
		editor.setValue($('#source-' + 01).text(), -1);
		
	});
});




