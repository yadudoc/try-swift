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

$(document).ready(function () {
	$.get('scripts/index.txt', function(data) {	
		index = data.split("\n");
		maxPage = index.length;

		for (var i = 1; i <= maxPage; i++) {
			var page = document.createElement('div');
			page.className = 'example hidden';
			page.id = 'page-' + i;
			if (i == 1) {
				document.getElementById('wrapright').appendChild(page);
				$("div#page-1").html(function() {
					source = "scripts/01-page.html";
					return "<iframe src=\"" + source + " style=\"border-style: none; width: 100%; height: 1600px;\"></iframe>";
				});
				setVisiblePage(1);
			}
		}
		
	});
});




