var handleExpandOrCollapseQueries = function (control) {
	var expand = control.className.indexOf('expanded') == -1;
	control.parentElement.querySelectorAll('a.expand-collapse-control').forEach(function (controlElm) {
		expandOrCollapseQueries(controlElm, expand);
	});
	return void (0);
};
var expandOrCollapseQueries = function (control, expand) {
	var cls = control.className;
	if (expand) {
		control.className = cls + ' expanded';
	} else {
		control.className = cls.replace(/ expanded/g,'');
	}
	control.parentElement.querySelectorAll('.request-content .request-queries').forEach(function (queryElm) {
		expandOrCollapseQuery(queryElm, expand);
	});
};
var expandOrCollapseQuery = function (currentElm, expand) {
	var queryHeadElm = currentElm.querySelector('.request-queries-head'),
		queryContentElm = currentElm.querySelector('.request-queries-content'),
		headCls = queryHeadElm.className,
		contentCls = queryContentElm.className;
	if (expand) {
		queryHeadElm.className = headCls + ' hidden';
		queryContentElm.className = contentCls.replace(/ hidden/g, '');
	} else {
		queryHeadElm.className = headCls.replace(/ hidden/g, '');
		queryContentElm.className = contentCls + ' hidden';
	}
};
var editor = function (control, url) {
	var target = control.getAttribute('target');
	window.open(url, target || '_blank');
};
var copy = function (control, requestElmId) {
	var querySrc = document.getElementById(requestElmId),
		copyInput = document.getElementById('requests-copy2clipboard');
	copyInput.value = String(querySrc.textContent);
	copyInput.select();
	document.execCommand("copy");
};
document.addEventListener('click', function (e) {
	var currentElm = e.target,
		currentClassName = '',
		success = false;
	if (
		currentElm.className == 'request-queries-source' ||
		currentElm.className == 'request-queries-copy' ||
		currentElm.className == 'request-id'
	) {
		window.event = e;
		return;
	}
	while (true) {
		currentClassName = ' ' + String(currentElm.className) + ' ';
		if (currentClassName.indexOf(" request-queries ") != -1) {
			success = true;
			break;
		}
		if (currentElm.parentElement == null) break;
		currentElm = currentElm.parentElement;
	}
	if (!success) return;
	expandOrCollapseQuery(
		currentElm,
		currentElm.querySelector('.request-queries-content').className.indexOf('hidden') != -1
	);
	e.preventDefault();
	e.stopPropagation();
	return false;
});