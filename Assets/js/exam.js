function showLoading() {
  document.getElementById('loading-overlay').style.display = 'flex';
}

function hideLoading() {
  document.getElementById('loading-overlay').style.display = 'none';
}

function scrollToTop() {
	$('html, body').animate({scrollTop : 0}, 400);
}

function showMessage(status, msg) {
	scrollToTop();
	
	let alertClass = (status == 200) ? 'alert-success' : 'alert-danger';
	
	$('.container, .container-fluid').first().prepend(`
		<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
			${msg}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	`);
	
	setTimeout(function() {
		$('.alert').alert('close');
	}, 5000);
}

function loadMathJax(){	
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src  = "../Assets/MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML";
	document.getElementsByTagName("head")[0].appendChild(script);
}

window.onscroll = function() {
	if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
		document.getElementById('toTopBtn').style.display = 'block';
	} else {
		document.getElementById('toTopBtn').style.display = 'none';
	}
};

function generateHtmlId(prefix, id) {
	function customEncode(str) {
		var output = '';
		var sum = 0;
		for (var i = 0; i < str.length; i++) {
			var code = str.charCodeAt(i);
			output += code.toString(36);
			sum += code;
		}
		return output + sum.toString(36);
	}

	var encodedPrefix = customEncode(prefix).substr(0, 6);
	var encodedId = customEncode(id.toString()).substr(0, 10);
	return 'gamefamorg_' + encodedPrefix + '_' + encodedId;
}

function initCKEditor(elementId) {        
	CKEDITOR.replace(elementId, {
		toolbar: [
			{ name: 'styles', items: ['FontSize', 'Font', 'TextColor', 'BGColor', 'SpecialChar'] },
			{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', 'Mathjax'] },
			{ name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
			{ name: 'tools', items: ['Link', 'Unlink', 'Image', 'Table', 'HorizontalRule', 'Maximize', 'Source'] }
		],
		removeButtons: 'Heading',
		height: 100,
		font_defaultLabel: '14px',
		defaultFontSize: '14px',
		contentsCss: 'body { font-size: 14px; }',
		extraPlugins: 'mathjax',
		mathJaxLib: '../Assets/MathJax/MathJax.js?config=TeX-AMS_HTML',
		// Cấu hình màu chữ và màu nền
		format_tags: 'p;h1;h2;h3;pre',
		format_p: { element: 'p', overrides: 'p', attributes: { 'style': 'font-size:14px;' } },
		format_h1: { element: 'h1', overrides: 'h1' },
		format_h2: { element: 'h2', overrides: 'h2' },
		format_h3: { element: 'h3', overrides: 'h3' },
		format_pre: { element: 'pre', attributes: { class: 'code' }, overrides: 'pre' },
	});		
}

// 'trick
function AjaxRequest(suri, stype, saction, sdata) {
    this.url = suri;
    this.type = stype;
    this.action = saction;
    this.data = sdata;
    this.successCallback = null;
    this.errorCallback = null;

    this.success = function(callback) {
        this.successCallback = callback;
        return this;
    };

    this.error = function(callback) {
        this.errorCallback = callback;
        return this;
    };

    this.send = function() {
        showLoading();
        $.ajax({
            url: this.url,
            type: this.type,
            data: {
                action: this.action,
                submittedData: JSON.stringify(this.data)
            },
            dataType: 'json',
            success: (response, textStatus, jqXHR) => {
                hideLoading();
                if (typeof this.successCallback === 'function') {
                    this.successCallback(response);
                }
            },
            error: (jqXHR, textStatus, errorThrown) => {
                hideLoading();
                if (typeof this.errorCallback === 'function') {
                    this.errorCallback({ jqXHR, textStatus, errorThrown });
                }
            }
        });
    };
}

function shareLink(relativePath) {
    // Tạo URL đầy đủ
    var fullUrl = new URL(relativePath, window.location.origin).href;
    
    // Kiểm tra xem trình duyệt có hỗ trợ Clipboard API không
    if (navigator.clipboard) {
        navigator.clipboard.writeText(fullUrl).then(function() {
            alert('Đã sao chép link chia sẻ');
        }).catch(function(err) {
            console.error('Không thể sao chép link: ', err);
        });
    } else {
        // Fallback cho các trình duyệt không hỗ trợ Clipboard API
        var textArea = document.createElement("textarea");
        textArea.value = fullUrl;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert('Đã sao chép link chia sẻ');
        } catch (err) {
            console.error('Không thể sao chép link: ', err);
        }
        document.body.removeChild(textArea);
    }
}

function scrollToClass(className) {
	const element = document.querySelector(`.${className}`);
	if (element) {
		const offset = 10;
		const elementPosition = element.getBoundingClientRect().top + window.scrollY;
		const offsetPosition = elementPosition - offset;
		window.scrollTo({
			top: offsetPosition,
			behavior: 'smooth'
		});
	} else {
		console.warn(`Không tìm thấy phần tử với class: ${className}`);
	}
}