function library_confirm_msg(url,msg)
    {
        input_box=confirm(msg);
        if (input_box==true)
        {
            window.location.replace(url);
        }
    }


function uploadWindowstart() {
	$('uploadwindowing').style.visibility = 'visible';
}

function uploadWindowload() {
	$('uploadwindowing').style.visibility = 'hidden';
	var str = $('uploadattachframe').contentWindow.document.body.innerHTML;
	if(str == '') return;
	var arr = str.split('|');
	if(arr[0] == 'DISCUZUPLOAD' && arr[2] == 0) {
		UPLOADWINRECALL(arr[3], arr[5], arr[6]);
		hideWindow('upload', 0);
	} else {
		var sizelimit = '';
		if(arr[7] == 'ban') {
			sizelimit = '(附件类型被禁止)';
		} else if(arr[7] == 'perday') {
			sizelimit = '(不能超过 ' + arr[8] + ' 字节)';
		} else if(arr[7] > 0) {
			sizelimit = '(不能超过 ' + arr[7] + ' 字节)';
		}
		showError(STATUSMSG[arr[2]] + sizelimit);
	}
	if($('attachlimitnotice')) {
		ajaxget('forum.php?mod=ajax&action=updateattachlimit&fid=' + fid, 'attachlimitnotice');
	}
}

function uploadWindow(recall, type) {
	var type = isUndefined(type) ? 'image' : type;
	UPLOADWINRECALL = recall;
	showWindow('upload', 'forum.php?mod=misc&action=upload&fid=' + fid + '&type=' + type, 'get', 0, {'zindex':601});
}