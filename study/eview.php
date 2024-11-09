<?php
// eview.php

ini_set('date.timezone', 'Asia/Ho_Chi_Minh');
require_once '../apiCaller.php';
require_once '../config.php';
require_once 'langVN.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$udat = isset($_SESSION['usrdatav']) ? $_SESSION['usrdatav'] : null;
$userId = $udat != null && isset($udat['user_id']) ? $udat['user_id'] : 0;
$isAdmin = $udat != null && isset($udat['is_admin']) ? $udat['is_admin'] : false;
$hasPremium = $udat != null && isset($udat['has_premium']) ? $udat['has_premium'] : false;
$hasAdv = !($isAdmin || $hasPremium);
$hasOwner = false;
$bearerToken = isset($udat['token']) ? $udat['token'] : null;
$apiCaller = new ApiCaller($baseAPI, $bearerToken);
$isResultMode = false;
$examData = null;
$isOwner = true;
$isOwnerMsg = "Bạn không có quyền truy cập dữ liệu này.";
$action = isset($_GET['action']) ? $_GET['action'] : "";
$isBank = isset($_SESSION['is_bank']) ? $_SESSION['is_bank'] : false;
$eBankData = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action == "start" && isset($_GET['id']) && is_numeric($_GET['id'])){
	$examId = intval($_GET['id']);
    $isResultMode = false;	
	$postData = [
        'pickup_id' => $userId,
        'req_ip' => $_SERVER['REMOTE_ADDR'],
        'req_device' => '',
		'exam_id' => $examId
    ];
	
	$response = $apiCaller->call('POST', '/api/SApiService/CreateExamTest', $postData);
	$examData = $response['body'];
	
	if (is_string($examData)) {
        $examData = json_decode($examData, true);
    }
	
	$sessionId = $examData!=null && isset($examData['data']['session_id']) ? $examData['data']['session_id'] : "";
	
	$statusCode = isset($examData['status_code']) ? $examData['status_code'] : 0;
	if($statusCode != 200)
	{
		$isOwner = false;
		$isOwnerMsg = $examData['message'];
	}
	else{		
		header ("Location: doexam.php?sid=" . $sessionId);
		exit();		
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $examId = intval($_GET['id']);
    $isResultMode = false;
	$postData = [        
        'pickup_id' => $userId,
        'req_ip' => $_SERVER['REMOTE_ADDR'],
        'req_device' => '',
		'exam_id' => $examId
    ];
	
	$response = $apiCaller->call('POST', '/api/SApiService/ViewExamDetail', $postData);
	$examData = $response['body'];	
    
    if (is_string($examData)) {
        $examData = json_decode($examData, true);
    }
	
	$statusCode = isset($examData['status_code']) ? $examData['status_code'] : 0;
	if($statusCode != 200)
	{
		$isOwner = false;
		$isOwnerMsg = $examData['message'];
	}
	
	$isBank = isset($examData['is_bank']) ? $examData['is_bank'] : false;
	$_SESSION['is_bank'] = $isBank;	
	$eBankData = isset($examData['ebank']) ? $examData['ebank'] : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action != "start" && isset($_GET['session_id']) && !empty($_GET['session_id'])) {
    $sessionId = $_GET['session_id'];
    $isResultMode = true;
    $postData = [        
        'pickup_id' => $userId,
        'req_ip' => $_SERVER['REMOTE_ADDR'],
        'req_device' => '',
        'session_id' => $sessionId
    ];
    
    $response = $apiCaller->call('POST', '/api/SApiService/ViewExamDetail', $postData);
    $examData = $response['body'];
    
    if (is_string($examData)) {
        $examData = json_decode($examData, true);
    }
	
	$statusCode = isset($examData['status_code']) ? $examData['status_code'] : 0;
	if($statusCode != 200)
	{
		$isOwner = false;
		$isOwnerMsg = $examData['message'];
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isBank) {
    $postData = [
        'pickup_id' => $userId,
        'req_ip' => $_SERVER['REMOTE_ADDR'],
        'req_device' => '',
		'exam_id' => isset($_GET['id']) ? $_GET['id'] : 0,
        'exam_title' => $_POST['examTitle'],
        'exam_duration' => $_POST['examDuration'],
        'exam_question_count' => $_POST['questionCount'],
        'exam_description' => $_POST['examDescription'],
        'exam_pass_score' => $_POST['passScore'],
		'exam_banks' => isset($_POST['examBanks']) ? implode(';', $_POST['examBanks']) : ''
    ];
	
	if($isAdmin || $hasPremium)
	{
		if(isset($_POST['examTeacher'])) {
			$postData['exam_teacher'] = $_POST['examTeacher'];
		}
		if(isset($_POST['examFilterAccount'])) {
			$postData['exam_filter_account'] = $_POST['examFilterAccount'];
		}
		if(isset($_POST['examFilterIpAddress'])) {
			$postData['exam_filter_ipaddress'] = $_POST['examFilterIpAddress'];
		}
	}
	
	$response = $apiCaller->call('POST', '/api/SApiService/CreateRandomExam', $postData);
	$examData = $response['body'];
	
	if (is_string($examData)) {
        $examData = json_decode($examData, true);
    }
	
    if (isset($examData['status_code']) && $examData['status_code'] == 200) {
        $examId = $examData['data']['id'];
        header("Location: eview.php?id=" . $examId);
        exit();
    } else {
        $isOwner = false;
		$isOwnerMsg = $examData['message'];
    }
}

$exam_data = $examData;
$createdBy = isset($exam_data['user_id']) ? $exam_data['user_id'] : 0;
$hasOwner = $isAdmin || $userId == $createdBy;
$isEssay = (isset($exam_data['type']) ? $exam_data['type'] : "") == "TL";
$total_score = 0;
if($exam_data && isset($exam_data['questions'])){
	foreach ($exam_data['questions'] as $question) {
		$total_score += isset($question['score']) ? $question['score'] : 0;
	}	
}
$pass_score = isset($exam_data['pass_score']) ? $exam_data['pass_score'] : 0;
$user_score = isset($exam_data['user_score']) ? $exam_data['user_score'] : 0;
$passStatus = getPassStatus($user_score, $pass_score);

function is_correct_answer($question, $user_answer) {    
	if (empty($user_answer)) {
        return false;
    }
	if ($question['type'] == 'text') {
        $correct_answers = array();
        foreach ($question['answers'] as $answer) {
            $correct_answers[] = $answer['name'];
        }
        $user_answers = explode(';', $user_answer);
        $correct_count = 0;
        foreach ($correct_answers as $answer) {
            if (in_array($answer, $user_answers)) {
                $correct_count++;
            }
        }
        return $correct_count == count($correct_answers);
    } elseif ($question['type'] == 'single') {
        foreach ($question['answers'] as $answer) {
            if (isset($answer['is_correct']) && $answer['is_correct'] && $user_answer == $answer['id']) {
                return true;
            }
        }
        return false;
    } elseif ($question['type'] == 'multi') {
        $correct_answers = array();
        foreach ($question['answers'] as $answer) {
            if (isset($answer['is_correct']) && $answer['is_correct']) {
                $correct_answers[] = $answer['id'];
            }
        }
        $user_answers = explode(';', $user_answer);
        if (count($correct_answers) != count($user_answers)) {
            return false;
        }
        foreach ($correct_answers as $answer) {
            if (!in_array($answer, $user_answers)) {
                return false;
            }
        }
        return true;
    }
    return false;
}

function get_correct_answers($question) {
    $correct_answers = array();
    foreach ($question['answers'] as $answer) {
        if ($question['type'] == 'text' || (isset($answer['is_correct']) && $answer['is_correct'])) {
            $correct_answers[] = $answer['name'];
        }
    }
    return implode(', ', $correct_answers);
}

function formatDateTime($dateTimeString) {
    if (empty($dateTimeString)) {
        return '';
    }
    
    try {
        $dateTime = new DateTime($dateTimeString);
        return $dateTime->format('d/m/Y H:i:s');
    } catch (Exception $e) {
        return '';
    }
}

function getPassStatus($score, $total) {
	if($score >= $total)
		return ['text' => 'Vượt Qua', 'class' => 'badge-success'];
	else
		return ['text' => 'Thất Bại', 'class' => 'badge-warning'];    
}

function getUserAnswer($index, $user_answer) {
    if (empty($user_answer)) {
        return '';
    }
    $user_answers = explode(';', $user_answer);
    return isset($user_answers[$index]) ? $user_answers[$index] : '';
}
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
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #FFC107;
            --text-color: #333;
            --bg-color: #F0F8FF;
        }
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        .content {
            flex: 1 0 auto;
        }
        .exam-info {
            background-color: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .start-btn {
            background-color: var(--primary-color);
            border: none;
        }
        .start-btn:hover {
            background-color: #45a049;
        }
        .home-btn {
            background-color: var(--secondary-color);
            border: none;
            color: var(--text-color);
        }
        .home-btn:hover {
            background-color: #e6ac00;
        }
        .expand-btn {
            cursor: pointer;
            color: var(--primary-color);
        }
        .card {
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
        }        		
		.correct-answer {
            color: green;
            font-weight: bold;
        }
        .incorrect-answer {
            color: red;
            font-weight: bold;
        }
		img {
			width: 100%;
		}
		
		/* Đảm bảo container của Select2 không vượt quá chiều rộng của phần tử cha */
		.select2-container {
			width: 100% !important;
		}
		
		.select2-container--default .select2-selection--multiple .select2-selection__clear {
			margin-top: 2px !important;
		}

		/* Điều chỉnh style cho selection box */
		.select2-container--default .select2-selection--multiple {
			border: 1px solid #ced4da;
			border-radius: 0.25rem;
			min-height: 38px;
			line-height: 1.5;
		}

		/* Điều chỉnh padding cho container */
		.select2-container--default .select2-selection--multiple .select2-selection__rendered {
			padding: 2px 8px;
			display: flex;
			flex-wrap: wrap;
		}

		/* Style cho các item đã chọn */
		.select2-container--default .select2-selection--multiple .select2-selection__choice {
			background-color: #007bff;
			border: 1px solid #007bff;
			color: #325791;
			border-radius: 0.25rem;
			padding: 2px 8px;
			margin-top: 4px;
			margin-right: 5px;
			font-size: 0.875rem;
			line-height: 1.5;
			max-width: calc(100% - 10px);
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		/* Style cho nút xóa item */
		.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
			color: #fff;
			margin-right: 5px;
			margin-top: -1px;
			font-weight: bold;
		}

		.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
			color: #fff;
			background-color: #0056b3;
		}

		/* Điều chỉnh vị trí của input tìm kiếm */
		.select2-container .select2-search--inline .select2-search__field {
			margin-top: 7px;
			font-size: 0.875rem;
		}

		/* Style cho dropdown */
		.select2-container--default .select2-results__option--highlighted[aria-selected] {
			background-color: #007bff;
		}

		.select2-container--default .select2-results__option[aria-selected=true] {
			background-color: #e9ecef;
		}

		/* Đảm bảo dropdown không vượt quá chiều rộng của container */
		.select2-container--default .select2-dropdown {
			max-width: 100%;
		}

		/* Responsive adjustments */
		@media (max-width: 768px) {
			.select2-container--default .select2-selection--multiple .select2-selection__choice {
				font-size: 0.75rem;
				padding: 1px 5px;
			}

			.select2-container .select2-search--inline .select2-search__field {
				font-size: 0.75rem;
			}
		}		
    </style>	
</head>
<body>
    <div class="content">
        <div class="container mt-3">
            <div class="row align-items-center">
                <div class="col-md-10 col-8">
                    <h2 class="text-primary"><?php echo $isBank ? 'Ngân hàng đề thi' : 'Thông tin đề thi';?></h2>
                </div>
<?php if($userId > 0){?>				
                <div class="col-md-2 col-4 text-right">
                    <a href="index.php" class="btn home-btn"><i class="fas fa-home"></i> Home</a>
                </div>
<?php }?>				
            </div>
<?php if($exam_data){?>	
<?php if($isOwner): ?>
<?php if($isBank){?>
			<div class="exam-info mt-3">
				<form id="createRandomExam" method="POST">					
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="examBanks"><strong><i class="fas fa-database"></i> Chọn ngân hàng đề thi:</strong></label>
								<select class="form-control select2-multi" id="examBanks" name="examBanks[]" multiple>
									<?php
									$currentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
									foreach ($eBankData as $bank) {
										$selected = ($bank['id'] == $currentId) ? 'selected' : '';
										echo "<option value='{$bank['id']}' $selected>{$bank['name']}</option>";
									}
									?>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 col-6">
							<div class="form-group">
								<label for="examTitle" class="required"><strong><i class="fas fa-heading"></i> Tiêu đề:</strong></label>
								<input type="text" class="form-control" id="examTitle" name="examTitle" required>
							</div>
						</div>
						<div class="col-md-3 col-6">
							<div class="form-group">
								<label for="examDuration"><strong><i class="far fa-clock"></i> Thời gian (phút):</strong></label>
								<input type="number" class="form-control" id="examDuration" name="examDuration" required min="1" value="<?php echo htmlspecialchars($exam_data['duration']); ?>">
							</div>
						</div>
						<div class="col-md-3 col-6">
							<div class="form-group">
								<label for="questionCount" class="required"><strong><i class="fas fa-question-circle" title="số lượng câu hỏi tạo ra"></i> Số câu hỏi:</strong></label>
								<input type="number" class="form-control" id="questionCount" name="questionCount" required min="1">
								/<?php echo count($exam_data['questions']); ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9 col-6">
							<div class="form-group">
								<label for="examDescription"><strong><i class="fas fa-info-circle"></i> Mô tả:</strong></label>
								<textarea class="form-control" id="examDescription" name="examDescription" rows="3"></textarea>
							</div>
						</div>
						<div class="col-md-3 col-6">
							<div class="form-group">
								<label for="passScore"><strong><i class="fas fa-check-circle" title="điểm để đánh giá bài thi đạt trạng thái pass"></i> Điểm để pass:</strong></label>
								<input type="number" class="form-control" id="passScore" name="passScore" required min="0" step="0.01" value="<?php echo $pass_score; ?>">
								/<?php echo $total_score; ?>
							</div>
						</div>
					</div>
<?php if($isAdmin || $hasPremium){?>					
					<div class="row">
						<div class="col-md-12 col-6">
							<div class="form-group">
								<label for="examTeacher"><strong><i class="fas fa-info-circle" title="bỏ trống thì không phải là đề thi chấm điểm"></i> Tài khoản giáo viên:</strong></label>
								<input type="text" class="form-control" id="examTeacher" name="examTeacher">
							</div>
						</div>						
					</div>
					<div class="row">
						<div class="col-md-6 col-6">
							<div class="form-group">
								<label for="examFilterAccount"><strong><i class="fas fa-info-circle" title="bỏ trống để không yêu cầu"></i> Lọc cho phép tài khoản(acc1;acc2..):</strong></label>
								<textarea class="form-control" id="examFilterAccount" name="examFilterAccount" rows="3"></textarea>
							</div>
						</div>
						<div class="col-md-6 col-6">
							<div class="form-group">
								<label for="examFilterIpAddress"><strong><i class="fas fa-info-circle" title="bỏ trống để không yêu cầu"></i> Lọc cho phép địa chỉ IpAdress(ip1;ip2..):</strong></label>
								<textarea class="form-control" id="examFilterIpAddress" name="examFilterIpAddress" rows="3"></textarea>
							</div>
						</div>
					</div>
<?php }?>					
					<button type="submit" class="btn btn-primary start-btn mt-3"><i class="fas fa-random"></i> Tạo một đề thi ngẫu nhiên</button>
				</form>
			</div>
<?php }else{?>	
            <div class="exam-info mt-3">
                <div class="row">
                    <div class="col-md-6 col-6">
                        <p><strong><i class="fas fa-heading"></i> Tiêu đề:</strong> <?php echo htmlspecialchars($exam_data['name']); ?></p>
                    </div>                    
                    <div class="col-md-3 col-6">
                        <p><strong><i class="far fa-clock"></i> Thời gian:</strong> <?php echo htmlspecialchars($exam_data['duration']); ?> phút</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p><strong><i class="fas fa-question-circle"></i> Số câu hỏi:</strong> <?php echo count($exam_data['questions']); ?></p>
                    </div>
                    
                </div>
                <div class="row">                    
                    
                    <div class="col-md-9 col-6">
                        <p><strong><i class="fas fa-info-circle"></i> Mô tả:</strong> <?php echo htmlspecialchars($exam_data['description']); ?></p>
                    </div>
					<div class="col-md-3 col-6">
                        <p><strong><i class="fas fa-check-circle"></i> Điểm để pass:</strong> <?php echo $pass_score; ?>/<?php echo $total_score; ?></p>
                    </div>
                </div>
<?php }?>				
<?php if($isResultMode && !$isBank){?>					
                <hr>
                <div class="row mt-3">
					<div class="col-md-4">
						<p><strong><i class="fas fa-hourglass-start"></i> Bắt đầu:</strong> <?php echo isset($exam_data['start_time']) ? htmlspecialchars(formatDateTime($exam_data['start_time'])) : ''; ?></p>
					</div>
					<div class="col-md-4">
						<p><strong><i class="fas fa-hourglass-end"></i> Kết thúc:</strong> <?php echo isset($exam_data['end_time']) ? htmlspecialchars(formatDateTime($exam_data['end_time'])) : ''; ?></p>
					</div>
					<div class="col-md-4">
						<p><strong><i class="fas fa-paper-plane"></i> Đã nộp:</strong> <?php echo isset($exam_data['send_time']) ? htmlspecialchars(formatDateTime($exam_data['send_time'])) : ''; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<p><strong><i class="fas fa-user-graduate"></i> Học viên:</strong> <?php echo isset($exam_data['student']) ? htmlspecialchars($exam_data['student']) : 'Chưa có tài khoản'; ?></p>
					</div>
					<div class="col-md-4">
						<p><strong><i class="fas fa-star"></i> Điểm đã đạt:</strong> <?php echo isset($exam_data['user_score']) ? number_format($exam_data['user_score'], 2) : 0; ?></p>
					</div>
					<div class="col-md-4">
						<p><strong><i class="fas fa-trophy"></i> Trạng thái:</strong> <span class="badge <?php echo $passStatus['class']; ?>"><?php echo htmlspecialchars($passStatus['text']); ?></span></p>
					</div>					
				</div>
<?php }else{?>
<?php if(!$isBank){?>				
                <button class="btn btn-primary start-btn mt-3"  onclick="startExam(<?php echo $exam_data['id']; ?>)"><i class="fas fa-play"></i> Bắt đầu làm bài</button>
<?php }}?>								
            </div>
<?php if(($isResultMode || $hasOwner) && !$isBank){?>		
<?php if($userId > 0){?>	            
            <div class="mt-4">
            <h3 class="expand-btn" data-toggle="collapse" data-target="#questionList">
                <i class="fas fa-chevron-right"></i> Chi tiết bài thi
            </h3>
            <div id="questionList" class="collapse">
                <div class="question-list mt-3">
                    <?php 
					$questionNumber = 1;	
					$examMainError = isset($exam_data['pass_status']) ? $exam_data['pass_status'] : 0;					
					foreach ($exam_data['questions'] as $question): 
					$hasAccessRs = $isAdmin || $hasPremium || $hasOwner;
					?>
                        <div class="card">
                            <div class="card-header">
                                <h5>Câu hỏi <?php echo $questionNumber; ?> (<?php echo isset($question['score']) ? $question['score'] : '?'; ?> điểm)</h5>
                            </div>
                            <div class="card-body">
                                <p><?php echo $question['name']; ?></p>
								<?php 
								$qidx = 0;
								foreach ($question['answers'] as $answer): 
								if ($isEssay):
								?>
								<?php echo getUserAnswer($qidx, $question['user_answer']); ?>
								<?php elseif ($question['type'] == 'single' || $question['type'] == 'multi'): 
								?>
									<div class="form-check">
										<input class="form-check-input" type="<?php echo $question['type'] == 'single' ? 'radio' : 'checkbox'; ?>" disabled 
											   <?php echo $question['user_answer'] !== "" && in_array($answer['id'], explode(';', $question['user_answer'])) ? 'checked' : ''; ?>>
										<label class="<?php echo (isset($answer['is_correct']) && $answer['is_correct'] && $hasAccessRs && $examMainError != 1) ? 'correct-answer' : ''; ?>">
											<?php echo htmlspecialchars($answer['name']); ?>
										</label>
									</div>
								<?php elseif ($question['type'] == 'text'): ?>
									<input type="text" class="form-control" value="<?php echo htmlspecialchars(getUserAnswer($qidx, $question['user_answer'])); ?>" disabled>
								<?php 
								endif; 
								$qidx++;
								endforeach;
								?>								
<?php if($hasAccessRs && !$isEssay && $examMainError != 1){?>								
                                <p class="mt-2">
                                    <strong>Đáp án đúng:</strong> 
                                    <?php echo htmlspecialchars(get_correct_answers($question)); ?>
                                </p>
<?php } if($isResultMode && !$isEssay && $examMainError != 1){?>								
                                <p class="mt-2 <?php echo is_correct_answer($question, $question['user_answer']) ? 'correct-answer' : 'incorrect-answer'; ?>">
                                    <strong>Kết quả:</strong> 
                                    <?php
                                    if ($question['user_answer'] === "") {
                                        echo 'không trả lời';
                                    } else {
                                        echo is_correct_answer($question, $question['user_answer']) ? 'Đúng' : 'Sai';
                                    }
                                    ?>
                                </p>
<?php }?>								
                            </div>
                        </div>
                    <?php 
					$questionNumber++;
					endforeach; 
					?>
                </div>
            </div>
        </div>
<?php }else{?>
			<div class="alert alert-warning mt-4">
				Bạn cần <a href="https://gamefam.org/register/index.php">đăng ký là thành viên</a> để xem chi tiết bài thi.
			</div>
<?php }}?>
    <?php else: ?>
        <div class="alert alert-warning mt-3">
            <?php echo $isOwnerMsg;?>
        </div>
    <?php endif; ?>
<?php }?>			
        
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
	
	<script src="../Assets/js/jquery-3.3.1.min.js"></script>
	<script src="../Assets/js/popper-1.14.7.min.js"></script>
	<script src="../Assets/js/bootstrap-4.3.1.min.js"></script>
	<script src="../Assets/js/exam.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?php if($hasAdv){?>	
	<script src="../Assets/js/adv.js"></script>
<?php }?>
    <script>
        function startExam(examId) {
			window.location.href = `eview.php?id=${examId}&action=start`;
		}
		
		function triggerSelectBank() {
			var currentId = <?php echo isset($_GET['id']) ? $_GET['id'] : 'null'; ?>;
    
			// Khởi tạo Select2
			$('.select2-multi').select2({
				placeholder: "Chọn ngân hàng đề thi",
				allowClear: true,
				closeOnSelect: false,
				selectOnClose: false
			});

			if (currentId) {
				$('#examBanks option[value="' + currentId + '"]').prop('disabled', true);
			}

			function updateSelection() {
				var selectedValues = $('#examBanks').val() || [];
				if (currentId && !selectedValues.includes(currentId.toString())) {
					selectedValues.push(currentId.toString());
					$('#examBanks').val(selectedValues).trigger('change.select2');
				}
			}

			$('#examBanks').on('select2:select select2:unselect', function(e) {
				updateSelection();
			});

			$('#examBanks').on('select2:clear', function(e) {
				setTimeout(function() {
					updateSelection();
				}, 0);
			});

			// Trigger initial update
			updateSelection();
		}
		
		$(document).ready(function() {
            $('.expand-btn').click(function() {
                $(this).find('i').toggleClass('fa-chevron-right fa-chevron-down');
            });
			$('.start-btn').click(function() {
                $(this).find('i').toggleClass('fa-chevron-right fa-chevron-down');
            });
						
			triggerSelectBank();			
			loadMathJax();
        });				
    </script>
</body>
</html>