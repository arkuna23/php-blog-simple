import { upload_avatar } from './api.js'

const upload_btn = document.getElementById('upload-btn')

let file
document
    .getElementById('fileInput')
    .addEventListener('change', function (event) {
        file = event.target.files[0]
        if (file) {
            const reader = new FileReader()
            reader.onload = function (e) {
                const img = document.createElement('img')
                img.src = e.target.result
                const preview = document.getElementById('preview')
                preview.innerHTML = ''
                preview.appendChild(img)
            }
            reader.readAsDataURL(file)
            upload_btn.disabled = false
        }
    })

upload_btn.addEventListener('click', async () => {
    const result = await upload_avatar(file)
    const json = await result.json()

    if (json.succ) {
        alert('上传成功')
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
