<?php
// index.php

ini_set('date.timezone', 'Asia/Ho_Chi_Minh');
require_once '../apiCaller.php';
require_once '../config.php';
require_once 'langVN.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!(isset($_SESSION['usrlogin']) && $_SESSION['usrlogin'] != '')) {
	header ("Location: ../login.php");
	exit();
}

// clean values
if(isset($_SESSION['is_bank']))
	$_SESSION['is_bank'] = false;

$udat = $_SESSION['usrdatav'];
$userId = $udat != null && isset($udat['user_id']) ? $udat['user_id'] : 0;
$bearerToken = isset($udat['token']) ? $udat['token'] : null;
$isAdmin = isset($udat['is_admin']) ? $udat['is_admin'] : false;
$hasPremium = isset($udat['has_premium']) ? $udat['has_premium'] : false;
$hasAdv = !($isAdmin || $hasPremium);
$apiCaller = new ApiCaller($baseAPI, $bearerToken);

$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
$itemsPerPage = 10;
$totalItems = 0;

if ($tab === 'teacher' && !($isAdmin || $hasPremium)) {    
    header("Location: index.php?tab=all");
    exit();
}

try {    
    $postData = [
        'page' => $currentPage,
        'per_page' => $itemsPerPage,
        'search' => $search,
		'tab' => $tab,
        'pickup_id' => $userId,
        'req_ip' => $_SERVER['REMOTE_ADDR'],
        'req_device' => ''
    ];

    $response = $apiCaller->call('POST', '/api/SApiService/LoadExamView', $postData);

    if (!isset($response['body'])) {
        throw new Exception("Không nhận được dữ liệu hợp lệ từ API");
    }

    $data = $response['body'];    
    if (is_string($data)) {
        $data = json_decode($data, true);
    }

	$statusCode = isset($data['status_code']) ? $data['status_code'] : 0;
	if($statusCode == 200) {
		// Tính toán phân trang	
		$totalItems = isset($data['total_items_count']) ? $data['total_items_count'] : 0;	
		$totalPages = ceil($totalItems / $itemsPerPage);
		$start = ($currentPage - 1) * $itemsPerPage;
		$exams = $data['data'];
	}
		
} catch (Exception $e) {    
    $error = "Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau.";
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
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .content {
            flex: 1 0 auto;
            padding-bottom: 60px;
        }
        .content .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .table-responsive {
            margin-bottom: 20px;
        }
        .table {
            background-color: #ffffff;
        }
        .table th {
            background-color: #007bff;
            color: #ffffff;
        }
        .pagination {
            justify-content: center;
        }
        .btn-view, .btn-edit {
            white-space: nowrap;
        }                
        @media (max-width: 576px) {
            .search-box input {
                font-size: 16px;
            }
            .table {
                font-size: 14px;
            }
        }		
		/*USER INFO*/
		.user-info-container {
			position: relative;
			background-color: #ffffff;
			border-radius: 8px;
			padding: 0 15px 0 15px;
			box-shadow: 0 4px 6px rgba(0,0,0,0.1);
			transition: all 0.3s ease;
			max-width: 300px;
			overflow: hidden;
		}
		.user-info {
			font-size: 0.9rem;
			color: #333;
		}
		.user-info p {
			margin-bottom: 5px;
		}
		.user-info strong {
			font-weight: 600;
			color: #007bff;
		}
		.user-rank-ribbon {
			position: absolute;
			cursor: default;
			top: 15px;
			right: -30px;
			transform: rotate(45deg);
			background-color: #28a745;
			color: #fff;
			padding: 5px 30px;
			font-size: 0.8rem;
			font-weight: bold;
			text-transform: uppercase;
			box-shadow: 0 2px 4px rgba(0,0,0,0.2);
			z-index: 1;
		}
		.user-details {
			/*margin-top: 5px;*/
		}
		.user-name {
			margin: 0 0 10px 0;
			font-weight: bold;
			font-size: 1rem;
			padding-right: 40px; /* Để tránh bị che bởi ribbon */
		}
		.btn-logout {
			display: inline-block;
			background-color: transparent;
			color: #dc3545;
			border: none;			
			font-size: 0.9rem;
			transition: color 0.3s ease;
			margin-bottom: 5px;
		}
		.btn-logout:hover {
			color: #c82333;
			text-decoration: none;
		}		
		.btn-ebank {
			margin-left: 5px;
		}
		
		.container-i ul {
            list-style: none;
            padding: 0;
        }

        .container-i li {
            margin-bottom: 15px;
            color: #555;
            display: flex;
            align-items: center;
        }

        .container-i li::before {
            content: "-";
            margin-right: 10px;
            color: #007BFF;
        }
		
		@media (max-width: 576px) {
			.user-info-container {
				position: static;
				padding: 0;
				padding-right: 5px;
			}			
			#userInfo {
				position: fixed;
				top: auto;
				bottom: 0;
				left: 0;
				right: 0;
				background-color: #ffffff;
				border-radius: 8px 8px 0 0;
				padding: 15px;
				box-shadow: 0 -4px 6px rgba(0,0,0,0.1);
				z-index: 1000;
				width: 100%;
				max-width: 100%;
				transform: translateY(100%);
				transition: transform 0.3s ease;
			}
			#userInfo.collapse:not(.show) {
				display: none;
			}
			#userInfo.collapsing {
				height: auto;
			}
			#userInfo.show {
				transform: translateY(0);
				overflow: hidden;
			}
			.btn-toggle-user-info {
				background-color: #007bff;
				color: #fff;
				border: none;
				padding: 8px 12px;
				border-radius: 4px;
				font-size: 1rem;
				transition: background-color 0.3s ease;
			}
			.btn-toggle-user-info:hover, .btn-toggle-user-info:focus {
				background-color: #0056b3;
				color: #fff;
			}
			.user-info {
				max-height: 80vh;
				overflow-y: auto;
			}
		}
    </style>
</head>
<body>
<div class="content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
			<h1><i class="fas fa-info-circle" title="Hướng dẫn" onclick="scrollToClass('container-i')"></i> Dữ liệu học tập</h1>
			<div class="user-info-container">
				<button class="btn btn-toggle-user-info d-md-none" type="button" data-toggle="collapse" data-target="#userInfo" aria-expanded="false" aria-controls="userInfo">
					<i class="fas fa-user"></i>
				</button>
				<div class="collapse d-md-block" id="userInfo">
					<div class="user-info">
						<div class="user-rank-ribbon">
							<span title="<?php echo isset($udat['period_premium']) ? 'to '.date('d.m.Y', strtotime($udat['period_premium'])) : ''; ?>"><?php echo $hasPremium ? 'Premium' : 'Free User'; ?></span>
						</div>
						<div class="user-details">
							<p class="user-name">Xin chào: <?php echo isset($udat['full_name']) ? $udat['full_name'] : ''; ?></p>
							<a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
							<p title="cứ mỗi lần hoàn thành bài thi chung Pass bạn sẽ được cộng một cơ số điểm!"><strong>Điểm học tập:</strong> <?php echo isset($udat['study_point']) ? number_format($udat['study_point'], 0, ',', '.') : 0; ?></p>

<?php if($hasAdv): ?>	
    <a href="buyvip.php" class="btn-upgrade"><i class="fas fa-crown"></i> Nâng Cấp VIP</a>
<?php else: ?>
    <a href="buyvip.php" class="btn-upgrade"><i class="fas fa-crown"></i> Xem quyền VIP</a>
<?php endif; ?>

						</div>
					</div>
				</div>
			</div>
		</div>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $tab === 'all' ? 'active' : ''; ?>" href="?tab=all">Đề thi chung <?php echo $tab === 'all' && $totalItems > 0 ? "($totalItems)" : '';?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $tab === 'user' ? 'active' : ''; ?>" href="?tab=user">Đề thi của tôi <?php echo $tab === 'user' && $totalItems > 0 ? "($totalItems)" : '';?></a>
            </li>
			<li class="nav-item">
                <a class="nav-link <?php echo $tab === 'history' ? 'active' : ''; ?>" href="?tab=history">Lịch sử thi <?php echo $tab === 'history' && $totalItems > 0 ? "($totalItems)" : '';?></a>
            </li>
			<?php if ($isAdmin || $hasPremium): ?>
			<li class="nav-item">
                <a class="nav-link <?php echo $tab === 'teacher' ? 'active' : ''; ?>" href="?tab=teacher">Bài chấm thi <?php echo $tab === 'teacher' && $totalItems > 0 ? "($totalItems)" : '';?></a>
            </li>			
			<?php endif; ?>
			<li class="nav-item">
                <a class="nav-link <?php echo $tab === 'ebank' ? 'active' : ''; ?>" href="?tab=ebank">Ngân hàng đề thi <?php echo $tab === 'ebank' && $totalItems > 0 ? "($totalItems)" : '';?></a>
            </li>			
        </ul>

        <div class="d-flex justify-content-between mb-3">		
<?php if($tab === 'user') {?>
            <button class="btn btn-primary" onclick="addExam()"><i class="fas fa-plus"></i> Thêm đề thi</button>
<?php } ?>			
<?php if($tab === 'ebank') {?>			
			<button class="btn btn-primary btn-ebank" onclick="addEBank()"><i class="fas fa-plus"></i> Thêm ebank</button>
			<button class="btn btn-primary btn-ebank" onclick="importExam()"><i class="fas fa-upload"></i> Import ebank</button>
<?php } ?>			
            &nbsp;
			<form action="" method="GET" class="search-box">
                <div class="input-group">
                    <input type="hidden" name="tab" value="<?php echo $tab; ?>">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm đề thi..." value="<?php echo htmlspecialchars($search); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
		
<table class="table table-bordered table-hover" id="examTable">
    <thead class="thead-light">
        <tr>
            <?php if ($tab === 'history' || $tab === 'teacher'): ?>
                <th>STT</th>
                <th>Tên đề thi</th>
                <th>Token</th>
                <th>Ngày làm bài</th>
            <?php else: ?>
                <th>Tên đề thi</th>
                <th>Thời gian (phút)</th>
                <th>Số câu hỏi</th>
                <th>Điểm đạt</th>
                <th>Ngày tạo</th>
            <?php endif; ?>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($exams)): ?>
        <tr>
            <td colspan="6" class="text-center">Không có dữ liệu để hiển thị.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($exams as $index => $exam): 
            $isOwner = isset($exam['is_owner']) ? $exam['is_owner'] : false;
            $isPublic = isset($exam['is_public']) ? $exam['is_public'] : false;
            $lockHtml = $isPublic ? "<i class='fas fa-unlock'></i> Lock" : "<i class='fas fa-lock'></i> UnLock";
        ?>
            <tr id="exam-row-<?php echo $exam['id']; ?>">
                <?php if ($tab === 'history' || $tab === 'teacher'): ?>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($exam['name']); ?></td>
                    <td><?php echo $exam['session_id']; ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($exam['created_time'])); ?></td>
                <?php else: ?>
                    <td><?php echo htmlspecialchars($exam['name']); ?></td>
                    <td><?php echo $exam['duration']; ?></td>
                    <td><?php echo $exam['number_questions']; ?></td>
                    <td><?php echo $exam['pass_score']; ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($exam['created_time'])); ?></td>
                <?php endif; ?>
                <td>
                    <button class="btn btn-sm btn-view" onclick="viewExam(<?php echo ($tab === 'history' || $tab === 'teacher') ? '`'.$exam['session_id'] . '`' : $exam['id']; ?>)">
                        <i class="fas <?php echo $tab === 'ebank' ? 'fa-file-alt' : 'fa-eye';?>"></i> Xem
                    </button>
                    <?php if ($tab === 'teacher111'): ?>
                        <button class="btn btn-sm btn-edit" onclick="gradeExam(<?php echo $exam['id']; ?>)">
                            <i class="fas fa-edit"></i> Chấm thi
                        </button>
                    <?php endif; ?>
                    <?php if ((($tab === 'user' || $tab === 'ebank') && $isOwner) || ($tab === 'all' && $isAdmin)): ?>
                        <button class="btn btn-sm btn-edit" onclick="editExam(<?php echo $exam['id']; ?>)">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button class="btn btn-sm btn-delete" onclick="deleteExam(<?php echo $exam['id']; ?>)">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    <?php endif; ?>
                    <?php if ($tab === 'user' && $isOwner): ?>
                        <button class="btn btn-sm btn-clone" onclick="cloneExam(<?php echo $exam['id']; ?>)">
                            <i class="fas fa-clone"></i> Clone
                        </button>
                        <button class="btn btn-sm btn-clone" onclick="lockExam(<?php echo $exam['id']; ?>)">
                            <?php echo $lockHtml; ?>
                        </button>
                    <?php endif; ?>
                    <?php if ($tab === 'user' && $isAdmin): ?>
                        <button class="btn btn-sm btn-copy" onclick="copyExam(<?php echo $exam['id']; ?>)">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    <?php endif; ?>
					<?php if ($tab === 'all' || ($tab === 'user' && $isPublic && $isOwner)): ?>
                        <button class="btn btn-sm btn-share" onclick="shareLink('/study/eview.php?id=<?php echo $exam['id']; ?>')">
							<i class="fas fa-share-alt"></i> Share
						</button>
                    <?php endif; ?>
					<?php if ($tab === 'ebank'): ?>
                        <button class="btn btn-sm btn-copy" onclick="exportExam(<?php echo $exam['id']; ?>)">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
        
		</div>
<?php if (!empty($exams) && $totalPages){ ?>		
        <nav>
			<ul class="pagination">
				<?php
				$range = 2; // Số trang hiển thị ở hai bên trang hiện tại
				$showitems = ($range * 2) + 1; // Tổng số trang hiển thị (không bao gồm nút đầu, cuối)

				// Nút Trang đầu
				if ($currentPage > 1) {
					echo "<li class='page-item'><a class='page-link' href='?page=1&search=" . urlencode($search) . "&tab=$tab'>«</a></li>";
				}

				// Nút Trang trước
				if ($currentPage > 1) {
					echo "<li class='page-item'><a class='page-link' href='?page=" . ($currentPage - 1) . "&search=" . urlencode($search) . "&tab=$tab'>‹</a></li>";
				}

				for ($i = 1; $i <= $totalPages; $i++) {
					if ($i == 1 || $i == $totalPages || $i >= $currentPage - $range && $i <= $currentPage + $range) {
						echo ($i == $currentPage) 
							? "<li class='page-item active'><span class='page-link'>$i</span></li>"
							: "<li class='page-item'><a class='page-link' href='?page=$i&search=" . urlencode($search) . "&tab=$tab'>$i</a></li>";
					} elseif ($i == $currentPage - $range - 1 || $i == $currentPage + $range + 1) {
						echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
					}
				}

				// Nút Trang sau
				if ($currentPage < $totalPages) {
					echo "<li class='page-item'><a class='page-link' href='?page=" . ($currentPage + 1) . "&search=" . urlencode($search) . "&tab=$tab'>›</a></li>";
				}

				// Nút Trang cuối
				if ($currentPage < $totalPages) {
					echo "<li class='page-item'><a class='page-link' href='?page=$totalPages&search=" . urlencode($search) . "&tab=$tab'>»</a></li>";
				}
				?>
			</ul>
		</nav>
<?php } ?>		
    </div>

	<div class="container container-i">
		<h1>Hướng Dẫn Sử Dụng</h1>
		<p><i class="fas fa-plus"></i> Đăng kỳ tài khoản, tạo đề thi:</p>
		<ul>
			<li>Để đăng ký tài khoản bạn cần có một email có thể nhận mã kích hoạt, khi đăng ký tài khoản bạn sẽ được chuyển hướng tới hệ thống gamefam của chúng tôi, sau khi đăng ký thành công bạn có thể quay lại hệ thống thi để sử dụng.</li>
			<li>Bạn có thể tạo đề thi cá nhân và Ngân hàng đề thi để tạo đề thi cá nhân cực nhanh.</li>
			<li>Bạn có thể import/export 1 ngân hàng đề thi để chuyển giao cho người khác cùng sử dụng.</li>			
			<li>Đề thi là do bạn tự tạo ra và toàn quyền sở hữu, có thể rèn luyện để nâng cao tri thức một cách miễn phí.</li>
			<li>Bạn có thể nhập công thức toán học và soạn thảo dễ dàng trên hệ thống, để soạn thảo công thức toán học thì nhấn vào biểu tượng Omega.</li>
			<li>Bạn có thể chèn ảnh, nhúng link để dễ minh họa và trực quan hơn nếu cần thiết.</li>
		</ul>
		<p><i class="fas fa-share-alt"></i> Chia sẻ và sử dụng hệ thống:</p>
		<ul>
			<li>Những bài thi hệ thống hoặc cá nhân mới có thể chia sẻ.</li>
			<li>Những bài thi cá nhân muốn chia sẻ cho bạn bè cần mở khóa (un-lock).</li>			
			<li>Khi cần đưa bài thi cá nhân của bạn lên hệ thống thì cần liên hệ admin để phê duyệt, bạn cung cấp thông tin về bài thi để admin đưa lên hệ thống.</li>
		</ul>
		<p><i class="fas fa-edit"></i> Cách chỉnh sửa:</p>
		<ul>
			<li>Khi soạn thảo và chỉnh sửa bài thi bạn cần có các nguồn dữ liệu chuẩn bị trước, session đăng nhập của bạn chỉ khoảng 120 phút (2h) nếu quá thời gian đăng nhập và bạn chưa lưu thông tin soạn thảo có thể bị mất.</li>
			<li>Bạn có thể clone 1 bài thi cá nhân có sẵn để tạo thành 1 bài thi mới cực nhanh.</li>			
			<li>Bạn có thể tận dụng các menu điều hướng để di chuyển tới câu hỏi bất kỳ nhanh chóng.</li>	
			<li>Bạn có thể lọc, cài đặt tài khoản giáo viên, tài khoản thi, địa chỉ ip để nộp bài, nếu tài khoản là VIP! khi tạo đề thi ngẫu nhiên từ ngân hàng, Nếu không nhập hoặc bỏ trống tức là không yêu cầu và là loại đề thi bình thường.</li>			
		</ul>
		<p><i class="fas fa-pencil-alt"></i> Thi và làm bài thi:</p>
		<ul>
			<li>Khi bấm nút bắt đầu làm bài thi hệ thống sẽ đếm ngược thời gian mà bài thi đã cài đặt, nếu hết thời gian bài thi sẽ bị khóa và không thi được nữa.</li>
			<li>Khi bắt đầu thi rồi thì bạn có thể copy link địa chỉ trên trình duyệt để lưu lại và mở lại bất cứ lúc nào, nếu còn thời gian thì vẫn có thể làm bài tiếp tục.</li>	
			<li>Bạn không cần đăng nhập vẫn có thể Thi được với định danh là tài khoản anonymous (người lạ) nhưng sẽ không xem được lịch sử bài thi.</li>			
		</ul>
	</div>
</div>

<footer class="footer">
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
<script src="../Assets/js/exam.js"></script>
<script>
    function viewExam(examId) {
<?php if($tab === 'history' || $tab === 'teacher'):?>		
        window.location.href = `eview.php?session_id=${examId}`;
<?php else:?>		
		window.location.href = `eview.php?id=${examId}`;
<?php endif;?>		
    }

    function editExam(examId) {
        window.location.href = `exam.php?id=${examId}`;
    }

    function addExam() {
        window.location.href = 'exam.php';
    }
<?php if($tab === 'ebank') {?>	
	function addEBank() {
        window.location.href = 'exam.php?ebank=1';
    }
	
	function exportExam(examId) {
		handleExamAction('exportexam', examId);
	}
	
	function importExam() {
		var input = document.createElement('input');
		input.type = 'file';
		input.accept = '.xml';
		input.onchange = function(event) {
			var file = event.target.files[0];
			var reader = new FileReader();
			reader.onload = function(e) {
				var xmlContent = e.target.result;
				new AjaxRequest('process.php', 'POST', 'importexam', xmlContent)
					.success(function(response) {
						if (response.success) {
							showMessage(response.status_code, response.message);
							setTimeout(function() { location.reload(); }, 1500);
						} else {
							showMessage(response.status_code, response.message);
						}
					})
					.error(function(error) {
						alert('Có lỗi xảy ra khi import đề thi.');
					})
					.send();
			};
			reader.readAsText(file);
		};
		input.click();
	}
<?php }?>
	function deleteExam(examId) {
		handleExamAction('delete', examId);
	}
	
	function cloneExam(examId) {
        handleExamAction('clone', examId);
    }
	
	function lockExam(examId) {
        handleExamAction('onofflock', examId);
    }
<?php if($tab === 'user' && $isAdmin) {?>

	function copyExam(examId) {
        handleExamAction('makecopy', examId);
    }
<?php }?>

	function gradeExam(examId) {
		// Implement grading functionality
		alert('Chức năng chấm thi cho đề thi ' + examId);
	}

	function handleExamAction(action, examId, additionalData = {}) {
		let confirmMessage = '';
		switch(action) {
			case 'delete':
				confirmMessage = 'Bạn có chắc chắn muốn xóa đề thi này?';
				break;
			case 'clone':
				confirmMessage = 'Bạn có muốn tạo một bản sao cho đề thi này?';
				break;
			case 'onofflock':
				confirmMessage = 'Bạn có muốn đóng mở public đề thi này?';
				break;
<?php if($tab === 'ebank') {?>									
			case 'exportexam':
				confirmMessage = 'Bạn có muốn thực hiện xuất dữ liệu đề thi này ra tệp?';
				break;
<?php } if($tab === 'user' && $isAdmin) {?>				
			case 'makecopy':
				confirmMessage = 'Bạn có muốn tạo một bản copy hệ thống cho đề thi này?';
				break;			
<?php }?>				
		}

		if (confirm(confirmMessage)) {			
			new AjaxRequest('process.php', 'POST', action, examId)
				.success(function(response) {					
					if (response.success) {						
						switch(action) {
							case 'delete':
								$(`#exam-row-${examId}`).remove();
								if ($('#examTable tbody tr').length === 0) {
									$('#examTable tbody').html('<tr><td colspan="6" class="text-center">Không có dữ liệu để hiển thị.</td></tr>');
								}
								break;
<?php if($tab === 'ebank') {?>							
							case 'exportexam':
								var blob = new Blob([response.data], {type: 'application/xml'});
								var link = document.createElement('a');
								link.style.display = 'none';
								link.href = window.URL.createObjectURL(blob);								
								link.download = 'exam_' + examId + '.xml';
								link.click();								
								break;
<?php }?>																		
							case 'clone':
							case 'onofflock':
<?php if($tab === 'user' && $isAdmin) {?>	
							case 'makecopy':
<?php }?>										
								setTimeout(function() { location.reload(); }, 1500);
								break;							
						}
					}
					if(action !== 'exportexam')
						showMessage(response.status_code, response.message);
				})
				.error(function(error) {
					alert('Có lỗi xảy ra khi gửi yêu cầu.');
				})
				.send();
		}
	}

    $(document).ready(function() {                
		// Lazy loading cho bảng
		/*
        var lazyLoadTable = function() {
            var windowBottom = $(window).scrollTop() + $(window).height();
            var rows = $("#examTable tbody tr");
            rows.each(function() {
                var row = $(this);
                if (row.offset().top <= windowBottom) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        };

        $(window).scroll(lazyLoadTable);
        lazyLoadTable();*/
    });
</script>
</body>
</html>