<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

require_once __DIR__ . '/db_connect.php';

// Get current user ID from database
$current_user_id = null;
try {
    if ($user_role === 'student') {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
    } else {
        $stmt = $pdo->prepare("SELECT id FROM tutors WHERE email = ? LIMIT 1");
    }
    $stmt->execute([$user_email]);
    $user = $stmt->fetch();
    $current_user_id = $user['id'] ?? null;
} catch (Exception $e) {
    $current_user_id = null;
}

// Get user's active conversations
$conversations = [];
try {
    if ($user_role === 'student') {
        // Get conversations where student is involved
        $stmt = $pdo->prepare("SELECT DISTINCT b.id, b.title, b.content, b.created_at, b.tutor_id, t.name as other_person
                              FROM boards b 
                              LEFT JOIN tutors t ON b.tutor_id = t.id
                              WHERE b.user_role = 'conversation' AND b.user_id = ?
                              ORDER BY b.created_at DESC");
        $stmt->execute([$current_user_id]);
    } else { // tutor
        // Get conversations where tutor is involved
        $stmt = $pdo->prepare("SELECT DISTINCT b.id, b.title, b.content, b.created_at, b.user_id as student_id, s.name as other_person
                              FROM boards b 
                              LEFT JOIN students s ON b.user_id = s.id 
                              WHERE b.user_role = 'conversation' AND b.tutor_id = ?
                              ORDER BY b.created_at DESC");
        $stmt->execute([$current_user_id]);
    }
    $conversations = $stmt->fetchAll();
} catch (Exception $e) {
    $conversations = [];
}

// Handle AJAX request for loading messages
if (isset($_GET['load_messages']) && isset($_GET['conversation_id'])) {
    $conversation_id = (int)$_GET['conversation_id'];
    $messages = [];
    
    try {
        // Get all messages for this conversation
        $stmt = $pdo->prepare("SELECT b.content, b.user_id, b.created_at, 
                              CASE 
                                WHEN EXISTS(SELECT 1 FROM students WHERE id = b.user_id) THEN 'student'
                                WHEN EXISTS(SELECT 1 FROM tutors WHERE id = b.user_id) THEN 'tutor'
                                ELSE 'unknown'
                              END as sender_type,
                              CASE 
                                WHEN EXISTS(SELECT 1 FROM students WHERE id = b.user_id) THEN (SELECT name FROM students WHERE id = b.user_id)
                                WHEN EXISTS(SELECT 1 FROM tutors WHERE id = b.user_id) THEN (SELECT name FROM tutors WHERE id = b.user_id)
                                ELSE 'Unknown'
                              END as sender_name
                              FROM boards b 
                              WHERE b.user_role = 'message' AND b.parent_id = ?
                              ORDER BY b.created_at ASC");
        $stmt->execute([$conversation_id]);
        $messages = $stmt->fetchAll();
        
        // Also get the original conversation starter
        $stmt = $pdo->prepare("SELECT content, created_at FROM boards WHERE id = ? AND user_role = 'conversation'");
        $stmt->execute([$conversation_id]);
        $conversation = $stmt->fetch();
        
    } catch (Exception $e) {
        $messages = [];
        $conversation = null;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'messages' => $messages, 
        'conversation' => $conversation,
        'current_user_id' => $current_user_id,
        'current_user_role' => $user_role
    ]);
    exit;
}

// Handle sending new message
if ($_POST && isset($_POST['message']) && isset($_POST['conversation_id']) && $current_user_id) {
    $message = trim($_POST['message']);
    $conversation_id = (int)$_POST['conversation_id'];
    
    if (!empty($message) && $conversation_id > 0) {
        try {
            // Insert new message as separate board entry linked to conversation
            $stmt = $pdo->prepare("INSERT INTO boards (title, content, user_id, user_role, parent_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute(['message', $message, $current_user_id, 'message', $conversation_id]);
            
            // Return JSON response for AJAX
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Message sent']);
                exit;
            }
            $success_message = "Message sent!";
        } catch (Exception $e) {
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Failed to send message']);
                exit;
            }
            $error_message = "Failed to send message.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - ZU Tutors</title>
    <link rel="stylesheet" href="styling.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-logo">ZU Tutors</h2>
                <p class="sidebar-tagline">Connect. Learn. Succeed.</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <?php if ($user_role === 'student'): ?>
                        <li><a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="find_tutors.php" class="nav-link"><i class="fas fa-search"></i> Find Tutors</a></li>
                        <li><a href="my_boards.php" class="nav-link active"><i class="fas fa-comments"></i> My Messages</a></li>
                        <li><a href="request_help.php" class="nav-link"><i class="fas fa-hand-paper"></i> Request Help</a></li>
                    <?php elseif ($user_role === 'tutor'): ?>
                        <li><a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="my_boards.php" class="nav-link active"><i class="fas fa-comments"></i> My Messages</a></li>
                        <li><a href="help_requests.php" class="nav-link"><i class="fas fa-inbox"></i> Help Requests</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="user-role"><?php echo ucfirst($user_role); ?></p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="content-header">
                <h1>My Messages</h1>
                <p>Your conversations with <?php echo $user_role === 'student' ? 'tutors' : 'students'; ?></p>
            </header>
            
            <div class="content-body">
                <?php if (isset($success_message)): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="messages-container">
                    <?php if (empty($conversations)): ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <h3>No conversations yet</h3>
                            <?php if ($user_role === 'student'): ?>
                                <p>Request help from tutors to start messaging!</p>
                                <a href="find_tutors.php" class="empty-state-btn">Find Tutors</a>
                            <?php else: ?>
                                <p>Accept help requests to start conversations with students!</p>
                                <a href="help_requests.php" class="empty-state-btn">View Help Requests</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="conversations-list">
                            <?php foreach ($conversations as $conversation): ?>
                            <div class="conversation-card">
                                <div class="conversation-header">
                                    <div class="conversation-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="conversation-info">
                                        <h4><?php echo htmlspecialchars($conversation['other_person'] ?? 'User'); ?></h4>
                                        <p class="conversation-subject"><?php echo htmlspecialchars($conversation['title']); ?></p>
                                        <span class="conversation-time"><?php echo date('M j, Y g:i A', strtotime($conversation['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="conversation-preview">
                                    <p><?php echo htmlspecialchars(substr($conversation['content'], 0, 100)) . (strlen($conversation['content']) > 100 ? '...' : ''); ?></p>
                                </div>
                                
                                <div class="conversation-actions">
                                    <button class="message-btn" onclick="openConversation(<?php echo $conversation['id']; ?>, '<?php echo htmlspecialchars($conversation['title']); ?>')">
                                        <i class="fas fa-reply"></i> Open Conversation
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversation Modal -->
    <div class="modal-overlay" id="conversationModal" style="display: none;">
        <div class="modal-content conversation-modal">
            <div class="modal-header">
                <h3 id="conversationTitle">Conversation</h3>
                <button class="close-modal" onclick="closeConversation()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="messages-area" id="messagesArea">
                    <!-- Messages will be loaded via JavaScript -->
                </div>
                <div class="message-input-area">
                    <form class="message-form" onsubmit="sendMessage(event)">
                        <input type="hidden" id="conversationId" name="conversation_id">
                        <div class="message-input-group">
                            <textarea id="messageInput" name="message" placeholder="Type your message..." rows="2" required></textarea>
                            <button type="submit" class="send-message-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openConversation(conversationId, title) {
            document.getElementById('conversationTitle').textContent = title;
            document.getElementById('conversationId').value = conversationId;
            document.getElementById('conversationModal').style.display = 'flex';
            loadMessages(conversationId);
        }

        function loadMessages(conversationId) {
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">Loading messages...</p>';
            
            // Load messages via AJAX
            fetch(`?load_messages=1&conversation_id=${conversationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '';
                        
                        // Show conversation starter
                        if (data.conversation) {
                            html += `
                                <div class="message-item conversation-starter">
                                    <div class="message-content" style="background: #f3f4f6; border: 1px solid #d1d5db; color: #374151;">
                                        <p><strong>Original Request:</strong> ${data.conversation.content}</p>
                                        <span class="message-time">${new Date(data.conversation.created_at).toLocaleString()}</span>
                                    </div>
                                </div>
                            `;
                        }
                        
                        // Show all messages
                        data.messages.forEach(message => {
                            const isCurrentUser = message.user_id == data.current_user_id;
                            const messageClass = isCurrentUser ? 'sent' : 'received';
                            html += `
                                <div class="message-item ${messageClass}">
                                    <div class="message-content">
                                        <p>${message.content}</p>
                                        <span class="message-time">${new Date(message.created_at).toLocaleString()}</span>
                                    </div>
                                </div>
                            `;
                        });
                        
                        messagesArea.innerHTML = html || '<p style="text-align: center; color: #666; padding: 20px;">No messages yet. Start the conversation!</p>';
                        messagesArea.scrollTop = messagesArea.scrollHeight;
                    } else {
                        messagesArea.innerHTML = '<p style="text-align: center; color: #ef4444; padding: 20px;">Failed to load messages.</p>';
                    }
                })
                .catch(error => {
                    messagesArea.innerHTML = '<p style="text-align: center; color: #ef4444; padding: 20px;">Error loading messages.</p>';
                });
        }

        function closeConversation() {
            document.getElementById('conversationModal').style.display = 'none';
        }

        function sendMessage(event) {
            event.preventDefault();
            const messageText = document.getElementById('messageInput').value.trim();
            const conversationId = document.getElementById('conversationId').value;
            
            if (messageText && conversationId) {
                // Add message to UI immediately
                const messagesArea = document.getElementById('messagesArea');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message-item sent';
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <p>${messageText}</p>
                        <span class="message-time">Just now</span>
                    </div>
                `;
                messagesArea.appendChild(messageDiv);
                messagesArea.scrollTop = messagesArea.scrollHeight;
                
                // Clear input
                document.getElementById('messageInput').value = '';
                
                // Submit to server via AJAX
                const formData = new FormData();
                formData.append('message', messageText);
                formData.append('conversation_id', conversationId);
                formData.append('ajax', '1');
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Failed to send message: ' + (data.error || 'Unknown error'));
                        // Remove the message from UI if it failed
                        messageDiv.remove();
                    }
                })
                .catch(() => {
                    alert('Failed to send message. Please try again.');
                    messageDiv.remove();
                });
            }
        }

        // Close modal when clicking outside
        document.getElementById('conversationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConversation();
            }
        });
    </script>

    <style>
        .messages-container {
            max-width: 800px;
        }

        .conversations-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .conversation-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .conversation-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .conversation-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .conversation-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .conversation-info h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }

        .conversation-subject {
            margin: 4px 0;
            font-size: 14px;
            color: #6b7280;
        }

        .conversation-time {
            font-size: 12px;
            color: #9ca3af;
        }

        .conversation-preview p {
            margin: 8px 0 0;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.5;
        }

        .conversation-actions {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }

        .message-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s;
        }

        .message-btn:hover {
            background: #2563eb;
        }

        .conversation-modal {
            width: 600px;
            max-width: 90vw;
            height: 500px;
            display: flex;
            flex-direction: column;
        }

        .messages-area {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            background: #f9fafb;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .message-item {
            display: flex;
            max-width: 70%;
        }

        .message-item.sent {
            align-self: flex-end;
        }

        .message-item.received {
            align-self: flex-start;
        }

        .message-content {
            background: white;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .message-item.sent .message-content {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .message-content p {
            margin: 0 0 4px 0;
            line-height: 1.4;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
        }

        .message-input-area {
            padding: 16px;
            border-top: 1px solid #e5e7eb;
            background: white;
        }

        .message-input-group {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .message-input-group textarea {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            resize: vertical;
            min-height: 40px;
            max-height: 100px;
            font-family: inherit;
        }

        .send-message-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 44px;
        }

        .send-message-btn:hover {
            background: #2563eb;
        }

        .empty-state-btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 16px;
            transition: background-color 0.2s;
        }

        .empty-state-btn:hover {
            background: #2563eb;
        }
    </style>
</body>
</html>