<?php
session_start();

$storageDir = __DIR__ . '/chats/';
$adminPasswd = "4444";

function generateCode($length = 4) {
    return substr(str_shuffle(str_repeat($x='ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_chat') {
        $chatName = trim($_POST['name'] ?? 'Anonymous');
        $code = generateCode();
        $file = $storageDir . $code . '.txt';
        
        $initialData = [
            'name' => $chatName,
            'created_at' => date('Y-m-d H:i:s'),
            'messages' => []
        ];
        
        // We will store metadata in the first line and messages in subsequent lines
        file_put_contents($file, json_encode($initialData) . "\n");
        
        echo json_encode(['status' => 'success', 'code' => $code]);
        exit;
    }

    if ($action === 'send_message') {
        $code = $_POST['code'] ?? '';
        $user = $_POST['user'] ?? 'Guest';
        $message = $_POST['message'] ?? '';
        $file = $storageDir . $code . '.txt';

        if (file_exists($file)) {
            $data = [
                'user' => $user,
                'message' => $message,
                'time' => date('H:i:s'),
                'timestamp' => time()
            ];
            file_put_contents($file, json_encode($data) . "\n", FILE_APPEND);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Chat not found']);
        }
        exit;
    }

    if ($action === 'delete_chat') {
        $code = $_POST['code'] ?? '';
        $password = $_POST['password'] ?? '';
        $file = $storageDir . $code . '.txt';

        if ($password === $adminPasswd && file_exists($file)) {
            unlink($file);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized or file mismatch']);
        }
        exit;
    }

    if ($action === 'set_session') {
        $code = $_POST['code'] ?? '';
        $user = $_POST['user'] ?? '';
        if ($code && $user) {
            $_SESSION['chat_code'] = $code;
            $_SESSION['chat_user'] = $user;
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing code or user']);
        }
        exit;
    }

    if ($action === 'set_admin_session') {
        $password = $_POST['password'] ?? '';
        if ($password === $adminPasswd) {
            $_SESSION['admin_pass'] = $password;
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Passcode']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_messages') {
        $code = $_GET['code'] ?? '';
        $lastLine = intval($_GET['last_line'] ?? 0);
        $file = $storageDir . $code . '.txt';

        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $newMessages = [];
            $totalLines = count($lines);

            for ($i = max(1, $lastLine + 1); $i < $totalLines; $i++) {
                $msg = json_decode($lines[$i], true);
                if ($msg) {
                    $newMessages[] = $msg;
                }
            }

            echo json_encode([
                'status' => 'success',
                'messages' => $newMessages,
                'last_line' => $totalLines - 1,
                'chat_info' => json_decode($lines[0], true)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Chat not found']);
        }
        exit;
    }

    if ($action === 'list_chats') {
        $password = $_GET['password'] ?? '';
        if ($password === $adminPasswd) {
            $files = glob($storageDir . '*.txt');
            $chats = [];
            foreach ($files as $file) {
                $code = basename($file, '.txt');
                $handle = fopen($file, 'r');
                $firstLine = fgets($handle);
                fclose($handle);
                $info = json_decode($firstLine, true);
                $chats[] = [
                    'code' => $code,
                    'name' => $info['name'] ?? 'Unknown',
                    'created_at' => $info['created_at'] ?? 'N/A'
                ];
            }
            echo json_encode(['status' => 'success', 'chats' => $chats]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
        exit;
    }
}
