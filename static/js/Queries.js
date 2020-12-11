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
	control.parentElement.querySelectorAll('.request-queries .query').forEach(function (queryElm) {
		expandOrCollapseQuery(queryElm, expand);
	});
};
var expandOrCollapseQuery = function (currentElm, expand) {
	var queryHeadElm = currentElm.querySelector('.query-head'),
		queryContentElm = currentElm.querySelector('.query-content'),
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
var copy = function (control, queryElmId) {
	var querySrc = document.getElementById(queryElmId),
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
		currentElm.className == 'query-line' ||
		currentElm.className == 'query-copy' ||
		currentElm.className == 'query-id'
	) {
		window.event = e;
		return;
	}
	while (true) {
		currentClassName = ' ' + String(currentElm.className) + ' ';
		if (currentClassName.indexOf(" query ") != -1) {
			success = true;
			break;
		}
		if (currentElm.parentElement == null) break;
		currentElm = currentElm.parentElement;
	}
	if (!success) return;
	expandOrCollapseQuery(
		currentElm,
		currentElm.querySelector('.query-content').className.indexOf('hidden') != -1
	);
	e.preventDefault();
	e.stopPropagation();
	return false;
});