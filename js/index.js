import {
    getUsername,
    logout as api_logout,
    register_click,
    backup,
    show_error,
    addMessage,
    deleteMessage,
    getMessages,
} from './api.js'

const username = await getUsername()
document.getElementById('title-welcome').innerText += ', ' + username
register_click('logout', async () => {
    await api_logout()
    window.location.href = '/login.html'
})

if (username === 'admin') {
    document.getElementById('backup-users').style.display = 'inline'
    document.getElementById('backup-msgs').style.display = 'inline'
    document.getElementById('backup-all').style.display = 'inline'
}

const backup_check = async (target) => {
    const res = await backup(target)
    const json = await res.json()
    if (json.succ) {
        alert('备份完成')
    } else {
        alert('备份失败')
    }
}

const user_avatar = document.getElementById('user-avatar')
const tip = document.getElementById('tip')
user_avatar.onmouseover = () => {
    tip.style.display = 'inline'
}
user_avatar.onmouseout = () => {
    tip.style.display = 'none'
}

register_click('backup-users', async () => {
    await backup_check('users')
})
register_click('backup-msgs', async () => {
    await backup_check('messages')
})
register_click('backup-all', async () => {
    await backup_check()
})

register_click('add-msg', async () => {
    const msg = document.getElementById('msg').value
    const res = await addMessage(msg)
    const json = await res.json()
    if (json.succ) {
        location.reload()
    } else {
        show_error(json.msg)
    }
})

const template = document.getElementById('msg-template').content
const messageSection = document.getElementById('messages')
const createMessage = (id, user, msg, timestamp) => {
    const clone = document.importNode(template, true)
    const img = clone.querySelector('img')
    img.src = 'api/avatar.php?username=' + user
    img.alt = `${user}的头像`

    clone.querySelector('.username').textContent = user
    clone.querySelector('.content').textContent = msg
    clone.querySelector('.timestamp').textContent = timestamp
    if (username === 'admin' || username === user) {
        const delBtn = clone.querySelector('.delete')
        delBtn.style.display = 'inline'
        delBtn.onclick = async () => {
            const res = await deleteMessage(id)
            const json = await res.json()
            if (json.succ) {
                window.location.reload()
            } else {
                alert('错误: ' + json.msg)
            }
        }
    }

    messageSection.appendChild(clone)
}

const messages = (await (await getMessages()).json()).data
for (const msg of messages) {
    createMessage(msg.id, msg.username, msg.message, msg.updated_at)
}
