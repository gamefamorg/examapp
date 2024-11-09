<?php
require_once '../apiCaller.php';
require_once '../config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!(isset($_SESSION['usrlogin']) && $_SESSION['usrlogin'] != '')) {
    header("Location: ../login.php");
    exit();
}

$udat = $_SESSION['usrdatav'];
$userId = $udat != null && isset($udat['user_id']) ? $udat['user_id'] : 0;
$bearerToken = isset($udat['token']) ? $udat['token'] : null;
$apiCaller = new ApiCaller($baseAPI, $bearerToken);
$response = ['success' => false, 'message' => '', 'status_code' => 0];
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['submittedData'])) {
    $examId = $action == 'importexam' ? 0 : intval(json_decode($_POST['submittedData'], true));
	if (is_numeric($examId)) {        
        $postData = [        
            'pickup_id' => $userId,
            'req_ip' => $_SERVER['REMOTE_ADDR'],
            'req_device' => '',
            'exam_id' => $examId
        ];
        
        $apiEndpoint = '';
        
        switch ($action) {
            case 'delete':
                $apiEndpoint = '/api/SApiService/DeleteExistedExam';
                break;
            case 'clone':
                $apiEndpoint = '/api/SApiService/CloneExistedExam';
                break;
            case 'onofflock':
                $apiEndpoint = '/api/SApiService/UpdateExistedExamStatus';
                break;
			case 'makecopy':
                $apiEndpoint = '/api/SApiService/MakeCopyExistedExam';
                break;						
			case 'importexam':
                $apiEndpoint = '/api/SApiService/ImportExam';		
				$postData['exam_import_data'] = $_POST['submittedData'];
                break;
			case 'exportexam':
                $apiEndpoint = '/api/SApiService/ExportExam';
                break;
            default:
                $response['message'] = 'Hành động không hợp lệ';
                echo json_encode($response);
                exit;
        }
        
        $apiResponse = $apiCaller->call('POST', $apiEndpoint, $postData);
        
        $data = $apiResponse['body'];
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        
        ob_start();
        var_dump($apiResponse);
        $dump = ob_get_clean();
        
        $response['status_code'] = isset($data['status_code']) ? $data['status_code'] : 0;
        $response['success'] = isset($data['success']) ? $data['success'] : false;
        $response['message'] = isset($data['message']) ? $data['message'] : $dump.'';
		$response['data'] = isset($data['data']) ? $data['data'] : null;
    } else {
        $response['message'] = 'ID đề thi không hợp lệ';
    }
} else {
    $response['message'] = 'Yêu cầu không hợp lệ';
}

header('Content-Type: application/json');	
echo json_encode($response);
exit;
?>