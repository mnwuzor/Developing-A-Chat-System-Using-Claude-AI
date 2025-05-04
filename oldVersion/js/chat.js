$(document).ready(function() {
    let selectedUserId = null;
    let refreshInterval = null;
    
    // Toggle sidebar on mobile
    $('#mobile-menu-toggle').click(function() {
        $('.chat-sidebar').toggleClass('show');
    });
    
    // Close sidebar when clicking outside on mobile
    $('.chat-main').click(function() {
        if ($('.chat-sidebar').hasClass('show')) {
            $('.chat-sidebar').removeClass('show');
        }
    });
    
    // User search functionality
    $('#user-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.user-item').each(function() {
            const username = $(this).find('p').text().toLowerCase();
            const fullname = $(this).find('h4').text().toLowerCase();
            
            if (username.includes(searchTerm) || fullname.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Load chat when clicking on a user
    $(document).on('click', '.user-item', function() {
        selectedUserId = $(this).data('user-id');
        $('.user-item').removeClass('active');
        $(this).addClass('active');
        
        // Hide unread badge
        $(this).find('.unread-badge').remove();
        
        // Enable chat form
        $('#chat-form').removeClass('hidden');
        $('#message-input').prop('disabled', false);
        $('#send-btn').prop('disabled', false);
        
        // Load messages
        loadMessages(selectedUserId);
        
        // Close sidebar on mobile
        if (window.innerWidth < 768) {
            $('.chat-sidebar').removeClass('show');
        }
        
        // Clear previous interval and set a new one
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        refreshInterval = setInterval(function() {
            loadMessages(selectedUserId, true);
        }, 5000); // Refresh every 5 seconds
    });
    
    // Refresh button functionality
    $('#refresh-btn').click(function() {
        if (selectedUserId) {
            loadMessages(selectedUserId);
        }
    });
    
    // Send message form submission
    $('#chat-form').submit(function(e) {
        e.preventDefault();
        
        const message = $('#message-input').val().trim();
        if (!message || !selectedUserId) return;
        
        // Send message via AJAX
        $.ajax({
            url: 'ajax_chat.php',
            type: 'POST',
            data: {
                action: 'send_message',
                receiver_id: selectedUserId,
                message: message
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear input field
                    $('#message-input').val('');
                    
                    // Add message to chat
                    appendMessage(response.message);
                    
                    // Scroll to bottom
                    scrollToBottom();
                } else {
                    alert('Failed to send message: ' + response.error);
                }
            },
            error: function() {
                alert('An error occurred while sending the message.');
            }
        });
    });
    
    // Load messages function
    function loadMessages(userId, silent = false) {
        if (!userId) return;
        
        $.ajax({
            url: 'ajax_chat.php',
            type: 'POST',
            data: {
                action: 'get_messages',
                receiver_id: userId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update chat header with user info
                    updateChatHeader(response.receiver);
                    
                    // Clear existing messages
                    $('#chat-messages').empty();
                    
                    // Display messages
                    if (response.messages.length === 0) {
                        $('#chat-messages').html('<div class="no-messages"><p>No messages yet. Start a conversation!</p></div>');
                    } else {
                        response.messages.forEach(function(message) {
                            appendMessage(message);
                        });
                    }
                    
                    // Scroll to bottom
                    scrollToBottom();
                } else if (!silent) {
                    alert('Failed to load messages: ' + response.error);
                }
            },
            error: function() {
                if (!silent) {
                    alert('An error occurred while loading messages.');
                }
            }
        });
    }
    
    // Update chat header with selected user info
    function updateChatHeader(user) {
        const statusClass = user.status || 'offline';
        
        $('#selected-user-info').html(`
            <div class="user-avatar">
                <img src="images/default.jpg" alt="${user.fullname}">
                <span class="status-dot ${statusClass}"></span>
            </div>
            <div class="user-details">
                <h4>${user.fullname}</h4>
                <p>@${user.username}</p>
            </div>
        `);
    }
    
    // Append a message to the chat
    function appendMessage(message) {
        const currentUserId = $('body').data('user-id');
        const isOwnMessage = message.sender == currentUserId;
        const messageClass = isOwnMessage ? 'message-out' : 'message-in';
        const timeFormatted = formatTime(message.sent_at);
        
        const messageHTML = `
            <div class="message ${messageClass}">
                <div class="message-content">
                    <div class="message-text">${message.message}</div>
                    <div class="message-time">
                        ${timeFormatted}
                        ${isOwnMessage ? (message.is_read == 1 ? '<i class="fas fa-check-double read"></i>' : '<i class="fas fa-check"></i>') : ''}
                    </div>
                </div>
            </div>
        `;
        
        $('#chat-messages').append(messageHTML);
    }
    
    // Format timestamp
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        
        return `${hours}:${minutes} ${ampm}`;
    }
    
    // Scroll chat to bottom
    function scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Set current user ID in body data attribute
    const currentUserId = document.body.getAttribute('data-user-id');
    
    // Refresh user list periodically
    setInterval(function() {
        $.ajax({
            url: 'ajax_chat.php',
            type: 'POST',
            data: {
                action: 'get_users'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateUserList(response.users, response.unread_counts);
                }
            }
        });
    }, 10000); // Every 10 seconds
    
    // Update user list function
    function updateUserList(users, unreadCounts) {
        $('.user-list').empty();
        
        users.forEach(function(user) {
            const unreadBadge = unreadCounts[user.user_id] ? 
                `<div class="unread-badge">${unreadCounts[user.user_id]}</div>` : '';
                
            const isActive = selectedUserId == user.user_id ? 'active' : '';
            
            const userHTML = `
                <div class="user-item ${isActive}" data-user-id="${user.user_id}">
                    <div class="user-avatar">
                        <img src="images/${user.profile_pic}" alt="${user.fullname}">
                        <span class="status-dot ${user.status}"></span>
                    </div>
                    <div class="user-info">
                        <h4>${user.fullname}</h4>
                        <p>@${user.username}</p>
                    </div>
                    ${unreadBadge}
                </div>
            `;
            
            $('.user-list').append(userHTML);
        });
    }