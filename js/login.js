import { login, register_click } from './api.js'

register_click('login', async function (event) {
    event.preventDefault()

    const username = document.getElementById('username').value
    const password = document.getElementById('password').value
    const captcha = document.getElementById('captcha').value

    const result = await login(username, password, captcha)
    const json = await result.json()
    if (json.succ) {
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
