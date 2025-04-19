<?php
session_start();
$data_file = "chat_data.txt"; // 所有数据存储文件

// ================== 核心功能函数 ==================
function generateRandomID($seq) { // 顺序ID转随机ID
    return ($seq * 16807) % 1000000; // 生成6位随机数
}

function saveUser($username, $password) {
    global $data_file;
    $users = file($data_file);
    $max_id = 0;
    
    // 查找最大顺序ID
    foreach ($users as $line) {
        if (strpos($line, "USER|") === 0) {
            $parts = explode("|", trim($line));
            $max_id = max($max_id, intval($parts[1]));
        }
    }
    
    $new_id = $max_id + 1;
    $random_id = generateRandomID($new_id);
    $data = "USER|$new_id|$random_id|$username|".md5($password)."|".PHP_EOL;
    file_put_contents($data_file, $data, FILE_APPEND);
    return $random_id;
}

// ================== 主逻辑处理 ==================
if (isset($_POST['register'])) { // 注册处理
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // 检查用户名是否存在
    $users = file($data_file);
    foreach ($users as $line) {
        if (strpos($line, "USER|") === 0) {
            $parts = explode("|", trim($line));
            if ($parts[3] == $username) {
                die("用户名已存在");
            }
        }
    }
    
    $user_id = saveUser($username, $password);
    echo "注册成功！您的ID：$user_id";
}

if (isset($_POST['login'])) { // 登录处理
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));
    
    $users = file($data_file);
    foreach ($users as $line) {
        if (strpos($line, "USER|") === 0) {
            $parts = explode("|", trim($line));
            if ($parts[3] == $username && $parts[4] == $password) {
                $_SESSION['user_id'] = $parts[2]; // 存储随机ID
                $_SESSION['username'] = $parts[3];
                header("Location: chat.php");
                exit;
            }
        }
    }
    echo "登录失败";
}

// ================== HTML界面 ==================
if (!isset($_SESSION['user_id'])): // 登录/注册表单
?>
<!DOCTYPE html>
<html>
<head>
    <title>聊天系统</title>
    <style>
        .container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; }
        .tab { display: flex; margin-bottom: 20px; }
        .tab button { flex: 1; padding: 10px; border: none; cursor: pointer; }
        .tab button.active { background: #007bff; color: white; }
        form div { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="tab">
            <button onclick="showTab('login')" class="active">登录</button>
            <button onclick="showTab('register')">注册</button>
        </div>
        
        <form id="loginForm" method="post">
            <div><input type="text" name="username" placeholder="用户名" required></div>
            <div><input type="password" name="password" placeholder="密码" required></div>
            <input type="submit" name="login" value="登录">
        </form>

        <form id="registerForm" method="post" style="display:none;">
            <div><input type="text" name="username" placeholder="用户名" required></div>
            <div><input type="password" name="password" placeholder="密码" required></div>
            <input type="submit" name="register" value="注册">
        </form>
    </div>

    <script>
        function showTab(tab) {
            document.getElementById('loginForm').style.display = 
                (tab === 'login') ? 'block' : 'none';
            document.getElementById('registerForm').style.display = 
                (tab === 'register') ? 'block' : 'none';
        }
    </script>
</body>
</html>
<?php else: // 已登录显示聊天界面 ?>
<!DOCTYPE html>
<html>
<head>
    <title>聊天室 - <?php echo $_SESSION['username']; ?></title>
    <style>
        .chat-container { max-width: 800px; margin: 0 auto; }
        .friend-list { width: 30%; float: left; }
        .chat-box { width: 70%; float: right; }
    </style>
</head>
<body>
    <div class="chat-container">
        <h2>欢迎 <?php echo $_SESSION['username']; ?></h2>
        
        <div class="friend-list">
            <h3>好友列表</h3>
            <!-- 好友列表显示逻辑 -->
        </div>

        <div class="chat-box">
            <!-- 聊天消息显示区域 -->
        </div>
    </div>
</body>
</html>
<?php endif; ?>
