<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据库安装</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
        }
        form {
            display: inline-block;
            text-align: left;
        }
        input[type="text"], input[type="password"] {
            padding: 10px;
            margin: 10px 0;
            width: 300px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
        .success-message {
            margin-top: 20px;
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<h1>数据库安装</h1>
<form method="post">
    <label for="servername">服务器地址:</label><br>
    <input type="text" id="servername" name="servername" value="<?php if(isset($_POST['servername'])){echo $_POST['servername'];}else{ echo "localhost";}  ?>" ><br>
    <label for="username">数据库账号:</label><br>
    <input type="text" id="username" name="username" value="<?php echo $_POST['username']; ?>" ><br>
    <label for="password">数据库密码:</label><br>
    <input type="password" id="password" name="password" value="<?php echo $_POST['password']; ?>"><br>
    <label for="dbname">数据库名:</label><br>
    <input type="text" id="dbname" name="dbname" value="<?php echo $_POST['dbname']; ?>"><br><br>
    <input type="submit" name="checkConnection" value="检查数据库连接" style="background: rgba(110,162,249,0.96)">
    <input type="submit" name="install" value="安装">

</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 获取用户输入的数据库连接信息
    $servername = $_POST['servername'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];
    if (isset($_POST['checkConnection'])) {

        // 检查数据库连接
        $conn = @mysqli_connect($servername, $username, $password);
        if (!$conn) {
            echo '<div class="error-message">数据库连接失败: ' . mysqli_connect_error() . '</div>';
        } else {
            echo '<div class="success-message">数据库连接成功</div>';

        }

    } else if (isset($_POST['install'])) {
        // 创建数据库连接
        $conn = new mysqli($servername, $username, $password);

        // 检查连接是否成功
        if ($conn->connect_error) {
            die("连接失败: " . $conn->connect_error);
        }

        // 选择数据库
        $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
        $conn->select_db($dbname);

        // 读取 SQL 文件并替换数据库名后执行
        $sql = file_get_contents('sql/install.sql');
        $sql = str_replace('database_name_placeholder', $dbname, $sql); // 将占位符替换为用户输入的数据库名
        if ($conn->multi_query($sql) === TRUE) {
            echo '<div class="success-message">数据库安装成功</div>';
            // 将用户输入的数据库连接信息写入配置文件
            $config = "<?php\nreturn [\n    'hostname' => '$servername',\n    'username' => '$username',\n    'password' => '$password',\n    'database' => '$dbname',\n];";
            file_put_contents('config/database.php', $config);
        } else {
            echo "Error: " . $conn->error;
        }

        // 关闭连接
        $conn->close();
    }


}
?>
</body>
</html>
