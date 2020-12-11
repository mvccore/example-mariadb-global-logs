var mark = function (control) {
	Ajax.get(
		control.getAttribute('data-url'),
		{ mark: control.getAttribute('data-mark') },
		function (data, statusCode, xhr, requestId, url, type) {
			if (statusCode === 200 && data.success) {
				control.setAttribute('data-mark', data.mark ? '1' : '0');
				control.innerHTML = data.mark ? 'unmark' : 'mark';
				var row = control.parentElement.parentElement;
				if (data.mark) {
					row.className = 'marked';
				} else {
					row.className = '';
				}
			}
		},
		'json'
	);
};