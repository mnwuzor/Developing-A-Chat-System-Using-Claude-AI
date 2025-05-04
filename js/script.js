$(document).ready(function() {
    // Variables
    let selectedUserId = $('#receiver-id').val();
    let lastTimestamp = null;
    let chatMessagesDiv = $('#chat-messages');
    
    // Check if a user is selected and load messages
    if (selectedUserId) {
        loadMessages();
        
        // Set interval to refresh messages every 5 seconds
        setInterval(loadMessages, 5000);
    }
    
    // Search users functionality
    $('#user-search').on('keyup', function() {
        let searchTerm = $(this).val().toLowerCase();
        
        $('.user-item').each(function() {
            let username = $(this).find('h3').text().toLowerCase();
            let displayName = $(this).find('p').text().toLowerCase();
            
            if (username.includes(searchTerm) || displayName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Send message form submission
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        
        let message = $('#message').val();
        if (!message.trim()) return;
        
        $.ajax({
            url: 'send_message.php',
            type: 'POST',
            data: {
                receiver_id: selectedUserId,
                message: message
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Add the new message to the chat
                    appendMessage(response.message);
                    
                    // Clear the input field
                    $('#message').val('');
                    
                    // Scroll to bottom
                    scrollToBottom();
                } else {
                    alert(response.error || 'Failed to send message');
                }
            },
            error: function() {
                alert('An error occurred while sending the message');
            }
        });
    });
    
    // Function to load messages
    function loadMessages() {
        if (!selectedUserId) return;
        
        $.ajax({
            url: 'get_messages.php',
            type: 'POST',
            data: {
                receiver_id: selectedUserId,
                last_timestamp: lastTimestamp
            },
            dataType: 'json',
            success: function(response) {
                if (response.messages && response.messages.length > 0) {
                    // If it's the first load, clear the container
                    if (!lastTimestamp) {
                        chatMessagesDiv.empty();
                    }
                    
                    // Append each message
                    response.messages.forEach(function(message) {
                        appendMessage(message);
                        
                        // Update last timestamp
                        lastTimestamp = message.sent_at;
                    });
                    
                    // If it's the first load, scroll to bottom
                    if (!lastTimestamp) {
                        scrollToBottom();
                    }
                } else if (!lastTimestamp) {
                    // If no messages and first load, show empty message
                    chatMessagesDiv.html('<div class="no-messages">No messages yet. Start the conversation!</div>');
                }
            },
            error: function() {
                console.error('Failed to load messages');
            }
        });
    }
    
    // Function to append a message to the chat
    function appendMessage(message) {
        let currentUserId = $('meta[name="user-id"]').attr('content');
        let isSender = message.sender == currentUserId;
        let messageClass = isSender ? 'sent' : 'received';
        let timeFormatted = formatTime(message.sent_at);
        
        let messageHTML = `
            <div class="message ${messageClass}">
                <div class="message-content">
                    ${message.message}
                    <span class="message-time">${timeFormatted}</span>
                </div>
            </div>
        `;
        
        chatMessagesDiv.append(messageHTML);
    }
    
    // Function to format time
    function formatTime(timestamp) {
        let date = new Date(timestamp);
        let hours = date.getHours();
        let minutes = date.getMinutes();
        let ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        
        return hours + ':' + minutes + ' ' + ampm;
    }
    
    // Function to scroll to the bottom of the chat
    function scrollToBottom() {
        chatMessagesDiv.scrollTop(chatMessagesDiv[0].scrollHeight);
    }
    
    // Add meta tag with user ID
    $('head').append('<meta name="user-id" content="' + $('body').data('user-id') + '">');
    
    // Responsive design - toggle user list on mobile
    $('.toggle-users').on('click', function() {
        $('.users-list').toggleClass('active');
    });
    
    // Close user list when a user is clicked on mobile
    $('.user-item a').on('click', function() {
        if (window.innerWidth < 768) {
            $('.users-list').removeClass('active');
        }
    });
    
    // Adjust textarea height based on content
    $('#message').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});