import { register_click, register } from './api.js'

register_click('register', async (event) => {
    event.preventDefault()

    const username = document.getElementById('username').value
    const password = document.getElementById('password').value
    const captcha = document.getElementById('captcha').value
    const resp = await register(username, password, captcha)
    const json = await resp.json()

    if (json.succ) {
        alert('注册成功')
        window.location.href = '/'
    } else {
        const ele = document.getElementById('error-msg')
        ele.style.color = 'red'
        ele.innerText = json.msg

        setTimeout(() => {
            ele.style.color = 'gray'
        }, 1000)
    }
})
