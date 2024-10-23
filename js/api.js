export function err_popup(msg) {
    alert('错误' + msg)
}

export function show_error(err) {
    const ele = document.getElementById('error-msg')
    ele.style.color = 'red'
    ele.innerText = err

    setTimeout(() => {
        ele.style.color = 'gray'
    }, 1000)
}

export function onUnauthorized() {
    alert('未登录或登录信息失效，即将跳转到登录页')
    window.location.href = '/login.html'
}

/**
 * @param {RequestInfo | URL} url
 * @param {RequestInit=} init
 */
export async function request(url, init, is_json = true) {
    if (is_json) {
        init = init || {}
        init.headers = init.headers || {}
        init.headers['Content-Type'] = 'application/json'
    }

    const resp = await fetch(url, init)
    switch (resp.status) {
        case 401:
            onUnauthorized()
            break
        default:
            return resp
    }
}

export async function getUsername() {
    const resp = await request('/api/user_info.php')
    const json = await resp.json()
    return json.data
}

export async function backupUsers() {
    await request('/api/backup.php?target=users', {
        method: 'GET',
    })
}

export async function backupMessages() {
    await request('/api/backup.php?target=messages', {
        method: 'GET',
    })
}

/** @param {string} username
 * @param {string} password
 * @param {string} capthca
 * @returns {Promise<Response | undefined>}
 */
export async function login(username, password, captcha) {
    return await request('/api/login.php', {
        method: 'POST',
        body: JSON.stringify({
            username,
            password,
            captcha,
        }),
    })
}

export async function logout() {
    return await request('/api/logout.php')
}

export async function register(username, password, captcha) {
    return await request('/api/register.php', {
        method: 'POST',
        body: JSON.stringify({
            username,
            password,
            captcha,
        }),
    })
}

export async function register_click(id, func) {
    document.getElementById(id).addEventListener('click', func)
}

export async function modify_pwd(username, password, new_password, captcha) {
    return await request('/api/modify_pass.php', {
        method: 'POST',
        body: JSON.stringify({
            username,
            password,
            new_password,
            captcha,
        }),
    })
}

export async function upload_avatar(file) {
    const formData = new FormData()
    formData.append('avatar', file)
    return await request(
        '/api/avatar.php',
        {
            method: 'POST',
            body: formData,
        },
        false
    )
}

export async function backup(target = undefined) {
    let url = '/api/backup.php'
    if (target) {
        url += `?target=${target}`
    }
    return await request(url, {
        method: 'GET',
    })
}

export async function addMessage(message) {
    return await request('/api/message.php', {
        method: 'POST',
        body: JSON.stringify({
            message,
        }),
    })
}

export async function deleteMessage(id) {
    return await request(`/api/message.php?id=${id}`, {
        method: 'DELETE',
    })
}

export async function getMessages(username = undefined) {
    let url = '/api/message.php'
    if (username) {
        url += `?username=${username}`
    }
    return await request(url)
}
