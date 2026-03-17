import './bootstrap'
import Alpine from 'alpinejs'

window.Alpine = Alpine
Alpine.start()

document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | Real-time Notification Counter
    |--------------------------------------------------------------------------
    */

    if(window.Echo && window.userId){

        window.Echo.private(`user.${window.userId}`)
            .listen('.notification.created', (e) => {

                const badge = document.querySelector('.bg-red-500')

                if(!badge) return

                let count = parseInt(badge.innerText || 0)
                badge.innerText = count + 1

            })

        /*
        |--------------------------------------------------------------------------
        | Real-time Message Counter
        |--------------------------------------------------------------------------
        */

        window.Echo.channel(`user.${window.userId}`)
            .listen('.message.notification', (e) => {

                const badge = document.querySelector('.message-counter')

                if(!badge) return

                let count = parseInt(badge.innerText || 0)
                badge.innerText = count + 1

            })

    }

    /*
    |--------------------------------------------------------------------------
    | User Activity Ping (Online Status)
    |--------------------------------------------------------------------------
    */

    setInterval(() => {

        fetch('/user/ping', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
                'Accept': 'application/json'
            }
        }).catch(() => {})

    }, 30000)

})
