<?php
session_start();
$pass = $_SESSION['admin_pass'] ?? $_GET['pass'] ?? '';
if ($pass !== '4444') {
    die("Unauthorized Access. <a href='index.php'>Go Back</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Chat - Existing Chats</title>
    <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-4.15.0.min.css" />
    <style>
        body { background-color: #f7f7f7; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .chat-item { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem; margin-bottom: 0.5rem; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .chat-info { flex-grow: 1; }
        .chat-name { font-weight: bold; font-size: 1.1rem; }
        .chat-code { color: #666; font-family: monospace; }
        .delete-btn { color: #c7162b; cursor: pointer; border: none; background: none; }
    </style>
</head>
<body>

    <script>
        // Clean URL immediately
        if (window.location.search || window.location.pathname.endsWith('.php')) {
            window.history.replaceState({}, '', 'chat_list');
        }
    </script>

    <div class="container text-center">
        <div class="header">
            <h3>📂 Existing Chats</h3>
            <a href="index.php" class="p-button">Back Home</a>
        </div>

        <div id="chats-container">
            <p>Loading chats...</p>
        </div>
    </div>

    <script>
        const pass = <?php echo json_encode($pass); ?>;

        async function loadChats() {
            const response = await fetch(`actions.php?action=list_chats&password=${pass}`);
            const result = await response.json();
            const container = document.getElementById('chats-container');
            
            if (result.status === 'success') {
                if (result.chats.length === 0) {
                    container.innerHTML = '<div class="p-notification--information"><p class="p-notification__content">No active chats found.</p></div>';
                    return;
                }
                
                container.innerHTML = result.chats.map(chat => `
                    <div class="chat-item">
                        <div class="chat-info">
                            <div class="chat-name">${chat.name}</div>
                            <div class="chat-code">Code: ${chat.code} | Created: ${chat.created_at}</div>
                        </div>
                        <div class="chat-actions" style="display:flex; gap:8px;">
                            <button class="p-button--brand u-no-margin--bottom" onclick="joinChat('${chat.code}')">Join</button>
                            <button class="p-button--negative u-no-margin--bottom" onclick="deleteChat('${chat.code}')">Delete</button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `<div class="p-notification--negative"><p class="p-notification__content">${result.message}</p></div>`;
            }
        }

        async function joinChat(code) {
            const formData = new FormData();
            formData.append('action', 'set_session');
            formData.append('code', code);
            formData.append('user', 'Admin');
            const response = await fetch('actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = 'chat.php';
            }
        }

        async function deleteChat(code) {
            if (!confirm(`Are you sure you want to delete chat ${code}?`)) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_chat');
            formData.append('code', code);
            formData.append('password', pass);
            
            const response = await fetch('actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                loadChats();
            } else {
                alert(result.message);
            }
        }

        loadChats();
    </script>
</body>
</html>
