// Auto-scroll to bottom
const messagesContainer = document.getElementById('messagesContainer');
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Auto-resize textarea
const chatInput = document.querySelector('.chat-input');
if (chatInput) {
    chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Submit on Enter (but Shift+Enter for new line)
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('chatForm').dispatchEvent(new Event('submit'));
        }
    });
}

// ===== FILE ATTACHMENT =====
const attachBtn = document.querySelector('.input-btn[title="Attach file"]');
if (attachBtn) {
    // Create hidden file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.style.display = 'none';
    fileInput.accept = 'image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt';
    document.body.appendChild(fileInput);
    
    attachBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Show file preview
            const fileName = file.name;
            const fileSize = (file.size / 1024).toFixed(2) + ' KB';
            
            // Create preview element
            const preview = document.createElement('div');
            preview.style.cssText = 'padding: 8px 12px; background: #e7f3ff; border-radius: 8px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;';
            preview.innerHTML = `
                <i class="fas fa-file" style="color: #0084ff;"></i>
                <span style="flex: 1; font-size: 13px;">${fileName} (${fileSize})</span>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #65676b; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Insert before input wrapper
            const inputArea = document.querySelector('.chat-input-area');
            const inputWrapper = document.querySelector('.chat-input-wrapper');
            inputArea.insertBefore(preview, inputWrapper);
            
            console.log('File selected:', fileName);
        }
    });
}

// ===== VOICE RECORDING =====
let mediaRecorder;
let audioChunks = [];
let recordingInterval;
let recordingSeconds = 0;

const voiceBtn = document.querySelector('.input-btn[title="Voice message"]');
if (voiceBtn) {
    voiceBtn.addEventListener('click', async function() {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
            // Start recording
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];
                recordingSeconds = 0;
                
                mediaRecorder.ondataavailable = (event) => {
                    audioChunks.push(event.data);
                };
                
                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    
                    // Show audio preview
                    const preview = document.createElement('div');
                    preview.style.cssText = 'padding: 12px; background: #e7f3ff; border-radius: 8px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;';
                    preview.innerHTML = `
                        <button type="button" onclick="this.nextElementSibling.play()" style="width: 32px; height: 32px; border-radius: 50%; background: #0084ff; border: none; color: white; cursor: pointer;">
                            <i class="fas fa-play"></i>
                        </button>
                        <audio src="${audioUrl}" style="display: none;"></audio>
                        <div style="flex: 1;">
                            <div style="font-size: 13px; font-weight: 600;">Voice message</div>
                            <div style="font-size: 12px; color: #65676b;">${recordingSeconds}s</div>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #65676b; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    
                    const inputArea = document.querySelector('.chat-input-area');
                    const inputWrapper = document.querySelector('.chat-input-wrapper');
                    inputArea.insertBefore(preview, inputWrapper);
                    
                    // Stop all tracks
                    stream.getTracks().forEach(track => track.stop());
                };
                
                mediaRecorder.start();
                
                // Update button to show recording
                this.innerHTML = '<i class="fas fa-stop"></i>';
                this.style.background = '#ef4444';
                this.style.color = 'white';
                
                // Show recording indicator
                const indicator = document.createElement('div');
                indicator.id = 'recordingIndicator';
                indicator.style.cssText = 'padding: 8px 12px; background: #fee2e2; border-radius: 8px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: #dc2626;';
                indicator.innerHTML = `
                    <i class="fas fa-circle" style="font-size: 8px; animation: pulse 1s infinite;"></i>
                    <span style="font-size: 13px; font-weight: 600;">Recording... <span id="recordingTime">0:00</span></span>
                `;
                
                const inputArea = document.querySelector('.chat-input-area');
                const inputWrapper = document.querySelector('.chat-input-wrapper');
                inputArea.insertBefore(indicator, inputWrapper);
                
                // Update timer
                recordingInterval = setInterval(() => {
                    recordingSeconds++;
                    const minutes = Math.floor(recordingSeconds / 60);
                    const seconds = recordingSeconds % 60;
                    document.getElementById('recordingTime').textContent = 
                        `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }, 1000);
                
            } catch (error) {
                console.error('Error accessing microphone:', error);
                alert('Could not access microphone. Please check permissions.');
            }
        } else {
            // Stop recording
            mediaRecorder.stop();
            clearInterval(recordingInterval);
            
            // Reset button
            this.innerHTML = '<i class="fas fa-microphone"></i>';
            this.style.background = '';
            this.style.color = '';
            
            // Remove indicator
            const indicator = document.getElementById('recordingIndicator');
            if (indicator) indicator.remove();
        }
    });
}

// ===== EMOJI PICKER =====
const emojiBtn = document.querySelector('.input-btn[title="Emoji"]');
if (emojiBtn) {
    const emojis = ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇', '☝️', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄', '💋', '🩸', '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️', '🈴', '🈵', '🈹', '🈲', '🅰️', '🅱️', '🆎', '🆑', '🅾️', '🆘', '❌', '⭕', '🛑', '⛔', '📛', '🚫', '💯', '💢', '♨️', '🚷', '🚯', '🚳', '🚱', '🔞', '📵', '🚭'];
    
    let emojiPicker = null;
    
    emojiBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (emojiPicker) {
            emojiPicker.remove();
            emojiPicker = null;
            return;
        }
        
        // Create emoji picker
        emojiPicker = document.createElement('div');
        emojiPicker.style.cssText = 'position: absolute; bottom: 60px; right: 24px; background: white; border: 1px solid #e4e6eb; border-radius: 12px; padding: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 320px; max-height: 300px; overflow-y: auto; display: grid; grid-template-columns: repeat(8, 1fr); gap: 4px; z-index: 1000;';
        
        emojis.forEach(emoji => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = emoji;
            btn.style.cssText = 'width: 32px; height: 32px; border: none; background: transparent; font-size: 20px; cursor: pointer; border-radius: 6px; transition: background 0.2s;';
            btn.onmouseover = () => btn.style.background = '#f0f2f5';
            btn.onmouseout = () => btn.style.background = 'transparent';
            btn.onclick = () => {
                const input = document.querySelector('.chat-input');
                input.value += emoji;
                input.focus();
                emojiPicker.remove();
                emojiPicker = null;
            };
            emojiPicker.appendChild(btn);
        });
        
        document.querySelector('.chat-input-area').appendChild(emojiPicker);
    });
    
    // Close emoji picker when clicking outside
    document.addEventListener('click', function(e) {
        if (emojiPicker && !emojiPicker.contains(e.target) && e.target !== emojiBtn) {
            emojiPicker.remove();
            emojiPicker = null;
        }
    });
}

// ===== FORM SUBMISSION =====
const chatForm = document.getElementById('chatForm');
if (chatForm) {
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const content = formData.get('message[content]');
        
        if (!content || content.trim() === '') {
            return;
        }
        
        // Disable send button
        const sendBtn = document.getElementById('sendBtn');
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                chatInput.value = '';
                chatInput.style.height = 'auto';
                
                // Reload to show new message
                window.location.reload();
            } else {
                alert(data.error || 'Error sending message');
                if (sendBtn) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending message');
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        });
    });
}

// Add pulse animation for recording indicator
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
`;
document.head.appendChild(style);
