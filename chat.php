<?php
session_start();
$code = $_SESSION['chat_code'] ?? $_GET['code'] ?? '';
$user = $_SESSION['chat_user'] ?? $_GET['user'] ?? 'Guest';

if (!$code) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat: <?php echo htmlspecialchars($code); ?></title>
    <!-- Vanilla Framework -->
    <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-4.15.0.min.css" />
    <!-- Highlight.js Themes -->
    <link id="hljs-theme" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <style>
        :root {
            --bg-color: #ffffff;
            --header-bg: #ffffff;
            --border-color: #f0f0f0;
            --accent-color: #3b82f6;
            --text-color: #333333;
            --msg-bg-other: #f6f6f6;
            --msg-bg-self: #ffffff;
            --input-bg: #ffffff;
        }
        
        .dark-mode {
            --bg-color: #1a1a1a;
            --header-bg: #2d2d2d;
            --border-color: #333333;
            --text-color: #e0e0e0;
            --msg-bg-other: #2d2d2d;
            --msg-bg-self: #3d3d3d;
            --input-bg: #2d2d2d;
        }

        body { margin: 0; padding: 0; background-color: var(--bg-color); color: var(--text-color); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; overflow: hidden; transition: background 0.3s; }
        
        .main-wrapper { display: flex; flex-direction: column; height: 100vh; width: 100%; }
        
        /* Compact Header */
        .chat-header { 
            padding: 2px 8px; 
            border-bottom: 1px solid var(--border-color); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: var(--header-bg); 
            height: 28px;
            font-size: 0.7rem;
        }
        .chat-info-v2 { display: flex; align-items: center; gap: 4px; }
        .chat-badge { background: rgba(0,0,0,0.05); padding: 0px 3px; border-radius: 2px; font-family: monospace; font-weight: bold; }
        .dark-mode .chat-badge { background: rgba(255,255,255,0.1); }
        
        /* Ultra-Tight Message Area */
        .chat-messages { 
            flex-grow: 1; 
            overflow-y: auto; 
            padding: 2px 4px; 
            display: flex; 
            flex-direction: column; 
            width: 100%;
            background: var(--bg-color);
        }
        
        .message-row { 
            width: 100%; 
            display: flex; 
            padding: 1px 0;
            margin: 0;
        }
        .message-row.self { justify-content: flex-end; }
        .message-row.other { justify-content: flex-start; }
        
        .message-content { 
            width: 98%; 
            max-width: 98%; 
            display: flex; 
            gap: 4px;
            align-items: flex-start;
        }
        .message-row.self .message-content { flex-direction: row-reverse; }
        
        .avatar { width: 18px; height: 18px; border-radius: 2px; background: rgba(0,0,0,0.05); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 0.55rem; font-weight: bold; color: #999; margin-top: 1px; }
        .dark-mode .avatar { background: rgba(255,255,255,0.1); color: #aaa; }
        .message-row.self .avatar { background: var(--accent-color); color: white; }
        
        .text-wrapper { flex-grow: 1; min-width: 0; background: var(--msg-bg-other); padding: 1px 4px; border-radius: 3px; border: 1px solid var(--border-color); text-align: left; }
        .message-row.self .text-wrapper { background: var(--msg-bg-self); }
        
        .user-name { font-weight: 700; font-size: 0.6rem; margin-bottom: 0px; display: block; opacity: 0.6; }
        .text { font-size: 0.85rem; line-height: 1.2; word-wrap: break-word; }
        
        /* Minimal Code Block Styling */
        pre { background: rgba(0,0,0,0.02) !important; border-radius: 3px; padding: 4px; margin: 2px 0; border: 1px solid var(--border-color); overflow-x: auto; width: 100%; box-sizing: border-box; white-space: pre-wrap; word-break: break-all; }
        .dark-mode pre { background: rgba(255,255,255,0.02) !important; }
        code { font-family: 'JetBrains Mono', Consolas, monospace; font-size: 0.8rem; line-height: 1.1; }

        /* Smallest Input Area */
        .chat-input-area { padding: 2px 4px 6px 4px; background: var(--header-bg); border-top: 1px solid var(--border-color); }
        .input-container {
            width: 100%;
            position: relative;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 2px 28px 2px 6px;
        }
        #message-input {
            width: 100%;
            border: none;
            outline: none;
            resize: none;
            background: transparent;
            color: var(--text-color);
            font-size: 0.85rem;
            line-height: 1.2;
            max-height: 100px;
            padding: 0;
            display: block;
        }
        .send-btn { 
            position: absolute; 
            right: 3px; 
            bottom: 3px; 
            width: 22px; 
            height: 22px; 
            border-radius: 2px; 
            background: rgba(0,0,0,0.05); 
            border: none; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            color: var(--text-color);
        }
        .dark-mode .send-btn { background: rgba(255,255,255,0.1); }
        .send-btn.active { background: var(--accent-color); color: white; }
        .send-btn svg { width: 12px; height: 12px; stroke: currentColor; fill: none; display: block; }
        
        .theme-toggle { padding: 0 4px; cursor: pointer; opacity: 0.6; display: flex; align-items: center; }
        .theme-toggle:hover { opacity: 1; }
        .theme-toggle svg { width: 12px; height: 12px; }
    </style>
</head>
<body>

    <script>
        // Clean URL immediately
        if (window.location.search || window.location.pathname.endsWith('.php')) {
            window.history.replaceState({}, '', 'chat');
        }
    </script>

    <div class="main-wrapper">
        <header class="chat-header">
            <div class="chat-info-v2">
                <span class="chat-badge"><?php echo htmlspecialchars($code); ?></span>
                <span id="chat-title">...</span>
            </div>
            <div style="display:flex; gap:6px; align-items:center;">
                <div class="theme-toggle" id="theme-toggle" title="Toggle Dark Mode">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </div>
                <span style="opacity:0.6; font-size:0.65rem;"><?php echo htmlspecialchars($user); ?></span>
                <a href="index.php" class="p-button--base" style="padding:0px 4px; font-size:0.65rem; min-height:0; line-height:1;">Exit</a>
            </div>
        </header>

        <main class="chat-messages" id="chat-messages"></main>

        <footer class="chat-input-area">
            <div class="input-container">
                <form id="chat-form" style="margin:0;">
                    <textarea id="message-input" placeholder="Message..." rows="1"></textarea>
                    <button type="submit" id="send-btn" class="send-btn">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            </div>
        </footer>
    </div>

    <script>
        const chatCode = <?php echo json_encode($code); ?>;
        const userName = <?php echo json_encode($user); ?>;
        let lastLine = -1;
        const messagesContainer = document.getElementById('chat-messages');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const chatForm = document.getElementById('chat-form');
        const themeToggle = document.getElementById('theme-toggle');
        const hljsTheme = document.getElementById('hljs-theme');

        // Theme Toggle Logic
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            const lightTheme = "https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css";
            const darkTheme = "https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css";
            hljsTheme.href = isDark ? darkTheme : lightTheme;
        });

        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            sendBtn.classList.toggle('active', this.value.trim().length > 0);
        });

        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        chatForm.addEventListener('submit', (e) => { e.preventDefault(); sendMessage(); });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatMessage(text) {
            if (!text) return '';
            const parts = text.split(/```/g);
            if (parts.length === 1) {
                if (text.includes('\n') || text.length > 50) {
                    return `<pre><code class="hljs">${escapeHtml(text)}</code></pre>`;
                }
                return escapeHtml(text).replace(/\n/g, '<br>');
            }
            let formatted = '';
            for (let i = 0; i < parts.length; i++) {
                if (i % 2 === 1) {
                    let content = parts[i].trim();
                    let lang = 'hljs';
                    const lines = content.split('\n');
                    const firstLine = lines[0].trim();
                    if (firstLine && !firstLine.includes(' ') && firstLine.length < 20) {
                        lang = 'language-' + firstLine;
                        content = lines.slice(1).join('\n').trim();
                    }
                    formatted += `<pre><code class="${lang}">${escapeHtml(content)}</code></pre>`;
                } else {
                    formatted += escapeHtml(parts[i]).replace(/\n/g, '<br>');
                }
            }
            return formatted;
        }

        function appendMessage(msg, isSelf) {
            const row = document.createElement('div');
            row.className = `message-row ${isSelf ? 'self' : 'other'}`;
            const initials = msg.user.substring(0, 2).toUpperCase();
            
            row.innerHTML = `
                <div class="message-content">
                    <div class="avatar">${initials}</div>
                    <div class="text-wrapper">
                        <span class="user-name">${escapeHtml(msg.user)}</span>
                        <div class="text">${formatMessage(msg.message)}</div>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(row);
            row.querySelectorAll('pre code').forEach((block) => {
                try { hljs.highlightElement(block); } catch (e) {}
            });
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        async function fetchMessages() {
            try {
                const response = await fetch(`actions.php?action=get_messages&code=${chatCode}&last_line=${lastLine}`);
                const result = await response.json();
                if (result.status === 'success') {
                    if (lastLine === -1) document.getElementById('chat-title').textContent = result.chat_info.name;
                    if (result.messages.length > 0) {
                        result.messages.forEach(msg => {
                            appendMessage(msg, msg.user === userName);
                        });
                        lastLine = result.last_line;
                    }
                }
            } catch (e) {}
        }

        async function sendMessage() {
            const msg = messageInput.value.trim();
            if (!msg) return;
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('code', chatCode);
            formData.append('user', userName);
            formData.append('message', msg);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendBtn.classList.remove('active');
            const response = await fetch('actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status !== 'success') alert(result.message);
        }

        setInterval(fetchMessages, 1000);
        fetchMessages();
    </script>
</body>
</html>
