<?php
// doexam.php

require_once '../apiCaller.php';
require_once '../config.php';
require_once 'langVN.php';
ini_set('session.gc_maxlifetime', 10800);
session_set_cookie_params(10800);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$udat = isset($_SESSION['usrdatav']) ? $_SESSION['usrdatav'] : null;
$userId = $udat != null && isset($udat['user_id']) ? $udat['user_id'] : 0;
$isAdmin = $udat != null && isset($udat['is_admin']) ? $udat['is_admin'] : false;
$hasPremium = $udat != null && isset($udat['has_premium']) ? $udat['has_premium'] : false;
$hasAdv = !($isAdmin || $hasPremium);
$apiCaller = new ApiCaller($baseAPI, null);
$examData = null;
$sessionId = "";
$isOwner = true;
$isOwnerMsg = "Lỗi không xác định.";
$examType = "TN"; //có 2 type: TN (trắc nghiệm), TL (tự luận)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    	    
    // Decode JSON
    $submittedData = json_decode($_POST['submittedData'], true);
    
    $sessionId = isset($submittedData['session_id']) ? $submittedData['session_id'] : "";	
	
	$submittedData['user_id'] = $userId;
	$submittedData['session_id'] = $sessionId;
    $response = $apiCaller->call('POST', '/api/SApiService/SubmitExamTest', $submittedData);
	$data = $response['body'];	
    
    if (is_string($data)) {
        $data = json_decode($data, true);
    }
	
    $statusCode = isset($data['status_code']) ? $data['status_code'] : 0;
	
	header('Content-Type: application/json');
	if($statusCode != 200) {		
        echo json_encode([
            'success' => false,
            'message' => $data['message'],
            'status_code' => $statusCode
        ]);
	} else {
		echo json_encode([
            'success' => true,
            'message' => 'Bài làm của bạn đã được nộp thành công!',
            'status_code' => $statusCode,
            'redirectUrl' => "eview.php?session_id=" . $sessionId
        ]);
	}
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sid'])){
	$sessionId = $_GET['sid'];    
	$postData = [        
        'pickup_id' => $userId,
        'req_ip' => $_SERVER['REMOTE_ADDR'],
        'req_device' => '',
		'session_id' => $sessionId
    ];
	
	$response = $apiCaller->call('POST', '/api/SApiService/LoadExamTest', $postData);
	$examData = $response['body'];	
	
	if (is_string($examData)) {
        $examData = json_decode($examData, true);
    }
	
	if(isset($examData['type']))
		$examType = $examData['type'];
	
	$statusCode = isset($examData['status_code']) ? $examData['status_code'] : 0;
	if($statusCode != 200)
	{
		$isOwner = false;
		$isOwnerMsg = $examData['message'];		
	}
}

$examDataJson = json_encode($examData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $LANGpageTitle;?></title>	
	<link rel="icon" type="image/ico" href="https://gamefam.org/favicon.ico">
	<link rel="stylesheet" href="../Assets/css/bootstrap-4.3.1.min.css">
	<link rel="stylesheet" href="../Assets/css/font-awesome-5.15.3.css">
	<link rel="stylesheet" href="../Assets/css/exam.css">
    <style>	
        body {
            background-color: #f8f9fa;
            padding-bottom: 60px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        #examHeader {
            background-color: #007bff;
            color: white;
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        #questionMatrix, #mobileMatrix {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .matrix-item {
            width: 40px;
            height: 40px;
            margin: 5px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border: 2px solid #007bff;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        .matrix-item:hover {
            background-color: #e6f2ff;
        }
        .matrix-item.answered {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        #scrollToTop {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
        }
        .collapsible-header {
            cursor: pointer;
        }
        .collapsible-header:after {
            content: '\25BC';
            float: right;
            transition: transform 0.3s ease;
        }
        .collapsible-header.collapsed:after {
            transform: rotate(-90deg);
        }
        @media (min-width: 768px) {
            #questionMatrix {
                position: sticky;
                top: 100px;
            }
        }
        @media (max-width: 767px) {
            #mobileQuestionMatrix {
                order: 2;
            }
            #questionContainer {
                order: 3;
            }
        }		
		.form-check-label, .form-group label {
			display: flex;
			align-items: center;
		}		
		img {
			width: 100%;
		}
		.form-check-input[type="radio"],
		.form-check-input[type="checkbox"] {
		  transform: scale(1.5);
		}
    </style>	
</head>
<body>
<?php if($isOwner): ?>
    <div id="examHeader">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-0" id="examName">Bài kiểm tra Toán học</h1>
                </div>
                <div class="col-md-6 text-md-right">
                    <span id="timer" class="h4"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="card mb-3">
            <div class="card-header collapsible-header" data-toggle="collapse" data-target="#examInfo">
                Thông tin bài kiểm tra
            </div>
            <div id="examInfo" class="collapse show">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Thời gian:</strong> <span id="examDuration"></span> phút</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Điểm đạt:</strong> <span id="passScore"></span> điểm</p>
                        </div>
						<div class="col-md-6">
                            <p><strong>Mô tả:</strong> <span id="examDescription"></span></p>
                        </div>
                    </div>
                    <p><strong>Dặn dò:</strong> <span id="noticeWarn">Copy lại đường link làm bài để có thể mở lại, thi xong nhấn nút nộp bài ở phía cuối.</span></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9">
                <div id="mobileQuestionMatrix" class="d-md-none">
                    <div class="card mb-3">
                        <div class="card-body" id="mobileMatrix"></div>
                    </div>
                </div>
                <form id="examForm">
                    <div id="questionContainer"></div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">Nộp bài</button>
                </form>
            </div>
            <div class="col-md-3 d-none d-md-block">
                <div id="questionMatrix"></div>
            </div>
        </div>
    </div>
    
<?php else: ?>
	<div class="alert alert-warning mt-3">
		<?php echo $isOwnerMsg;?>
	</div>
<?php endif; ?>	
	
	<button id="toTopBtn" class="btn btn-primary" onclick="scrollToTop()">
		<i class="fas fa-arrow-up"></i>
	</button>
	
	<div id="loading-overlay">
	  <div class="spinner"></div>
	</div>
	
	<script src="../Assets/js/jquery-3.3.1.min.js"></script>
	<script src="../Assets/js/popper-1.14.7.min.js"></script>
	<script src="../Assets/js/bootstrap-4.3.1.min.js"></script>	
	<script src="../Assets/ckeditor/ckeditor.js"></script>
	<script src="../Assets/js/exam.js"></script>
<?php if($hasAdv){?>	
	<script src="../Assets/js/adv.js"></script>
<?php }?>
    <script>   		
        function renderQuestions() {
            const container = document.getElementById('questionContainer');
            examData.questions.forEach((question, index) => {
                const htmlQuestionId = generateHtmlId('q', question.id);
                const questionDiv = document.createElement('div');
                questionDiv.className = 'card mb-4';
                questionDiv.id = htmlQuestionId;
                questionDiv.innerHTML = `
                    <div class="card-body">
                        <h5 class="card-title">Câu hỏi ${index + 1} (${question.score} điểm)</h5>
                        <p class="card-text">${question.name}</p>
                        ${renderAnswers(question, htmlQuestionId)}
                    </div>
                `;
                container.appendChild(questionDiv);
				if (examType === 'TL') {
					const htmlAnswerId = `${htmlQuestionId}_answer`;
					initCKEditor(htmlAnswerId);					
				}
            });
        }

        function renderAnswers(question, htmlQuestionId) {
            if (examType === "TN") {							
				switch (question.type) {
					case 'single':
						return question.answers.map(answer => {
							const htmlAnswerId = generateHtmlId(`${question.id}_a`, answer.id);
							return `
								<div class="form-check">
									<input class="form-check-input" type="radio" name="${htmlQuestionId}" id="${htmlAnswerId}" value="${answer.id}" onchange="updateQuestionStatus('${htmlQuestionId}')">
									<label class="form-check-label" for="${htmlAnswerId}">${answer.name}</label>
								</div>
							`;
						}).join('');
					case 'multi':
						return question.answers.map(answer => {
							const htmlAnswerId = generateHtmlId(`${question.id}_a`, answer.id);
							return `
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="${htmlQuestionId}" id="${htmlAnswerId}" value="${answer.id}" onchange="updateQuestionStatus('${htmlQuestionId}')">
									<label class="form-check-label" for="${htmlAnswerId}">${answer.name}</label>
								</div>
							`;
						}).join('');
					case 'text':
						return question.answers.map((answer, index) => {
							const htmlAnswerId = generateHtmlId(`${question.id}_a`, answer.id);
							const labelText = question.answers.length > 1 ? `Đáp án ${index + 1}:` : 'Câu trả lời:';
							return `
								<div class="form-group d-flex align-items-center mb-2">
									<label for="${htmlAnswerId}" class="me-2 answer-number" style="min-width: 100px;">${labelText}</label>
									<input type="text" class="form-control" name="${htmlAnswerId}" id="${htmlAnswerId}" placeholder="nhập câu trả lời" oninput="updateQuestionStatus('${htmlQuestionId}')">
								</div>
							`;
						}).join('');
					default:
						return '';
				}
			} else if (examType === "TL") {
				const htmlAnswerId = `${htmlQuestionId}_answer`;
				return `<textarea id="${htmlAnswerId}" name="${htmlAnswerId}"></textarea>`;
			}
        }

        function renderQuestionMatrix() {
            const matrices = [document.getElementById('questionMatrix'), document.getElementById('mobileMatrix')];
            const matrixContent = `
                <h5 class="mb-3">Danh sách câu hỏi</h5>
                ${examData.questions.map((question, index) => {
                    const htmlQuestionId = generateHtmlId('q', question.id);
                    return `
                        <div class="matrix-item" data-question="${htmlQuestionId}" onclick="scrollToQuestion('${htmlQuestionId}')">${index + 1}</div>
                    `;
                }).join('')}
            `;
            matrices.forEach(matrix => {
                if (matrix) matrix.innerHTML = matrixContent;
            });
        }

        function scrollToQuestion(htmlQuestionId) {
            const questionElement = document.getElementById(htmlQuestionId);
			const offset = 30;
			const elementPosition = questionElement.getBoundingClientRect().top + window.scrollY;
			const offsetPosition = elementPosition - offset;
			window.scrollTo({
				top: offsetPosition,
				behavior: 'smooth'
			});            
        }

        function updateQuestionStatus(htmlQuestionId) {
            const question = examData.questions.find(q => generateHtmlId('q', q.id) === htmlQuestionId);
            let isAnswered = false;

			if (examType === "TN") {
				if (question.type === 'single') {
					isAnswered = document.querySelector(`input[name="${htmlQuestionId}"]:checked`) !== null;
				} else if (question.type === 'multi') {
					isAnswered = document.querySelectorAll(`input[name="${htmlQuestionId}"]:checked`).length > 0;
				} else if (question.type === 'text') {
					isAnswered = question.answers.some(answer => {
						const htmlAnswerId = generateHtmlId(`${question.id}_a`, answer.id);
						return document.querySelector(`input[name="${htmlAnswerId}"]`).value.trim() !== '';
					});
				}
			} else if (examType === "TL") {
				const htmlAnswerId = `${htmlQuestionId}_answer`;
				isAnswered = CKEDITOR.instances[htmlAnswerId].getData().trim() !== '';
			}
            
            const matrixItems = document.querySelectorAll(`.matrix-item[data-question="${htmlQuestionId}"]`);
            matrixItems.forEach(item => {
                if (isAnswered) {
                    item.classList.add('answered');
                } else {
                    item.classList.remove('answered');
                }
            });
        }
		
		(function(){var _0x34b1=['addEventListener','copy','preventDefault'];(function(_0x37d1dc,_0x1d0173){var _0x5a5b76=function(_0x3da665){while(--_0x3da665){_0x37d1dc['push'](_0x37d1dc['shift']());}};_0x5a5b76(++_0x1d0173);}(_0x34b1,0x1d3));var _0x5a5b=function(_0x37d1dc,_0x1d0173){_0x37d1dc=_0x37d1dc-0x0;var _0x5a5b76=_0x34b1[_0x37d1dc];return _0x5a5b76;};(function(){var _0x3da665=function(){var _0x2f3438=function(_0x4cd0c5){_0x4cd0c5[_0x5a5b('0x0')]();};document[_0x5a5b('0x1')](_0x5a5b('0x2'),_0x2f3438);setInterval(function(){if(!document['oncopy']){document[_0x5a5b('0x1')](_0x5a5b('0x2'),_0x2f3438);}},0x3e8);};_0x3da665();})();})();

        function startTimer(duration) {
            let timer = duration * 60;
            const timerDisplay = document.getElementById('timer');
            
            const countdown = setInterval(() => {
                const minutes = parseInt(timer / 60, 10);
                const seconds = parseInt(timer % 60, 10);

                timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                if (--timer < 0) {
                    clearInterval(countdown);
                    alert('Hết giờ!');
                    submitExam();
                }
            }, 1000);
        }

        function submitExam() {
			const formData = new FormData(document.getElementById('examForm'));
			const submittedData = {
				user_id: examData.user_id,
				id: examData.id,
				name: examData.name,
				session_id: sessionId,
				questions: examData.questions.map(question => {
					const htmlQuestionId = generateHtmlId('q', question.id);
					const htmlAnswerPrefix = `${question.id}_a`;
					let userAnswers;
					if (examType === "TN") {
						// Giữ nguyên logic cho TN
						if (question.type === 'single') {
							userAnswers = [formData.get(htmlQuestionId)];
						} else if (question.type === 'multi') {
							userAnswers = formData.getAll(htmlQuestionId);
						} else if (question.type === 'text') {
							userAnswers = question.answers.map(answer => 
								formData.get(generateHtmlId(htmlAnswerPrefix, answer.id))
							);
						}
					} else if (examType === "TL") {
						const htmlAnswerId = `${htmlQuestionId}_answer`;
						userAnswers = [CKEDITOR.instances[htmlAnswerId].getData()];
					}					
					return {
						id: question.id,
						type: question.type,
						score: question.score,
						name: question.name,
						answers: question.answers.map((answer, index) => ({
							id: answer.id,
							name: examType === "TL" ? userAnswers[0] : (question.type === 'text' ? 
								userAnswers[index] || '' : 
								answer.name),
							is_correct: (examType === "TL" || question.type === 'text') ? 
								true :
								userAnswers.includes(answer.id.toString())
						}))
					};					
				})
			};
			//console.log(submittedData);
			new AjaxRequest('doexam.php', 'POST', 'submit', submittedData)
				.success(function(response) {
					alert(response.message);
					if (response.success) {						
						if (response.redirectUrl) {
							window.location.href = response.redirectUrl;
						}
					}
					else {						
						let url = new URL(window.location.href);						
						let sid = url.searchParams.get('sid');						
						let urlView = '/study/eview.php?session_id=' + sid;						
						window.location.href = urlView;
					}
				})
				.error(function(error) {
					alert('Có lỗi xảy ra khi gửi yêu cầu.');
				})
				.send();
		}

        document.getElementById('examForm').addEventListener('submit', (e) => {
            e.preventDefault();
            submitExam();
        });

		function getParameterByName(name, url = window.location.href) {
			name = name.replace(/[\[\]]/g, '\\$&');
			var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
				results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return decodeURIComponent(results[2].replace(/\+/g, ' '));
		}

		const sessionId = getParameterByName('sid');	
		const examType = '<?php echo $examType; ?>';		
		var examData = <?php echo $examDataJson ? $examDataJson : 'null'; ?>;		
		
		document.addEventListener('DOMContentLoaded', () => {
			if(examData){
				renderQuestions();
				renderQuestionMatrix();
				startTimer(examData.duration);

				document.getElementById('examName').textContent = examData.name;
				document.getElementById('examDuration').textContent = examData.duration;
				document.getElementById('passScore').textContent = examData.pass_score;
				document.getElementById('examDescription').textContent = examData.description;

				$('.collapsible-header').click(function() {
					$(this).toggleClass('collapsed');
				});

				loadMathJax();
			}	
		});						
    </script>
</body>
</html>