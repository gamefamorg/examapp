<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$udat = $_SESSION['usrdatav'];
$isAdmin = isset($udat['is_admin']) ? $udat['is_admin'] : false;
$hasPremium = isset($udat['has_premium']) ? $udat['has_premium'] : false;
$hasAdv = !($isAdmin || $hasPremium);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>So sánh quyền lợi VIP</title>
    <link rel="icon" type="image/ico" href="https://gamefam.org/favicon.ico">
    <link rel="stylesheet" href="../Assets/css/bootstrap-4.3.1.min.css">
    <link rel="stylesheet" href="../Assets/css/font-awesome-5.15.3.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .pricing-header {
            max-width: 500px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            min-width: 600px;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table th:first-child, .table td:first-child {
            text-align: left;
        }
        .premium-col {
            background-color: #fff9e6;
        }
        .btn-premium {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .btn-premium:hover {
            background-color: #e0a800;
            border-color: #e0a800;
        }
        .feature-icon {
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
            margin-right: 10px;
        }
        .text-premium {
            color: #ffc107;
        }
		.price-row {
			font-size: 1.2em;
			font-weight: bold;
		}

		.premium-price {
			color: #ffc107;
			font-size: 1.3em;
			font-weight: bold;
		}

		.signup-row td {
			padding: 20px 10px;
		}

		.btn-premium {
			background-color: #ffc107;
			border-color: #ffc107;
			color: #000;
			font-weight: bold;
			padding: 10px 20px;
		}

		.btn-premium:hover {
			background-color: #e0a800;
			border-color: #e0a800;
		}
    </style>
</head>
<body>
    <div class="container">
        <div class="pricing-header mx-auto text-center">
            <h1 class="display-4">Quyền lợi VIP</h1>            
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tiêu chí</th>
                        <th>Free User</th>
                        <th class="premium-col">Premium User</th>
                    </tr>
                </thead>
                <tbody>
					<?php if($hasAdv){ ?>
                    <tr class="price-row">
						<td><strong>Giá</strong></td>
						<td>0đ / Free</td>
						<td class="premium-col">
							<span class="premium-price">100.000đ / 3 tháng</span>							
						</td>
					</tr>                   
                    <?php } ?>
                    <tr>
                        <td>Giới hạn đề thi</td>
                        <td>20</td>
                        <td class="premium-col">100</td>
                    </tr>
                    <tr>
                        <td>Giới hạn câu hỏi/đề thi</td>
                        <td>40</td>
                        <td class="premium-col">100</td>
                    </tr>
                    <tr>
                        <td>Giới hạn câu trả lời/câu hỏi</td>
                        <td>4</td>
                        <td class="premium-col">12</td>
                    </tr>
                    <tr>
                        <td>Giới hạn delete/ngày</td>
                        <td>5</td>
                        <td class="premium-col">30</td>
                    </tr>
                    <tr>
                        <td>Giới hạn update/ngày</td>
                        <td>30</td>
                        <td class="premium-col">120</td>
                    </tr>
                    <tr>
                        <td>Giới hạn luyện tập/ngày</td>
                        <td>24</td>
                        <td class="premium-col">∞</td>
                    </tr>
                    <tr>
                        <td>Session đã thi lưu trữ</td>
                        <td>50</td>
                        <td class="premium-col">200</td>
                    </tr>
                    <tr>
                        <td>Xem Đáp Án đúng sai</td>
                        <td><i class="fas fa-times-circle text-danger"></i> No</td>
                        <td class="premium-col"><i class="fas fa-check-circle text-success"></i> Yes</td>
                    </tr>                    				
					<tr>
                        <td>Chức năng chấm thi</td>
                        <td><i class="fas fa-times-circle text-danger"></i> No</td>
                        <td class="premium-col"><i class="fas fa-check-circle text-success"></i> Yes</td>
                    </tr>					
					<tr>
                        <td>Số lượng ngân hàng đề thi</td>
                        <td>5 (tối đa 500 câu hỏi)</td>
                        <td class="premium-col">50 (tối đa 10k câu hỏi)</td>
                    </tr>
					<tr>
                        <td>Số lượng câu hỏi/ngân hàng</td>
                        <td>100</td>
                        <td class="premium-col">200</td>
                    </tr>					
					<tr>
                        <td>Quảng cáo</td>
                        <td><i class="fas fa-check-circle text-warning"></i> Yes</td>
                        <td class="premium-col"><i class="fas fa-ban text-success"></i> No</td>
                    </tr>
                    <?php if($hasAdv){ ?>                    
                    <tr>
                        <td></td>
                        <td><button type="button" class="btn btn-outline-primary btn-register">Đăng ký miễn phí</button></td>
                        <td class="premium-col"><button type="button" class="btn btn-premium btn-login">Nâng cấp ngay</button></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../Assets/js/jquery-3.3.1.min.js"></script>
    <script src="../Assets/js/popper-1.14.7.min.js"></script>
    <script src="../Assets/js/bootstrap-4.3.1.min.js"></script>    
    <script>
        $(document).ready(function() {        
            <?php if($hasAdv){ ?>
            $('.btn-register').click(function() {
                window.location.href = 'https://gamefam.org/register/index.php';
            });
            $('.btn-login').click(function() {
                window.location.href = 'https://gamefam.org/login.php';
            });
            <?php } ?>            
        });
    </script>
</body>
</html>