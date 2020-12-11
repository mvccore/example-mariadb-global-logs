(function () {
	var forms = document.getElementsByTagName('form'),
		form;
	for (var i = 0, l = forms.length; i < l; i += 1) {
		form = forms[i];
		if (form.className.indexOf('delete') > -1) {
			form.onsubmit = function (e) {
				e = e || window.event;
				if (!confirm("Are you sure?")) {
					if (e.preventDefault) e.preventDefault();
					return false;
				}
			}
		}
	}
})();

(function () {
	var progressElms = document.querySelectorAll('td.log-processing'),
		fileSizeElms = document.querySelectorAll('td.log-file-size'),
		linksElms = document.querySelectorAll('td.log-link'),
		urlElm = document.getElementById('logs-processing-progresses-url'),
		rawLogFileId = '',
		logFileId = 0,
		progressElm,
		logs = {};
	for (var i = 0, l = progressElms.length; i < l; i += 1) {
		progressElm = progressElms[i];
		rawLogFileId = progressElm.getAttribute('data-id');
		rawLogFileId = String(rawLogFileId).trim();
		if (rawLogFileId.length === 0) continue;
		logFileId = parseInt(rawLogFileId, 10);
		logs[logFileId] = [progressElm, fileSizeElms[i], linksElms[i]];
	}
	var intId = null;
	var req = function () {
		var logsIds = Object.keys(logs);
		if (logsIds.length === 0) return clearInterval(intId);
		Ajax.load({
			url: urlElm.value,
			method: 'POST',
			data: {
				logs_files_ids: Object.keys(logs).join(',')
			},
			success: function (response, statusCode) {
				var dataItem,
					elms
				if (!response.success) return;
				for (var logIdStr in response.data) {
					dataItem = response.data[logIdStr];
					elms = logs[logIdStr];
					if (dataItem.done) 
						delete logs[logIdStr];
					elms[0].innerHTML = dataItem.progress;
					elms[1].innerHTML = dataItem.fileSize;
					if (dataItem.linkCode)
						elms[2].innerHTML = dataItem.linkCode;
				}
			}
		});
	};
	if (Object.keys(logs).length > 0) 
		intId = setInterval(req, 3000);
})();