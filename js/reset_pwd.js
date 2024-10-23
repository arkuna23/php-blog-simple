import { register_click, modify_pwd } from './api.js'

register_click('reset-btn', async (event) => {
    event.preventDefault()

    const username = document.getElementById('username').value
    const password = document.getElementById('password').value
    const new_password = document.getElementById('password').value
    const captcha = document.getElementById('captcha').value
    const resp = await modify_pwd(username, password, new_password, captcha)
    const json = await resp.json()

    if (json.succ) {
        alert('重置成功')
        window.location.href = '/login.html'
    } else {
        const ele = document.getElementById('error-msg')
        ele.style.color = 'red'
        ele.innerText = json.msg

        setTimeout(() => {
            ele.style.color = 'gray'
        }, 1000)
    }
})
