# MiniChat - Minimalist PHP Chat

A lightweight, mobile-first, and ultra-compact chat application built with **Pure PHP**. Designed for high performance and minimal footprint.

## ✨ Features

-   **Ultra-Compact UI**: Pixel-perfect, ChatGPT-inspired design utilizing 98% of screen width.
-   **Clean URLs**: Parameter-free URLs (e.g., `/chat`) using PHP Sessions and History API.
-   **Syntax Highlighting**: Real-time code rendering with Highlight.js (GitHub Theme).
-   **Dark Mode**: One-click toggle in the header with dynamic code theme switching.
-   **Code Wrapping**: Automatically wraps long code blocks to prevent horizontal scrollbars.
-   **Zero Dependencies**: Uses core PHP and Vanilla Framework (CDN) for maximum speed.
-   **File-Based Storage**: No database required; chats are stored as secure JSON text files.
-   **Security**: `.htaccess` protection for chat history and admin-only management.

## 🚀 Getting Started

1.  Extract the project folder to your web server (Hostinger, Apache, etc.).
2.  Ensure the `chats/` directory is writable by the server.
3.  Access the landing page via `index.php`.

To run locally:
```bash
php -S localhost:8000
```

## 🛠 Admin & Management

-   Access the **Admin** tab on the home page.
-   Default Passcode: `4444`.
-   Monitor active chats, join as admin, or delete rooms from the dashboard.

## 📦 Deployment Note
For Apache servers (like Hostinger), the included `.htaccess` ensures your chat files and system metadata remain private and secure.
