<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IM DM - Home</title>
    <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-4.15.0.min.css" />
    <style>
        body {
            background-color: #f7f7f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .p-card {
            max-width: 450px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            padding: 2rem;
            overflow: hidden;
        }

        .p-tabs__list {
            overflow: hidden !important;
            border-bottom: 1px solid #eee;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            font-size: 2.5rem;
            color: #111;
            letter-spacing: -1px;
        }

        .p-tabs__link {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .tab-content {
            padding-top: 1.5rem;
        }

        input {
            border-radius: 6px !important;
        }

        button {
            border-radius: 6px !important;
            font-weight: 600 !important;
        }
    </style>
</head>

<body>

    <div class="p-card">
        <div class="logo">💬 InstantMessage DirectMessage</div>

        <nav class="p-tabs">
            <ul class="p-tabs__list">
                <li class="p-tabs__item"><a class="p-tabs__link" href="#create" id="tab-create" aria-selected="true">New
                        Chat</a></li>
                <li class="p-tabs__item"><a class="p-tabs__link" href="#join" id="tab-join">Join via Code</a></li>
                <li class="p-tabs__item"><a class="p-tabs__link" href="#read" id="tab-read">Admin</a></li>
            </ul>
        </nav>

        <!-- Create New Chat -->
        <div id="create-section" class="tab-content">
            <form id="create-form">
                <p class="p-text--small">Start a fresh conversation and get a unique 4-digit code.</p>
                <label for="create-name">Your Name</label>
                <input type="text" id="create-name" name="name" placeholder="E.g. Deepak" required>
                <button type="submit" class="p-button--positive u-full-width u-no-margin--bottom">Join New
                    Meeting</button>
            </form>
        </div>

        <!-- Join Existing Chat -->
        <div id="join-section" class="tab-content" style="display:none;">
            <form id="join-form">
                <p class="p-text--small">Enter the 4-digit code and your name to enter a chat.</p>
                <label for="join-code">Chat Code (4-digit)</label>
                <input type="text" id="join-code" name="code" placeholder="E.g. ABCD" maxlength="4" required
                    style="text-transform: uppercase;">
                <label for="join-name">Your Name</label>
                <input type="text" id="join-name" name="name" placeholder="E.g. Deepak" required>
                <button type="submit" class="p-button--brand u-full-width u-no-margin--bottom">Enter Chat</button>
            </form>
        </div>

        <!-- Admin Access -->
        <div id="read-section" class="tab-content" style="display:none;">
            <form id="read-form">
                <p class="p-text--small">View and manage all active chat rooms.</p>
                <label for="passcode">Admin Passcode</label>
                <input type="password" id="passcode" name="passcode" placeholder="Enter Passcode" required>
                <button type="submit" class="p-button u-full-width u-no-margin--bottom">Open Chat List</button>
            </form>
        </div>
    </div>

    <script>
        const tabs = {
            create: { tab: document.getElementById('tab-create'), section: document.getElementById('create-section') },
            join: { tab: document.getElementById('tab-join'), section: document.getElementById('join-section') },
            read: { tab: document.getElementById('tab-read'), section: document.getElementById('read-section') }
        };

        function switchTab(activeKey) {
            Object.keys(tabs).forEach(key => {
                const isActive = key === activeKey;
                tabs[key].section.style.display = isActive ? 'block' : 'none';
                tabs[key].tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }

        tabs.create.tab.addEventListener('click', (e) => { e.preventDefault(); switchTab('create'); });
        tabs.join.tab.addEventListener('click', (e) => { e.preventDefault(); switchTab('join'); });
        tabs.read.tab.addEventListener('click', (e) => { e.preventDefault(); switchTab('read'); });

        async function enterChat(code, name) {
            const formData = new FormData();
            formData.append('action', 'set_session');
            formData.append('code', code);
            formData.append('user', name); // FIXED: name -> user
            const response = await fetch('actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = 'chat.php';
            }
        }

        // Create Chat
        document.getElementById('create-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const name = formData.get('name');
            formData.append('action', 'create_chat');
            const response = await fetch('actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                await enterChat(result.code, name);
            }
        });

        // Join Existing Chat
        document.getElementById('join-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const code = document.getElementById('join-code').value.toUpperCase();
            const name = document.getElementById('join-name').value;
            await enterChat(code, name);
        });

        // Admin Access
        document.getElementById('read-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const passcode = document.getElementById('passcode').value;
            const formData = new FormData();
            formData.append('action', 'set_admin_session');
            formData.append('password', passcode);
            const response = await fetch('actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = 'chat_list.php';
            } else {
                alert(result.message);
            }
        });
    </script>
</body>

</html>