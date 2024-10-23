export function err_popup(msg) {
    alert('错误' + msg)
}

export function onUnauthorized() {
    alert('未登录或登录信息失效，即将跳转到登录页')
    window.location.href = '/login.html'
}

/**
 * @param {RequestInfo | URL} url
 * @param {RequestInit=} init
 */
export async function request(url, init) {
    const resp = await fetch(url, init)
    switch (resp.status) {
        case 200:
            return await resp.json()
        case 401:
            onUnauthorized()
            break
        default:
            const json = await resp.json()
            if (json) {
                err_popup(json.msg)
            } else {
                err_popup(resp.statusText)
            }
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

export async function getMessages() {
    return await request('/api/message.php')
}
