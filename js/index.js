import { getUsername } from './main.js'

const username = 'test'
document.getElementById('title-welcome').innerText += ', ' + username

if (username === 'admin') {
    document.getElementById('backup-users').style.display = 'inline'
    document.getElementById('backup-msgs').style.display = 'inline'
}
