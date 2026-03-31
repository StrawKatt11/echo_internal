document.addEventListener('DOMContentLoaded', () => {
  setupHamburgerMenu();
  setupCustomCursor();
  setupContactForm();
  setupStoryImageModal();
  setupForumImageModal();
  setupMessaging();
  setupFriends();
  setupVerificationCodeInput();
  setupDownloadPage();
  setupFileInputs();
  setupAvatarSelection();
});

function setupHamburgerMenu() {
  const hamburgerBtn = document.querySelector('.hamburger-btn');
  const menu = document.getElementById('echoMainMenu');
  if (!hamburgerBtn || !menu) return;

  const offcanvasInstance = (typeof bootstrap !== 'undefined' && bootstrap.Offcanvas)
    ? bootstrap.Offcanvas.getOrCreateInstance(menu)
    : null;

  const isMenuOpen = () => menu.classList.contains('show') || menu.classList.contains('active');

  const openMenu = () => {
    hamburgerBtn.classList.add('active');
    hamburgerBtn.setAttribute('aria-expanded', 'true');
    if (offcanvasInstance) offcanvasInstance.show();
    else menu.classList.add('show');
  };

  const closeMenu = () => {
    hamburgerBtn.classList.remove('active');
    hamburgerBtn.setAttribute('aria-expanded', 'false');
    if (offcanvasInstance) offcanvasInstance.hide();
    else {
      menu.classList.remove('show', 'active');
      menu.setAttribute('aria-hidden', 'true');
    }
  };

  hamburgerBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    isMenuOpen() ? closeMenu() : openMenu();
  });

  document.addEventListener('click', (e) => {
    if (!isMenuOpen()) return;
    if (e.target === hamburgerBtn || e.target.closest('.hamburger-btn')) return;
    if (menu && e.target.closest && e.target.closest('#echoMainMenu, .offcanvas, .menu')) return;
    closeMenu();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isMenuOpen()) closeMenu();
    if (e.key === 'Tab') {
      const activeEl = document.activeElement;
      const isInput = activeEl && (
        ['INPUT','TEXTAREA','SELECT','BUTTON'].includes(activeEl.tagName) ||
        activeEl.isContentEditable || activeEl.hasAttribute('contenteditable')
      );
      if (!isInput) {
        e.preventDefault();
        isMenuOpen() ? closeMenu() : openMenu();
      }
    }
  });

  if (offcanvasInstance) {
    menu.addEventListener('shown.bs.offcanvas', () => {
      hamburgerBtn.classList.add('active');
      hamburgerBtn.setAttribute('aria-expanded', 'true');
    });
    menu.addEventListener('hidden.bs.offcanvas', () => {
      hamburgerBtn.classList.remove('active');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
    });
  }

  const mo = new MutationObserver(() => {
    if (!isMenuOpen()) {
      hamburgerBtn.classList.remove('active');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
    } else {
      hamburgerBtn.classList.add('active');
      hamburgerBtn.setAttribute('aria-expanded', 'true');
    }
  });
  mo.observe(menu, { attributes: true, attributeFilter: ['class', 'aria-hidden', 'style'] });
}

function setupCustomCursor() {
  const cursor = document.getElementById('echoCursor');
  if (!cursor) return;

  const colors = ['#00ffff', '#ff00ff', '#ff0080', '#00ff80', '#ffff00', '#ff8000', '#8000ff'];
  const savedColor = localStorage.getItem('echoCursorColor') || '#00ffff';

  const applyColor = (color) => {
    document.documentElement.style.setProperty('--cursor-color', color);
    document.documentElement.style.setProperty('--cursor-color-alpha', color + '80');
    localStorage.setItem('echoCursorColor', color);
  };
  applyColor(savedColor);

  let lastX = window.innerWidth / 2;
  let lastY = window.innerHeight / 2;

  const updateCursorPosition = (x, y) => {
    cursor.style.left = x + 'px';
    cursor.style.top = y + 'px';
    lastX = x;
    lastY = y;
  };

  const moveCursor = (e) => {
    updateCursorPosition(e.clientX, e.clientY);
    cursor.classList.add('active');
  };

  const restoreCursor = () => {
    document.body.style.cursor = 'none';
    cursor.style.display = 'block';
  };

  const originalAlert = window.alert;
  const originalConfirm = window.confirm;

  window.alert = function(message) {
    cursor.style.display = 'none';
    document.body.style.cursor = 'default';
    const result = originalAlert(message);
    cursor.style.display = 'block';
    document.body.style.cursor = 'none';
    updateCursorPosition(lastX, lastY);
    return result;
  };
  
  window.confirm = function(message) {
    cursor.style.display = 'none';
    document.body.style.cursor = 'default';
    const result = originalConfirm(message);
    cursor.style.display = 'block';
    document.body.style.cursor = 'none';
    updateCursorPosition(lastX, lastY);
    return result;
  };

  const handleFocus = () => {
    document.body.style.cursor = 'none';
    cursor.style.display = 'block';
    updateCursorPosition(lastX, lastY);
  };
  
  const handleBlur = () => {
    document.body.style.cursor = 'default';
    cursor.style.display = 'none';
  };

  window.addEventListener('focus', handleFocus);
  window.addEventListener('blur', handleBlur);
  
  document.onmousemove = moveCursor;
  
  document.addEventListener('mouseleave', () => cursor.classList.remove('active'));
  document.addEventListener('mouseenter', () => cursor.classList.add('active'));

  document.body.style.cursor = 'none';
  updateCursorPosition(lastX, lastY);
  cursor.style.display = 'block';

  document.addEventListener('click', () => {
    const newColor = colors[Math.floor(Math.random() * colors.length)];
    applyColor(newColor);
  });
}

function setupContactForm() {
  const form = document.getElementById('contactForm');
  const statusDiv = document.getElementById('status');
  if (!form) return;

  form.addEventListener('submit', function(e){
    e.preventDefault();
    const templateParams = {
      name: form.querySelector('#name')?.value || '',
      email: form.querySelector('#email')?.value || '',
      message: form.querySelector('#message')?.value || ''
    };

    if (typeof emailjs === 'undefined' || !emailjs.send) {
      console.error('emailjs not available');
      if (statusDiv) statusDiv.innerHTML = '<span class="text-danger fw-bold">Email service unavailable.</span>';
      return;
    }

    emailjs.send('service_uwt99kd', 'template_xdp4ecx', templateParams)
      .then(() => {
        if (statusDiv) statusDiv.innerHTML = '<span class="text-success fw-bold">Message sent successfully!</span>';
        form.reset();
      }, (err) => {
        console.error(err);
        if (statusDiv) statusDiv.innerHTML = '<span class="text-danger fw-bold">Failed to send. Try again.</span>';
      });
  });
}

function setupStoryImageModal() {
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('modalImage');
  const closeBtn = document.getElementById('modalClose');
  const storyImages = document.querySelectorAll('.story-img');
  if (!modal || !modalImg || storyImages.length === 0) return;

  storyImages.forEach(img => {
    img.style.cursor = 'pointer';
    img.addEventListener('click', () => {
      modalImg.src = img.src;
      modal.classList.add('show');
    });
  });

  const hideModal = () => modal.classList.remove('show');
  if (closeBtn) closeBtn.addEventListener('click', hideModal);
  modal.addEventListener('click', e => {
    if (e.target === modal || e.target.classList.contains('modal-backdrop')) hideModal();
  });
}

function setupForumImageModal() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const closeBtn = document.getElementById('modalClose');
    if (!modal || !modalImg) return;
  
    document.querySelectorAll('.post-image-link img').forEach(img => {
      img.style.cursor = 'pointer';
      img.addEventListener('click', e => {
        e.preventDefault();
        modalImg.src = img.src.split('?')[0];
        modal.classList.add('show');
      });
    });
  
    const hide = () => modal.classList.remove('show');
    if (closeBtn) closeBtn.onclick = hide;
    modal.onclick = e => {
      if (e.target === modal || e.target.classList.contains('modal-backdrop')) hide();
    };
  }

function setupMessaging() {
  const messageIcon = document.getElementById('messageIcon');
  const messageBox = document.getElementById('messageBox');
  const profileContent = document.getElementById('profileContent');
  const messageText = document.getElementById('messageText');
  const currentUser = document.body.getAttribute('data-current-user');
  const otherUser = document.body.getAttribute('data-other-user');
  const otherUsername = document.body.getAttribute('data-other-username');
  
  if (!messageIcon || !messageBox || !profileContent || !messageText || !currentUser || !otherUser || !otherUsername) return;

  messageIcon.onclick = () => {
    const isOpen = messageBox.style.display === 'block';
    messageBox.style.display = isOpen ? 'none' : 'block';
    profileContent.style.display = isOpen ? 'block' : 'none';
    if (!isOpen) loadMessages();
  };

  messageText.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  const sendButton = document.getElementById('sendMessageBtn');
  if (sendButton) {
    sendButton.onclick = sendMessage;
  }

  function sendMessage() {
    const msg = messageText.value.trim();
    if (!msg) return;
    
    fetch('message.php?action=send', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({message: msg, user: otherUser})
    }).then(r => r.json()).then(d => {
      if (d.success) {
        messageText.value = '';
        loadMessages();
      }
    });
  }

  function deleteMessage(msgId) {
    if (!confirm('Delete this message?')) return;
    fetch('message.php?action=delete', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'id=' + msgId
    }).then(r => r.json()).then(res => {
      if (res.success) loadMessages();
    });
  }

  function loadMessages() {
    fetch(`message.php?action=load&user=${otherUser}&t=${Date.now()}`)
    .then(r => r.json())
    .then(data => {
      const chat = document.querySelector('.chat-messages');
      if (!chat) return;
      
      chat.innerHTML = data.map(m => {
        const isCurrentUser = m.sender_id == currentUser;
        return `
          <div class="chat-msg ${isCurrentUser ? 'sent' : 'received'}" style="position:relative;padding-right:45px;margin-bottom:12px;">
            <span class="sender" style="color:#00ffff;font-weight:bold;display:block;margin-bottom:4px;">
              ${isCurrentUser ? 'You' : otherUsername}
            </span>
            <div class="message-text" style="background:rgba(0,255,255,0.05);padding:10px 12px;border-radius:12px;display:inline-block;max-width:90%;word-wrap:break-word;">
              ${m.message.replace(/\n/g,'<br>')}
            </div>
            ${isCurrentUser ? `
            <button onclick="window.deleteMessage(${m.id})" 
                    title="Delete message"
                    style="position:absolute;top:8px;right:8px;background:rgba(255,0,80,0.25);border:2px solid #ff0080;color:#ff0080;width:36px;height:36px;border-radius:50%;font-size:20px;font-weight:bold;cursor:pointer;transition:all 0.3s;box-shadow:0 0 20px #ff0080;backdrop-filter:blur(4px);"
                    onmouseover="this.style.background='#ff0080';this.style.color='#000';this.style.transform='scale(1.1)';"
                    onmouseout="this.style.background='rgba(255,0,80,0.25)';this.style.color='#ff0080';this.style.transform='scale(1)';">
                ×
            </button>` : ''}
          </div>`;
      }).join('');
      
      chat.scrollTop = chat.scrollHeight;
    });
  }

  window.deleteMessage = deleteMessage;

  setInterval(() => {
    if (messageBox.style.display === 'block') loadMessages();
  }, 3000);
}

function setupFriends() {
  document.getElementById('avatar')?.addEventListener('change', e => {
    document.getElementById('file-name').textContent = e.target.files[0]?.name || 'No file chosen';
  });

  loadFriends();
  loadFriendRequests();

  const addFriendForm = document.getElementById('add-friend-form');
  if (addFriendForm) {
    addFriendForm.onsubmit = handleAddFriend;
  }
}

function loadFriends() {
  const friendList = document.getElementById('friend-list');
  if (!friendList) return;

  fetch('friends_ajax.php?action=list')
    .then(r => r.json())
    .then(data => {
      friendList.innerHTML = data.length 
        ? data.map(friend => `
          <li class="list-group-item d-flex justify-content-between">
            <a href="user_profile.php?id=${friend.id}" class="text-cyan friend-link">${friend.username}</a>
            <button class="btn btn-sm btn-danger" onclick="removeFriend(${friend.friend_row_id})">Remove</button>
          </li>`
        ).join('')
        : '<li class="list-group-item text-center text-secondary">No friends yet</li>';
    });
}

function loadFriendRequests() {
  const requestsList = document.getElementById('friend-requests');
  if (!requestsList) return;

  fetch('friends_ajax.php?action=requests')
    .then(r => r.json())
    .then(data => {
      requestsList.innerHTML = data.length 
        ? data.map(request => `
          <li class="list-group-item d-flex justify-content-between">
            <span>${request.username}</span>
            <div>
              <button class="btn btn-sm btn-success me-2" onclick="acceptRequest(${request.id})">Accept</button>
              <button class="btn btn-sm btn-danger" onclick="declineRequest(${request.id})">Decline</button>
            </div>
          </li>`
        ).join('')
        : '<li class="list-group-item text-center text-secondary">No requests</li>';
    });
}

function handleAddFriend(e) {
  e.preventDefault();
  const usernameInput = document.getElementById('friend-username');
  const username = usernameInput?.value?.trim();
  
  if (!username) return;

  fetch('friends_ajax.php?action=add', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username })
  })
  .then(r => r.json())
  .then(data => {
    alert(data.message);
    if (usernameInput) usernameInput.value = '';
    loadFriendRequests();
  });
}

window.removeFriend = function(friendId) {
  if (!confirm('Remove this friend?')) return;
  
  fetch('friends_ajax.php?action=remove', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: friendId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) loadFriends();
    if (data.message) alert(data.message);
  });
};

window.acceptRequest = function(requestId) {
  updateFriendRequest(requestId, 'accept');
};

window.declineRequest = function(requestId) {
  updateFriendRequest(requestId, 'decline');
};

function updateFriendRequest(requestId, action) {
  fetch('friends_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=${action}&id=${requestId}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      loadFriends();
      loadFriendRequests();
    }
    if (data.message) alert(data.message);
  });
}

function setupVerificationCodeInput() {
  const codeInputs = document.querySelectorAll('.verification-code-input');
  if (codeInputs.length === 0) return;

  codeInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
      if (e.target.value.length > 0) {
        const nextInput = codeInputs[index + 1];
        if (nextInput) nextInput.focus();
      }
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && e.target.value.length === 0) {
        const prevInput = codeInputs[index - 1];
        if (prevInput) prevInput.focus();
      }
    });
  });
}

function setupDownloadPage() {
  const downloadButton = document.getElementById('downloadButton');
  if (downloadButton) {
    downloadButton.addEventListener('click', function(e) {
      e.preventDefault();
      alert('Download will start shortly. Thank you for your interest in ECHO RUNNER!');
    });
  }

  const cursor = document.querySelector('.cursor');
  if (cursor) {
    document.addEventListener('mousemove', (e) => {
      cursor.style.left = e.pageX + 'px';
      cursor.style.top = e.pageY + 'px';
    });

    document.querySelectorAll('a, button, .screenshot, [data-bs-toggle]').forEach(el => {
      el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
      el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
    });
  }

  const screenshotModal = document.getElementById('screenshotModal');
  if (screenshotModal) {
    screenshotModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const imgSrc = button.getAttribute('data-img');
      const modalImg = screenshotModal.querySelector('#modalScreenshot');
      if (modalImg) modalImg.src = imgSrc;
    });
  }
}

function setupAvatarSelection() {
    window.selectPreset = function(avatarName) {
        console.log('Avatar selected:', avatarName);
        
        document.querySelectorAll('.preset-avatar').forEach(avatar => {
            avatar.classList.remove('selected-avatar', 'border', 'border-3', 'border-cyan');
        });
        
        const clickedAvatar = document.querySelector(`.preset-avatar[data-avatar="${avatarName}"]`);
        if (clickedAvatar) {
            clickedAvatar.classList.add('selected-avatar', 'border', 'border-3', 'border-cyan');
        }
        
        const presetInput = document.getElementById('presetInput');
        if (presetInput) {
            presetInput.value = avatarName;
            const form = document.getElementById('presetForm');
            if (form) {
                form.submit();
            } else {
                console.error('Preset form not found');
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    setupAvatarSelection();
    
    const fileInput = document.getElementById('avatar');
    const fileNameDisplay = document.getElementById('file-name');
    const fileButtonText = document.getElementById('file-button-text');

    if (fileInput && fileNameDisplay && fileButtonText) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileName = this.files[0].name;
                fileButtonText.textContent = 'File selected';
                fileNameDisplay.querySelector('span').textContent = fileName;
                
                if (this.form) {
                    this.form.submit();
                }
            }
        });
    }
    
    if (typeof setupHamburgerMenu === 'function') setupHamburgerMenu();
    if (typeof setupCustomCursor === 'function') setupCustomCursor();
});