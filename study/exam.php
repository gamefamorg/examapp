<?php
// exam.php

require_once '../apiCaller.php';
require_once '../config.php';
require_once 'langVN.php';
ini_set('session.gc_maxlifetime', 18000);
session_set_cookie_params(18000);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!(isset($_SESSION['usrlogin']) && $_SESSION['usrlogin'] != '')) {
	header ("Location: ../login.php");
	exit();
}

$examData = null;
$isEdit = false;
$isBank = false;
$examId = -1;
$udat = $_SESSION['usrdatav'];
$userId = $udat != null && isset($udat['user_id']) ? $udat['user_id'] : 0;
$bearerToken = isset($udat['token']) ? $udat['token'] : null;
$isAdmin = isset($udat['is_admin']) ? $udat['is_admin'] : false;
$hasPremium = isset($udat['has_premium']) ? $udat['has_premium'] : false;
$apiCaller = new ApiCaller($baseAPI, $bearerToken);

$vConfig = $udat['data'];
$LimitCharExamTitle = isset($vConfig['LimitCharExamTitle']) ? $vConfig['LimitCharExamTitle'] : 0;
$LimitCharExamDesc = isset($vConfig['LimitCharExamDesc']) ? $vConfig['LimitCharExamDesc'] : 0;
$LimitCharQuestion = isset($vConfig['LimitCharQuestion']) ? $vConfig['LimitCharQuestion'] : 0;
$LimitCharAnswer = isset($vConfig['LimitCharAnswer']) ? $vConfig['LimitCharAnswer'] : 0;
$LimitQuestion = isset($vConfig['LimitQuestion']) ? $vConfig['LimitQuestion'] : 30;
$LimitQuestionBank = isset($vConfig['LimitQuestionPerBank']) ? $vConfig['LimitQuestionPerBank'] : 30;
$LimitAnswer = isset($vConfig['LimitAnswer']) ? $vConfig['LimitAnswer'] : 4;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {    	
    $examData = json_decode($_POST['submittedData'], true);	
    
	$isBank = isset($_SESSION['is_bank']) ? $_SESSION['is_bank'] : false;
	$examData['is_bank'] = $isBank;
	
    if ($_POST['action'] === 'update') {        
		$response = $apiCaller->call('POST', '/api/SApiService/UpdateExistedExam', $examData);
    } else {        
		$response = $apiCaller->call('POST', '/api/SApiService/CreateNewExam', $examData);
    }
	
	$data = $response['body'];	
	if (is_string($examData)) {
        $examData = json_decode($examData, true);
    }	
	$statusCode = isset($examData['status_code']) ? $examData['status_code'] : 0;
	if($statusCode == 200)
	{
		$isEdit = true;
		$examId = $data['data']['id'];
	}

    header('Content-Type: application/json');
    echo json_encode($response['body']);
    exit();
}

if (isset($_GET['ebank']) && is_numeric($_GET['ebank'])) {	
	$isBank = true;
	$_SESSION['is_bank'] = $isBank;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $examId = intval($_GET['id']);
    $isEdit = true;
    
	$postData = [        
        'pickup_id' => -1,
        'req_ip' => $_SERVER['REMOTE_ADDR'],
        'req_device' => '',
		'exam_id' => $examId
    ];
	
	$response = $apiCaller->call('POST', '/api/SApiService/LoadExamDetail', $postData);

	$examData = $response['body'];
	
    if (is_string($examData)) {
        $examData = json_decode($examData, true);
    }	
	
	$isBank = isset($examData['is_bank']) ? $examData['is_bank'] : false;
	$_SESSION['is_bank'] = $isBank;
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
            color: #333;
        }
        .container-fluid {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .question {
            background-color: #f1f3f5;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .answers {            
            margin-top: 15px;
        }
        .btn-group {
            margin-top: 20px;
        }
        .cke_notifications_area { 
            display: none !important; 
        }
        .cke_top {
            transition: height 0.3s ease;
            overflow: hidden;
            height: 30px;
        }
        .cke_top:hover {
            height: auto;
        }
        .question-list {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            height: calc(100vh - 100px);
            overflow-y: auto;
        }
        #examSettings {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        #examSettings .form-group {
            margin-bottom: 10px;
        }
        #examSettings label {
            font-weight: bold;
        }
        .question-list-item {
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
        }
        .question-list-item:hover {
            background-color: #d6d8db;
        }
        .question-list-item.active {
            background-color: #007bff;
            color: white;
        }
        #toTopBtn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            z-index: 99;
        }		
		.question {
			border-left: 4px solid #007bff;
			transition: all 0.3s ease;
		}
		.question:hover {
			box-shadow: 0 0 15px rgba(0,0,0,0.1);
		}
		.btn-group {
			position: sticky;
			bottom: 20px;
			background-color: rgba(255,255,255,0.9);
			padding: 10px;
			border-radius: 8px;
			box-shadow: 0 -5px 10px rgba(0,0,0,0.1);
		}
		.question-list-item {
			transition: all 0.3s ease;
		}		
		.question-list {
			background-color: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 8px;
			padding: 15px;
			height: calc(100vh - 100px);
			overflow-y: auto;
			box-shadow: 0 0 10px rgba(0,0,0,0.1);
		}
		.question-list h7 {
			color: #007bff;
			border-bottom: 2px solid #007bff;
			padding-bottom: 10px;
			margin-bottom: 15px;
		}
		.question-list-item {
			background-color: white;
			border: 1px solid #e9ecef;
			border-radius: 5px;			
			transition: all 0.3s ease;
		}
		.question-list-item.active {
			background-color: #e9ecef;
			border-left: 3px solid #007bff;
		}
		.question-number {
			font-weight: bold;
			color: #007bff;
		}
		.question-type {
			font-style: italic;
			color: #6c757d;
		}
		.answer-count {
			font-size: 0.9em;
			color: #28a745;
		}		
		.question-list ul {
			margin-top: 20px !important;
		}			
		.correct-answer:disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}		
		.question-score {
			font-size: 0.9em;
			color: #dc3545;
			font-weight: bold;
		}		
    </style>
</head>
<body>
<div class="container-fluid">
    <h1><a href="index.php" title="Trở về trang tổng quan">Quản lý Đề thi</a></h1>
    <div class="row">
        <div class="col-md-9">
            <div id="examSettings" class="mb-4">
                <h4>
                    <?php echo $isBank ? 'Ngân hàng đề thi' : 'Cài đặt đề thi';?> 
                    <button class="btn btn-sm btn-link float-right" data-toggle="collapse" data-target="#examSettingsContent">
                        <span class="collapse-text">Thu gọn</span>
                    </button>
                </h4>
                <div id="examSettingsContent" class="collapse show">
                    <div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="examType">Loại đề thi:</label>
								<select class="form-control form-control-sm" id="examType" onchange="examTypeChange()">
									<option value="TN">Trắc Nghiệm</option>
									<option value="TL">Tự Luận</option>
								</select>
							</div>
						</div>
                        <div class="col-md-6">
                            <div class="form-group">
								<label for="examTitle" class="required">Tiêu đề bài thi:</label>
								<input type="text" class="form-control form-control-sm" id="examTitle" placeholder="Nhập tiêu đề bài thi" maxlength="<?php echo $LimitCharExamTitle;?>" required>
							</div>
							<div class="form-group">
                                <label for="examDescription">Miêu tả bài thi:</label>
                                <textarea class="form-control form-control-sm" id="examDescription" rows="2" placeholder="Nhập miêu tả bài thi" maxlength="<?php echo $LimitCharExamDesc;?>"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="examDuration">Thời gian làm bài (phút):</label>
                                <input type="number" class="form-control form-control-sm" id="examDuration" placeholder="Nhập thời gian làm bài" max="999">
                            </div>
                            <div class="form-group">
                                <label for="passingScore">Điểm để vượt qua:</label>
                                <input type="number" class="form-control form-control-sm" id="passingScore" placeholder="Nhập điểm để vượt qua" max="9999">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn-group d-flex justify-content-between mb-4">
                <button class="btn btn-primary" onclick="addQuestion()">
                    <i class="fas fa-plus"></i> Thêm câu hỏi
                </button>
                <button class="btn btn-success" onclick="submitExam()">
                    <i class="fas fa-save"></i> Lưu đề thi
                </button>
            </div>
            <div id="questions"></div>
        </div>
        <div class="col-md-3">
			<div class="question-list">
				<h7><i class="fas fa-list"></i> Danh sách (<span class="question-score total-score" data-total-score="0">0đ</span>)</h7>
				<ul id="questionList" class="list-unstyled"></ul>
			</div>
		</div>
    </div>
</div>

<footer class="mt-4">
	<div class="container">
		<?php echo $LANGfooterInfo;?>
	</div>
</footer>

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
<script>    
	function getQuestions(){
		return $('div.question');
	}
	
	function countQuestions() {
		return getQuestions().length;
	}
	
	function removeQuestion(questionId) {
        $(`#${questionId}`).remove();
        renumberQuestions();		
    }
	
	function renumberQuestions() {		
		getQuestions().each(function(index) {
			const newNumber = index + 1;
			const $question = $(this);			
			$question.find('h4 a.question-link').text(`Câu hỏi ${newNumber}`);			
		});
		updateQuestionList();		
	}
	
    function addQuestion(qid) {
		let questionCount = countQuestions();
		if (questionCount >= limitQuestion) {
			alert(`Không thể thêm quá ${limitQuestion} câu hỏi.`);
			return null;
		}
		questionCount += 1;
		let qcid = -1 * questionCount;
		let questionId = generateHtmlId('question_', qcid);
		let originId = qcid;
		if(qid) {
			questionId = generateHtmlId('question_', qid);    
			originId = qid;
		}    

		const examType = $('#examType').val();
		
		let answerHtml = '';
		let questionTypeOptions = '';
		if (examType === 'TL') {
			answerHtml = `
				<div class="form-group">
					<label for="${questionId}_answer">Nội dung đáp án:</label>
					<textarea class="form-control" id="${questionId}_answer"></textarea>
				</div>
			`;
			questionTypeOptions = `
				<option value="essay" selected>Tự luận</option>
			`;
		} else {
			answerHtml = `
				<div class="answers" id="${questionId}_answers"></div>
				<div class="mt-3">
					<button class="btn btn-outline-primary btn-sm" onclick="addAnswer('${questionId}')">
						<i class="fas fa-plus"></i> Thêm câu trả lời
					</button>
				</div>
			`;
			questionTypeOptions = `
				<option value="single">Single Choice</option>
				<option value="multi">Multi Choice</option>
				<option value="text">Input Text</option>
			`;
		}

		const questionHtml = `
			<div class="question" id="${questionId}" data-question-origin-id="${originId}">
				<h4>
					<a href="#" onclick="highlightQuestion('${questionId}')" class="question-link">
						Câu hỏi ${questionCount}
					</a>
				</h4>
				<div class="form-group">
					<label for="${questionId}_type">Loại câu hỏi:</label>
					<select class="form-control question-type" id="${questionId}_type" onchange="changeQuestionType('${questionId}')" ${examType === 'TL' ? 'disabled' : ''}>
						 ${questionTypeOptions}
					</select>
				</div>
				<div class="form-group">
					<label for="${questionId}_score">Điểm:</label>
					<input type="number" class="form-control" id="${questionId}_score" placeholder="Nhập điểm cho câu hỏi" min="0" max="999" step="0.1" oninput="updateQuestionList('${questionId}')">
				</div>
				<div class="form-group">
					<label for="${questionId}_text">Nội dung câu hỏi:</label>
					<textarea class="form-control" id="${questionId}_text"></textarea>
				</div>
				${answerHtml}
				<div class="mt-3">
					<button class="btn btn-outline-danger btn-sm" onclick="removeQuestion('${questionId}')">
						<i class="fas fa-trash"></i> Xóa câu hỏi
					</button>
				</div>
			</div>
		`;
		$('#questions').append(questionHtml);
		initCKEditor(`${questionId}_text`);
		if (examType === 'TL') {
			initCKEditor(`${questionId}_answer`);
		}
		updateQuestionList();        
		return questionId;
	}

    function removeAnswer(questionId, answerId) {        
		$(`#${answerId}`).remove();
        renumberAnswers(questionId);
    }

    function renumberAnswers(questionId) {
		const $answers = $(`#${questionId}_answers .input-group`);
		
		$answers.each(function(index) {
			const newNumber = index + 1;
			const $answer = $(this);			
			$answer.find('.input-group-prepend .input-group-text:first').text(`${newNumber}.`);			
		});

		updateQuestionList();
	}

    function addAnswer(questionId) {
		let answerCount = $(`#${questionId}_answers .input-group`).length;
		if (answerCount >= limitAnswer) {
			alert(`Không thể thêm quá ${limitAnswer} câu trả lời cho một câu hỏi.`);
			return null;
		}
		answerCount += 1;
		const questionType = $(`#${questionId}_type`).val();				
		const answerId = `${questionId}_answer${Date.now()}${Math.random().toString(36).substr(2, 9)}${answerCount}`;
		let answerHtml = `
			<div class="input-group mb-2" id="${answerId}">
				<div class="input-group-prepend">
					<span class="input-group-text">${answerCount}.</span>
					<div class="input-group-text">
						<input name="group${questionId}"
							   type="${questionType === 'single' ? 'radio' : 'checkbox'}" 
							   class="correct-answer" 							   
							   ${questionType === 'text' ? 'disabled' : ''}>
					</div>
				</div>
				<input type="text" class="form-control answer-text" placeholder="Nhập câu trả lời" maxlength="<?php echo $LimitCharAnswer;?>">
				<div class="input-group-append">
					<button class="btn btn-outline-danger" onclick="removeAnswer('${questionId}', '${answerId}')">
						<i class="fas fa-times"></i>
					</button>
				</div>
			</div>
		`;
		$(`#${questionId}_answers`).append(answerHtml);
		updateQuestionList();		
		return answerId;
	}

    function changeQuestionType(questionId) {
		const answersDiv = $(`#${questionId}_answers`);
		const questionType = $(`#${questionId}_type`).val();
		
		answersDiv.find('.correct-answer').each(function() {
			if (questionType === 'single') {
				$(this).attr('type', 'radio').prop('disabled', false);
			} else if (questionType === 'multi') {
				$(this).attr('type', 'checkbox').prop('disabled', false);
			} else if (questionType === 'text') {
				$(this).prop('disabled', true);
			}
		});

		updateQuestionList();
	}

	function updateQuestionList() {
		let totalScore = 0;
		const questionList = $('#questionList');
		questionList.empty();
		getQuestions().each(function(index) {
			const questionId = $(this).attr('id');
			const questionType = $(this).find('.question-type').val();			
			const answerCount = $(this).find('.answers .input-group').length + $(this).find('textarea[id$="_answer"]').length;
			const questionNumber = index + 1;
			const score = $(this).find(`#${questionId}_score`).val() || '0';
			totalScore += parseFloat(score);
			questionList.append(`
				<li class="question-list-item" data-question-html-id="${questionId}" onclick="scrollToQuestion('${questionId}')">
					<div class="question-number">Câu ${questionNumber} (<span class="answer-count">${answerCount} đáp án</span>) (<span class="question-score">${score}đ</span>)</div>
					<div class="question-type">${getQuestionTypeText(questionType)}</div>
				</li>
			`);
		});		
		$('.total-score').text(totalScore + "đ");
	}

	function getQuestionTypeText(type) {
		switch(type) {
			case 'single':
				return 'Một lựa chọn';
			case 'multi':
				return 'Nhiều lựa chọn';
			case 'text':
				return 'Nhập văn bản';
			case 'essay':
				return 'Tự luận';
			default:
				return type;
		}
	}
	
	function clearAllQuestions() {
		$('#questions').empty();
		$('#questionList').empty();
		updateQuestionList();
	}

    function scrollToQuestion(questionId) {
        const questionElement = document.getElementById(questionId);
		const offset = 10;
		const elementPosition = questionElement.getBoundingClientRect().top + window.scrollY;
		const offsetPosition = elementPosition - offset;
		window.scrollTo({
			top: offsetPosition,
			behavior: 'smooth'
		});
        $('.question-list-item').removeClass('active');
        $(`.question-list-item[data-question-html-id="${questionId}"]`).addClass('active');
    }

    function submitExam() {		
		const examTitle = $('#examTitle').val().trim();
		if (!examTitle) {
			alert('Vui lòng nhập tiêu đề bài thi!');
			$('#examTitle').focus();
			return;
		}
		
		const examData = getExamData();
		const action = examData.id === -1 ? 'create' : 'update';
	
		new AjaxRequest('exam.php', 'POST', action, examData)
			.success(function(response) {								
				if (response.success) {											
					if (action === 'update') {
						setTimeout(function() { location.reload(); }, 3000);
					} else if (action === 'create' && response.data && response.data.id) {
						setTimeout(function() { 
							window.location.href = window.location.pathname + '?id=' + response.data.id;
						}, 3000);
					}
				}				
				showMessage(response.status_code, response.message);
			})
			.error(function(error) {
				alert('Có lỗi xảy ra khi gửi yêu cầu.');
			})
			.send();
	}

	function getExamData() {		
		const examData = {
			user_id: userID,
			id: examID,
			name: $('#examTitle').val(),
			description: $('#examDescription').val(),
			type: $('#examType').val(),
			duration: $('#examDuration').val() || 0,
			pass_score: $('#passingScore').val() || 0,
			questions: []
		};

		getQuestions().each(function() {
			const originId = $(this).attr('data-question-origin-id') || '0';
			const questionId = $(this).attr('id') || '';
			const questionType = $(`#${questionId}_type`).val();
			const questionScore = $(`#${questionId}_score`).val() || 0;
			const questionText = CKEDITOR.instances[`${questionId}_text`].getData();
			const answers = [];

			if (examData.type === 'TN') {
				$(`#${questionId}_answers .input-group`).each(function() {
					const answerText = $(this).find('.answer-text').val();
					const isCorrect = $(this).find('.correct-answer').prop('checked');
					answers.push({ name: answerText, is_correct: isCorrect });
				});
			} else {
				const answerText = CKEDITOR.instances[`${questionId}_answer`].getData();
				answers.push({ name: answerText, is_correct: true });
			}

			examData.questions.push({
				id: parseInt(originId),
				type: questionType,
				score: parseFloat(questionScore),
				name: questionText,
				answers: answers
			});
		});
		console.log(examData);
		return examData;
	}

    $('#examSettingsContent').on('hidden.bs.collapse', function () {
        $('.collapse-text').text('Mở rộng');
    });
	
    $('#examSettingsContent').on('shown.bs.collapse', function () {
        $('.collapse-text').text('Thu gọn');
    });

	function highlightQuestion(questionId) {
		$('.question-list-item').removeClass('active');
		$(`.question-list-item[data-question-html-id="${questionId}"]`).addClass('active');
	}
	
	function loadExamData(data) {		
		if (!data) return;
	
		const tempLimitQuestion = limitQuestion;
		const tempLimitAnswer = limitAnswer;
		limitQuestion = 9999;
		limitAnswer = 9999;
	
		// Load thông tin cơ bản của đề thi	
		$('#examType').val(data.type);
		$('#examType').data('current-type', data.type);
		$('#examTitle').val(data.name);
		$('#examDescription').val(data.description);
		$('#examDuration').val(data.duration);
		$('#passingScore').val(data.pass_score);
				
		// Xóa tất cả câu hỏi hiện tại
		$('#questions').empty();
		$('#questionList').empty();
		
		// Load từng câu hỏi
		data.questions.forEach((question, index) => {
			const questionId = addQuestion(question.id);			
			
			$(`#${questionId}_type`).val(question.type);
			$(`#${questionId}_score`).val(question.score);
			CKEDITOR.instances[`${questionId}_text`].setData(question.name);
			
			// Load câu trả lời
			if (data.type === 'TN') {
				question.answers.forEach(answer => {
					const answerId = addAnswer(questionId);
					$(`#${answerId} .answer-text`).val(answer.name);
					$(`#${answerId} .correct-answer`).prop('checked', answer.is_correct);
				});
			} else if (question.answers && question.answers.length > 0) {
				CKEDITOR.instances[`${questionId}_answer`].setData(question.answers[0].name);
			}
			
			changeQuestionType(questionId);
		});

		limitQuestion = tempLimitQuestion;
		limitAnswer = tempLimitAnswer;
	}

	function examTypeChange(){
		const selector = $('#examType');
		
		const newExamType = selector.val();
		const currentExamType = selector.data('current-type');

		if (newExamType !== currentExamType) {
			const confirmMessage = `Chuyển sang loại đề thi ${newExamType === 'TracNghiem' ? 'trắc nghiệm' : 'tự luận'} sẽ xóa tất cả câu hỏi hiện tại. Bạn có chắc chắn muốn tiếp tục?`;
				
			if (confirm(confirmMessage)) {
				clearAllQuestions();
				selector.data('current-type', newExamType);
			} else {
				selector.val(currentExamType);
				return;
			}
		}
	}

	var examData = <?php echo $examDataJson ? $examDataJson : 'null'; ?>;
	var examID = <?php echo $examId; ?>;
	var limitQuestion = <?php echo $isBank ? $LimitQuestionBank : $LimitQuestion; ?>;
	var limitAnswer = <?php echo $LimitAnswer; ?>;
	const userID = <?php echo $userId; ?>;
	
	$(document).ready(function() {		
		// Khởi tạo giá trị ban đầu cho loại đề thi
		$('#examType').data('current-type', $('#examType').val());
		
		if (examData) {
			loadExamData(examData);
		}		
	});
</script>
</body>
</html>
