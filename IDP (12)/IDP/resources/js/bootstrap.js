import axios from 'axios'

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'


/*
|--------------------------------------------------------------------------
| Laravel Echo + Reverb WebSocket
|--------------------------------------------------------------------------
*/

import Echo from 'laravel-echo'

window.Echo = new Echo({

    broadcaster: 'reverb',

    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,

    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,

    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,

    forceTLS: false,

    enabledTransports: ['ws'],

    disableStats: true,

    withCredentials: true,

    authEndpoint: '/broadcasting/auth',

    auth: {
        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content')
        }
    }

})


/*
|--------------------------------------------------------------------------
| WebSocket Debug
|--------------------------------------------------------------------------
*/

if (window.Echo && window.Echo.connector) {

    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✔ WebSocket Connected')
    })

    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('WebSocket Disconnected')
    })

    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('WebSocket Error:', err)
    })

}
